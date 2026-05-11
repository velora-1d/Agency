<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Client Portal - {{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-950 text-stone-50">
        <main class="mx-auto flex min-h-screen max-w-5xl flex-col justify-center gap-6 px-6 py-12">
            <p class="text-sm uppercase tracking-[0.3em] text-amber-300">Client Portal</p>
            <h1 class="text-4xl font-semibold tracking-tight">Portal scaffold sudah dipisah dari panel admin.</h1>
            <p class="max-w-2xl text-sm text-stone-300">
                Halaman ini placeholder untuk portal client custom. Tahap berikutnya tinggal sambungkan Inertia/Vue.
            </p>
        </main>
    </body>
</html>
