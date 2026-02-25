@extends('layouts.app') {{-- или любой ваш основной макет --}}

@section('content')

    <div class="container">
        <h1>Управление плагинами</h1>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="mb-3">
            <form action="{{ route('admin.plugins.sync') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-primary">Синхронизировать плагины</button>
            </form>
        </div>

        <table class="table">
            <thead>
            <tr>
                <th>Название</th>
                <th>Провайдер</th>
                <th>Версия</th>
                <th>Описание</th>
                <th>Автор</th>
                <th>Зависимости</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            @foreach($plugins as $plugin)
                <tr>
                    <td>{{ $plugin->name }}</td>
                    <td>{{ $plugin->provider }}</td>
                    <td>{{ $plugin->version }}</td>
                    <td>{{ Str::limit($plugin->description, 50) }}</td>
                    <td>{{ $plugin->author }}</td>
                    <td>
                        @if($plugin->requires)
                            @foreach($plugin->requires as $requirement)
                                @php
                                    // Разделяем имя и версию (если есть)
                                    $parts = explode('@', $requirement);
                                    $reqName = $parts[0];
                                    $reqVersion = $parts[1] ?? '*';
                                    // Ищем требуемый плагин в общей коллекции
                                    $requiredPlugin = $plugins->firstWhere('name', $reqName);
                                    $installed = $requiredPlugin ? true : false;
                                    $active = $requiredPlugin && $requiredPlugin->active;
                                @endphp
                                <div class="mb-1">
                                    <strong>{{ $reqName }}</strong>
                                    @if($reqVersion != '*')
                                        <small>({{ $reqVersion }})</small>
                                    @endif
                                    @if(!$installed)
                                        <span class="badge bg-danger">Отсутствует</span>
                                    @elseif(!$active)
                                        <span class="badge bg-warning text-dark">Неактивен</span>
                                    @else
                                        <span class="badge bg-success">Установлен</span>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <span class="text-muted">Отсутствуют</span>
                        @endif
                    </td>
                    <td>
                        @if($plugin->active)
                            <span class="badge bg-success">Активен</span>
                        @else
                            <span class="badge bg-secondary">Неактивен</span>
                        @endif
                    </td>
                    <td>
                        @if($plugin->active)
                            <form action="{{ route('admin.plugins.deactivate', $plugin) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm">Деактивировать</button>
                            </form>
                        @else
                            <form action="{{ route('admin.plugins.activate', $plugin) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Активировать</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@endsection
