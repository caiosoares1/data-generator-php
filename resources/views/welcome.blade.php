<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }} - Gerador de Dados</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
            <div class="sm:mx-auto sm:w-full sm:max-w-md">
                <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-blue-500 text-white text-2xl font-bold">
                    DG
                </div>
                <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900">
                    Data Generator
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Gere dados fictícios para seus projetos
                </p>
            </div>

            <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                    @if (Route::has('login'))
                        <div class="space-y-4">
                            @auth
                                <a href="{{ route('generator.index') }}" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    🎲 Acessar Gerador
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    🔐 Entrar
                                </a>
                                
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        📝 Registrar
                                    </a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
                
                <div class="mt-6 text-center text-xs text-gray-500">
                    <p>Tipos de dados disponíveis:</p>
                    <div class="mt-2 flex flex-wrap justify-center gap-2">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">📧 Email</span>
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">🆔 CPF</span>
                        <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">🏢 CNPJ</span>
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">🔒 Senha</span>
                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">📱 Telefone</span>
                        <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs">👤 Nome</span>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>