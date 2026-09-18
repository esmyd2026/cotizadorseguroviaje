<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#16243D">
        <meta name="description" content="Cotiza y contrata tu seguro de viaje con Gestión Segura.">

        <title>{{ config('app.name') }} — Cotiza tu seguro de viaje</title>

        <meta name="csrf-token" content="{{ csrf_token() }}">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-brand-gray-light text-brand-navy antialiased">
        <div id="app" data-auth='@json(auth()->check() ? ["name" => auth()->user()->name, "isAdmin" => auth()->user()->isAdmin()] : null)'></div>
    </body>
</html>
