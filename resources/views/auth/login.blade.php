<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Binaro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-8 border border-slate-200">
        <div class="text-center mb-6">
            <div class="w-12 h-12 bg-[#13527D] text-white font-black text-xl rounded-xl mx-auto flex items-center justify-center mb-3 shadow-md">
                BN
            </div>
            <h1 class="text-xl font-black text-slate-900">Binaro</h1>
            <p class="text-xs text-slate-500 mt-0.5">SDN Kalitapen 01</p>
        </div>

        @if(session('error'))
        <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-lg flex items-center gap-2">
            <i class="fas fa-exclamation-circle text-rose-500"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if(session('success'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-lg flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-500"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">NIP / Username</label>
                <input type="text" name="username" required placeholder="Masukkan NIP" 
                       class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-slate-50">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Password</label>
                <input type="password" name="password" required placeholder="Masukkan Password" 
                       class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-slate-50">
            </div>
            <button type="submit" class="w-full py-2.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white font-bold rounded-lg transition shadow-md">
                Masuk ke Sistem
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-100 text-center text-[11px] text-slate-400">
            Akses untuk Admin & Guru Pengajar
        </div>
    </div>
</body>
</html>
