<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Binaro Learning & Assessment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Instrument Sans', sans-serif; background-color: #F8FAFC; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-sm max-w-sm w-full p-8 border border-slate-200">
        <!-- Logo & Branding -->
        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-[#13527D] text-white font-black text-2xl rounded-2xl mx-auto flex items-center justify-center mb-3 shadow-md">
                BN
            </div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Binaro Learning & Assessment</h1>
            <p class="text-xs text-slate-500 mt-0.5">SDN Kalitapen 01</p>
        </div>

        @if(session('error'))
        <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center gap-2">
            <i class="fas fa-circle-exclamation text-rose-500 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if(session('success'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-xl flex items-center gap-2">
            <i class="fas fa-circle-check text-emerald-500 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        <!-- Smart Auth Form (PRD Section 3) -->
        <form action="{{ route('login.post') }}" method="POST" id="loginForm" class="space-y-4 text-xs">
            @csrf
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="font-bold text-slate-700">NISN / NIP / Username</label>
                    <span id="role-badge" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 transition">
                        Deteksi Otomatis
                    </span>
                </div>

                <div class="relative">
                    <input type="text" id="username" name="username" required maxlength="100" autocomplete="username"
                           placeholder="10 digit (Siswa) / 18 digit (Guru) / Username"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-[#13527D] focus:bg-white text-xs transition">
                    <div id="input-icon" class="absolute right-3 top-3 text-slate-400 text-xs">
                        <i class="fas fa-id-card"></i>
                    </div>
                </div>

                <!-- Helper Hint with Digit Count -->
                <div class="flex items-center justify-between text-[11px] text-slate-400 mt-1.5 px-0.5">
                    <span id="role-hint">Ketik nomor identitas atau nama pengguna.</span>
                    <span id="digit-counter" class="font-mono text-[10px] text-slate-400 hidden">0 digit</span>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Kata Sandi</label>
                <div class="relative">
                    <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Masukkan kata sandi akun"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-[#13527D] focus:bg-white text-xs transition">
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3 top-3 text-slate-400 hover:text-slate-600 text-xs">
                        <i id="eye-icon" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" id="submitBtn" class="w-full py-2.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white font-bold rounded-xl transition shadow-sm flex items-center justify-center gap-2">
                <i class="fas fa-right-to-bracket"></i> Masuk ke Sistem
            </button>
        </form>

        <!-- Quick 1-Click Auto Fill Demo Accounts -->
        <div class="mt-4 pt-3 border-t border-slate-100">
            <span class="block text-[10px] font-bold text-slate-400 text-center uppercase tracking-wider mb-2">
                Pintasan Isi Otomatis Akun Demo
            </span>
            <div class="grid grid-cols-3 gap-1.5">
                <button type="button" onclick="fillDemo('siswa')" title="Siswa: 0012345678 / siswa123" class="p-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-300 text-[11px] font-bold text-slate-700 transition flex flex-col items-center gap-0.5 group">
                    <span class="text-xs group-hover:scale-110 transition">🎓</span>
                    <span>Siswa</span>
                    <span class="text-[9px] text-slate-400 font-normal">siswa123</span>
                </button>
                <button type="button" onclick="fillDemo('guru')" title="Guru: 198501012010012001 / guru123" class="p-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-sky-50 hover:border-sky-300 text-[11px] font-bold text-slate-700 transition flex flex-col items-center gap-0.5 group">
                    <span class="text-xs group-hover:scale-110 transition">👨‍🏫</span>
                    <span>Guru</span>
                    <span class="text-[9px] text-slate-400 font-normal">guru123</span>
                </button>
                <button type="button" onclick="fillDemo('admin')" title="Admin: admin / admin123" class="p-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-amber-50 hover:border-amber-300 text-[11px] font-bold text-slate-700 transition flex flex-col items-center gap-0.5 group">
                    <span class="text-xs group-hover:scale-110 transition">🛡️</span>
                    <span>Admin</span>
                    <span class="text-[9px] text-slate-400 font-normal">admin123</span>
                </button>
            </div>
        </div>

        <!-- Security Guardrail Badge (PRD 3 Anti-Bruteforce) -->
        <div class="mt-5 pt-3 border-t border-slate-100 flex flex-col items-center gap-1.5 text-center">
            <div class="flex items-center gap-1.5 text-[10px] font-semibold text-slate-400">
                <i class="fas fa-shield-halved text-emerald-600"></i>
                <span>Anti-Bruteforce: Max 5x coba / 15 menit</span>
            </div>
            <div class="text-[10px] text-slate-400">
                SDN Kalitapen 01 &bull; Binaro Smart Auth
            </div>
        </div>
    </div>

    <script>
        const input = document.getElementById('username');
        const badge = document.getElementById('role-badge');
        const hint = document.getElementById('role-hint');
        const counter = document.getElementById('digit-counter');
        const icon = document.getElementById('input-icon');

        // 1-Click Auto-Fill Demo
        function fillDemo(role) {
            const u = document.getElementById('username');
            const p = document.getElementById('password');
            if (role === 'siswa') {
                u.value = '0012345678';
                p.value = 'siswa123';
            } else if (role === 'guru') {
                u.value = '198501012010012001';
                p.value = 'guru123';
            } else if (role === 'admin') {
                u.value = 'admin';
                p.value = 'admin123';
            }
            u.dispatchEvent(new Event('input', { bubbles: true }));
            u.dispatchEvent(new Event('change', { bubbles: true }));
            p.dispatchEvent(new Event('input', { bubbles: true }));
            p.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // JS Input Mask & Real-time Digit Inspection (PRD Section 3)
        input.addEventListener('input', function(e) {
            let val = this.value;

            // If user starts with digit, enforce digits only
            if (/^\d/.test(val)) {
                val = val.replace(/\D/g, ''); // strip non-digits
                if (val.length > 18) {
                    val = val.slice(0, 18); // limit to 18 digits (NIP max)
                }
                this.value = val;
            }

            const len = val.length;

            if (/^\d+$/.test(val)) {
                counter.classList.remove('hidden');
                counter.textContent = `${len} digit`;

                if (len === 10) {
                    badge.textContent = 'Role: SISWA';
                    badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700';
                    hint.textContent = '10 Digit NISN Terpenuhi (Entitas Siswa)';
                    icon.innerHTML = '<i class="fas fa-graduation-cap text-emerald-600"></i>';
                } else if (len === 18) {
                    badge.textContent = 'Role: GURU';
                    badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700';
                    hint.textContent = '18 Digit NIP Terpenuhi (Entitas Guru)';
                    icon.innerHTML = '<i class="fas fa-chalkboard-user text-emerald-600"></i>';
                } else {
                    badge.textContent = 'Angka Identitas';
                    badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-sky-100 text-[#13527D]';
                    hint.textContent = len < 10 ? 'Menuju NISN Siswa (10 digit)' : 'Menuju NIP Guru (18 digit)';
                    icon.innerHTML = '<i class="fas fa-hashtag text-[#13527D]"></i>';
                }
            } else if (len > 0) {
                counter.classList.add('hidden');
                badge.textContent = 'Role: ADMIN';
                badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#13527D]/10 text-[#13527D]';
                hint.textContent = 'Username Terdeteksi (Entitas Administrator)';
                icon.innerHTML = '<i class="fas fa-user-shield text-[#13527D]"></i>';
            } else {
                counter.classList.add('hidden');
                badge.textContent = 'Deteksi Otomatis';
                badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500';
                hint.textContent = 'Ketik nomor identitas atau nama pengguna.';
                icon.innerHTML = '<i class="fas fa-id-card text-slate-400"></i>';
            }
        });

        function togglePasswordVisibility() {
            const pwd = document.getElementById('password');
            const eye = document.getElementById('eye-icon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                eye.classList.remove('fa-eye');
                eye.classList.add('fa-eye-slash');
            } else {
                pwd.type = 'password';
                eye.classList.remove('fa-eye-slash');
                eye.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
