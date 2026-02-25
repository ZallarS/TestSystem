<?php

namespace Plugins\HelloWorld\Providers;

use Illuminate\Support\ServiceProvider;
use App\Facades\Hook;

class HelloWorldServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Загружаем шаблоны из папки resources/views с пространством имён 'hello'
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'hello');

        // Регистрируем хук для вывода в сайдбаре
        Hook::addAction('sidebar', function () {
            echo view('hello::hello')->render();
        });
    }

    public function register()
    {
        // Ничего не регистрируем
    }
}
