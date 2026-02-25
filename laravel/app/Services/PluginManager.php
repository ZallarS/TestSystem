<?php

namespace App\Services;

use App\Models\Plugin;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Composer\Semver\Semver;

class PluginManager
{
    protected string $path = 'plugins';

    public function scanAndSync(): void
    {
        $plugins = [];
        $directories = File::directories(base_path($this->path));

        foreach ($directories as $dir) {
            $pluginJson = $dir . '/plugin.json';
            if (!File::exists($pluginJson)) {
                continue;
            }

            $config = json_decode(File::get($pluginJson), true);
            $name = basename($dir);
            $provider = $config['provider'] ?? null;

            if (!$provider || !class_exists($provider)) {
                Log::warning("Plugin [{$name}] has invalid provider.");
                continue;
            }

            $plugins[] = [
                'name'        => $name,
                'provider'    => $provider,
                'version'     => $config['version'] ?? null,
                'description' => $config['description'] ?? null,
                'author'      => $config['author'] ?? null,
                'requires'    => $config['requires'] ?? [],
                'active'      => false,
                'settings'    => $config['settings'] ?? [],
            ];
        }

        foreach ($plugins as $data) {
            Plugin::updateOrCreate(
                ['name' => $data['name']],
                [
                    'provider'    => $data['provider'],
                    'version'     => $data['version'],
                    'description' => $data['description'],
                    'author'      => $data['author'],
                    'requires'    => $data['requires'],
                    'settings'    => $data['settings'],
                ]
            );
        }

        $existingNames = collect($plugins)->pluck('name');
        Plugin::whereNotIn('name', $existingNames)->delete();
    }

    public function registerActiveProviders(): void
    {
        $activePlugins = Plugin::where('active', true)->get()->keyBy('name');

        // Построим граф зависимостей
        $graph = [];
        foreach ($activePlugins as $plugin) {
            $graph[$plugin->name] = [];
            foreach ($plugin->requires ?? [] as $req) {
                // извлекаем имя плагина из требования (без версии)
                $reqName = str_contains($req, '@') ? explode('@', $req)[0] : $req;
                if (isset($activePlugins[$reqName])) {
                    $graph[$plugin->name][] = $reqName;
                }
            }
        }

        // Топологическая сортировка (функция ниже)
        try {
            $sorted = $this->topologicalSort($graph);
        } catch (\Exception $e) {
            Log::error('Plugin dependency cycle: ' . $e->getMessage());
            // Можно также записать в сессию для отображения в админке
        }

        // Регистрируем провайдеры в полученном порядке
        foreach ($sorted as $pluginName) {
            $plugin = $activePlugins[$pluginName];
            if (class_exists($plugin->provider)) {
                app()->register($plugin->provider);
            }
        }
    }

    protected function topologicalSort(array $graph): array
    {
        $result = [];
        $visited = [];
        $tempMark = [];

        $visit = function ($node) use (&$visit, &$result, &$visited, &$tempMark, $graph) {
            if ($tempMark[$node] ?? false) {
                throw new \Exception("Circular dependency detected involving '{$node}'");
            }
            if (!($visited[$node] ?? false)) {
                $tempMark[$node] = true;
                foreach ($graph[$node] as $neighbor) {
                    $visit($neighbor);
                }
                $visited[$node] = true;
                $tempMark[$node] = false;
                array_unshift($result, $node);
            }
        };

        foreach (array_keys($graph) as $node) {
            if (!($visited[$node] ?? false)) {
                $visit($node);
            }
        }

        return $result;
    }

    public function checkDependencies(Plugin $plugin): array
    {
        $errors = [];
        $requires = $plugin->requires ?? [];

        foreach ($requires as $requirement) {
            // Разбираем строку вида "PluginName" или "PluginName@^1.2"
            if (str_contains($requirement, '@')) {
                [$reqName, $reqVersion] = explode('@', $requirement, 2);
            } else {
                $reqName = $requirement;
                $reqVersion = '*';
            }

            $requiredPlugin = Plugin::where('name', $reqName)->first();

            if (!$requiredPlugin) {
                $errors[] = "Required plugin '{$reqName}' is not installed.";
                continue;
            }

            if (!$requiredPlugin->active) {
                $errors[] = "Required plugin '{$reqName}' is not active.";
                continue;
            }

            // Если указана версия, проверяем совместимость
            if ($reqVersion !== '*' && !$this->versionMatches($requiredPlugin->version, $reqVersion)) {
                $errors[] = "Required plugin '{$reqName}' version {$requiredPlugin->version} does not match required {$reqVersion}.";
            }
        }

        return $errors;
    }


    protected function versionMatches($installed, $required)
    {
        return Semver::satisfies($installed, $required);
    }
}
