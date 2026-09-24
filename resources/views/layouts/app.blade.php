<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Binaro - SDN Kalitapen 01' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800 flex min-h-screen">
    @include('layouts.sidebar')

    <main class="flex-1 ml-64 flex flex-col min-w-0">
        @include('layouts.header')

        <!-- Flash Alerts -->
        @if(session('success'))
        <div class="mx-8 mt-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-600 text-sm"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="fas fa-times"></i></button>
        </div>
        @endif

        @if(session('error'))
        <div class="mx-8 mt-6 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-exclamation-circle text-rose-600 text-sm"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700"><i class="fas fa-times"></i></button>
        </div>
        @endif

        @if(isset($errors) && $errors->any())
        <div class="mx-8 mt-6 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            <div class="font-bold mb-1"><i class="fas fa-exclamation-circle mr-1"></i> Terjadi Kesalahan Input:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @yield('content')
    </main>
    <script>
        document.addEventListener('input', function (event) {
            const field = event.target;
            const isTextInput = field instanceof HTMLInputElement && ['text', 'search'].includes(field.type);
            const isTextArea = field instanceof HTMLTextAreaElement;
            if (!isTextInput && !isTextArea) {
                return;
            }

            const allowedSymbols = ['email', 'no_hp', 'kode_barcode', 'kode', 'kodeManual', 'jam', 'search', 'q', 'alamat'];
            if (allowedSymbols.includes(field.name) || allowedSymbols.includes(field.id)) {
                return;
            }

            field.value = field.value.replace(/[^\p{L}\p{N} ]/gu, '');
        });
    </script>
</body>
</html>
