<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Binaro</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #0B2A44 0%, #13527D 55%, #1E7FA8 100%);
            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 20px;

            position: relative;
            overflow-x: hidden;
        }


        /* Lingkaran dekoratif latar */
        body::before,
        body::after {
            content: "";
            position: fixed;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.35;
            pointer-events: none;
            z-index: 0;
        }

        body::before {
            width: 420px;
            height: 420px;
            background: #2AA5C4;
            top: -140px;
            right: -120px;
        }

        body::after {
            width: 360px;
            height: 360px;
            background: #F2A007;
            bottom: -140px;
            left: -120px;
            opacity: 0.22;
        }


        /* =====================================================
           CONTAINER
        ===================================================== */

        .login-container {
            position: relative;
            z-index: 1;

            width: 920px;
            max-width: 100%;
            min-height: 540px;

            background: white;

            display: flex;

            border-radius: 24px;

            overflow: hidden;

            box-shadow: 0 24px 70px rgba(4, 26, 43, 0.45);
        }


        /* =====================================================
           BAGIAN KIRI
        ===================================================== */

        .left-side {
            width: 46%;

            background:
                linear-gradient(rgba(14, 61, 93, 0.72), rgba(19, 82, 125, 0.88)),
                url("{{ asset('images/sekolah.jpg') }}"),
                #13527D;

            background-size: cover;
            background-position: center;

            color: white;

            padding: 44px 40px;

            display: flex;
            flex-direction: column;
            justify-content: center;

            position: relative;
            overflow: hidden;
        }


        /* Pola titik-titik dekoratif */
        .left-side::after {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.16) 1.5px, transparent 1.5px);
            background-size: 22px 22px;
            mask-image: linear-gradient(to top, black 20%, transparent 75%);
            pointer-events: none;
        }


        .left-side > * {
            position: relative;
            z-index: 1;
        }


        .logo {
            display: flex;
            align-items: center;
            gap: 10px;

            margin-bottom: 20px;
        }


        .logo-icon {
            width: 46px;
            height: 46px;

            background: linear-gradient(135deg, #F2A007, #F7C948);

            border-radius: 13px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 22px;
            font-weight: 800;
            color: #0E3D5D;

            box-shadow: 0 6px 16px rgba(242, 160, 7, 0.4);
        }


        .logo h1 {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 0.3px;
        }


        .logo p {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.75);
            font-weight: 600;
        }


        .description {
            font-size: 13px;
            line-height: 1.7;

            max-width: 350px;

            color: rgba(255, 255, 255, 0.88);
        }


        /* Daftar fitur unggulan */
        .feature-list {
            list-style: none;
            margin-top: 26px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }


        .feature-list li {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 12px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.92);
        }


        .feature-badge {
            width: 34px;
            height: 34px;
            flex-shrink: 0;

            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 10px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 15px;
        }


        /* =====================================================
           BAGIAN KANAN
        ===================================================== */

        .right-side {
            width: 54%;

            padding: 48px 46px;

            display: flex;
            flex-direction: column;
            justify-content: center;

            background: #fff;
        }


        .login-title {
            font-size: 26px;
            font-weight: 800;
            color: #0E3D5D;

            margin-bottom: 4px;
        }


        .login-subtitle {
            font-size: 12px;
            color: #7b8a99;
            font-weight: 500;

            margin-bottom: 22px;
        }


        /* =====================================================
           FORM
        ===================================================== */

        .form-group {
            margin-bottom: 12px;
        }


        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #3d5266;
            margin-bottom: 6px;
        }


        .input-box {
            width: 100%;
            height: 44px;

            border: 1.5px solid #D8E2EC;
            background: #F7FAFD;

            border-radius: 11px;

            padding: 0 14px;

            font-size: 13px;
            font-weight: 600;
            color: #0E3D5D;
            font-family: inherit;

            outline: none;
            transition: 0.2s;
        }


        .input-box::placeholder {
            font-weight: 500;
            color: #93a5b6;
        }


        .input-box:focus {
            border-color: #13527D;
            background: #fff;

            box-shadow:
                0 0 0 3px rgba(19, 82, 125, 0.12);
        }


        /* =====================================================
           LOGIN BUTTON
        ===================================================== */

        .login-button {
            width: 100%;

            height: 46px;

            border: none;

            border-radius: 12px;

            background: linear-gradient(135deg, #13527D 0%, #1E7FA8 100%);

            color: white;

            font-size: 14px;
            font-weight: 800;
            font-family: inherit;
            letter-spacing: 0.3px;

            cursor: pointer;

            margin-top: 12px;

            transition: 0.2s;

            box-shadow: 0 8px 20px rgba(19, 82, 125, 0.35);
        }


        .login-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 26px rgba(19, 82, 125, 0.45);
        }


        .login-button:active {
            transform: translateY(0);
        }


        /* =====================================================
           ERROR
        ===================================================== */

        .error-message {
            background: #FEF0F0;

            color: #c0392b;

            border: 1px solid #F6C9C9;
            border-left: 4px solid #E05252;

            padding: 11px 13px;

            border-radius: 10px;

            font-size: 12px;
            font-weight: 600;

            margin-bottom: 15px;
        }


        .success-message {
            background: #EAF9F0;

            color: #1e7a3c;

            border: 1px solid #B9E6C9;
            border-left: 4px solid #2FA95C;

            padding: 11px 13px;

            border-radius: 10px;

            font-size: 12px;
            font-weight: 600;

            margin-bottom: 15px;
        }


        /* =====================================================
           FOOTER
        ===================================================== */

        .access-text {
            text-align: center;

            font-size: 11px;
            font-weight: 600;

            color: #93a5b6;

            margin-top: 20px;
        }


        /* =====================================================
           RESPONSIVE MOBILE
        ===================================================== */

        @media (max-width: 700px) {

            body {
                padding: 15px;
            }


            .login-container {
                width: 100%;

                min-height: auto;

                display: block;

                border-radius: 20px;
            }


            .left-side {
                width: 100%;

                min-height: 260px;

                padding: 32px 28px;

                justify-content: center;
            }


            .feature-list {
                margin-top: 18px;
            }


            .logo h1 {
                font-size: 24px;
            }


            .description {
                font-size: 11px;
            }


            .right-side {
                width: 100%;

                padding: 30px 25px;
            }


            .login-title {
                font-size: 22px;
            }

        }
    </style>

