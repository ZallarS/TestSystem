<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Services\PluginManager;
use Illuminate\Http\Request;

class PluginController extends Controller
{
    protected PluginManager $pluginManager;

    public function __construct(PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    public function index()
    {
        $plugins = Plugin::all();
        return view('admin.plugins.index', compact('plugins'));
    }

    public function activate(Plugin $plugin)
    {
        $errors = app(PluginManager::class)->checkDependencies($plugin);
        if (!empty($errors)) {
            return redirect()->back()->withErrors($errors);
        }
        $plugin->active = true;
        $plugin->save();
        return redirect()->route('admin.plugins.index')->with('success', 'Плагин активирован.');
    }

    public function deactivate(Plugin $plugin)
    {
        $plugin->active = false;
        $plugin->save();
        return redirect()->route('admin.plugins.index')->with('success', 'Плагин деактивирован.');
    }

    public function sync()
    {
        $this->pluginManager->scanAndSync();
        return redirect()->route('admin.plugins.index')->with('success', 'Список плагинов обновлён.');
    }
}