</head>


<body>


    <div class="login-container">


        <!-- =====================================================
             BAGIAN KIRI
        ====================================================== -->

        <div class="left-side">

            <div class="logo">

                <div class="logo-icon">
                    B
                </div>

                <div>
                    <h1>Binaro</h1>
                    <p>{{ config('binaro.nama_sekolah') }}</p>
                </div>

            </div>


            <div class="description">

                Selamat datang di Binaro! Silakan login untuk mengakses jadwal mata pelajaran,
                materi pembelajaran, notifikasi PR, dan ujian dengan lebih terstruktur dan efisien!

            </div>


            <ul class="feature-list">
                <li>
                    <span class="feature-badge">📅</span>
                    Jadwal & Absensi Terpadu
                </li>
                <li>
                    <span class="feature-badge">📚</span>
                    Materi & Notifikasi PR
                </li>
                <li>
                    <span class="feature-badge">📝</span>
                    Ujian Online & Nilai
                </li>
            </ul>

        </div>



        <!-- =====================================================
             BAGIAN KANAN
        ====================================================== -->

        <div class="right-side">


            <div class="login-title">
                Selamat Datang 👋
            </div>

            <div class="login-subtitle">
                Masuk untuk melanjutkan ke portal pembelajaran
            </div>


            <!-- =================================================
                 ERROR
            ================================================== -->

            @if (session('error'))

                <div class="error-message">

                    {{ session('error') }}

                </div>

            @endif

            @if (session('success'))

                <div class="success-message">

                    {{ session('success') }}

                </div>

            @endif

            @if ($errors->any())

                <div class="error-message">

                    {{ $errors->first() }}

                </div>

            @endif



            <!-- =================================================
                 FORM LOGIN
            ================================================== -->

            <form action="{{ route('login.post') }}" method="POST">

                @csrf


                <!-- =============================================
                     IDENTITAS (peran dikenali otomatis dari format:
                     NISN 10 digit, NIP, atau username)
                ============================================== -->

                <div class="form-group">

                    <label class="form-label" for="identity">NISN / NIP / Username</label>
                    <input type="text" name="identity" id="identity" class="input-box"
                        placeholder="NISN, NIP, atau username" autocomplete="username"
                        aria-label="NISN, NIP, atau username" value="{{ old('identity') }}" required>

                </div>


                <!-- =============================================
                     PASSWORD
                ============================================== -->

                <div class="form-group">

                    <label class="form-label" for="password">Password</label>
                    <div style="position: relative;">
                        <input type="password" name="password" id="password" class="input-box"
                            placeholder="••••••••" autocomplete="current-password"
                            aria-label="Password" required style="padding-right: 44px;">
                        <button type="button" id="toggle-password"
                            aria-label="Tampilkan atau sembunyikan password"
                            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #94a3b8; padding: 4px;">
                            <i class="fas fa-eye" id="toggle-password-icon"></i>
                        </button>
                    </div>

                </div>


                <!-- =============================================
                     LOGIN BUTTON
                ============================================== -->

                <button type="submit" class="login-button">
                    Masuk Sekarang →
                </button>


            </form>


            <div class="access-text" id="access-text">
                🔒 Akses untuk Siswa, Guru, dan Admin • {{ config('binaro.nama_sekolah') }}
            </div>


        </div>

    </div>



    <!-- =========================================================
         TOGGLE LIHAT PASSWORD
    ========================================================= -->

    <script>

        document.getElementById('toggle-password').addEventListener('click', function () {

            const input = document.getElementById('password');
            const ikon = document.getElementById('toggle-password-icon');
            const tampil = input.type === 'password';

            input.type = tampil ? 'text' : 'password';
            ikon.classList.toggle('fa-eye', !tampil);
            ikon.classList.toggle('fa-eye-slash', tampil);

        });

    </script>



</body>

</html>