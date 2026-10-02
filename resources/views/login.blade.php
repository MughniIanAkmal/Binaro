<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Binaro</title>


    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {
            font-family: Plus Jakarta Sans;
            background: #1d1d1d;
            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 20px;
        }


        /* =====================================================
           CONTAINER
        ===================================================== */

        .login-container {
            width: 900px;
            min-height: 520px;

            background: white;

            display: flex;

            border-radius: 0 25px 25px 0;

            overflow: hidden;

            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }


        /* =====================================================
           BAGIAN KIRI
        ===================================================== */

        .left-side {
            width: 50%;

            background:
                linear-gradient(rgba(15, 52, 70, 0.65),
                    rgba(15, 52, 70, 0.65)),
                url("{{ asset('images/sekolah.jpg') }}");

            background-size: cover;
            background-position: center;

            color: white;

            padding: 40px;

            display: flex;
            flex-direction: column;
            justify-content: center;
        }


        .logo {
            display: flex;
            align-items: center;
            gap: 10px;

            margin-bottom: 20px;
        }


        .logo-icon {
            width: 42px;
            height: 42px;

            background: #2874a6;

            border-radius: 8px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 22px;
        }


        .logo h1 {
            font-size: 28px;
        }


        .description {
            font-size: 13px;
            line-height: 1.6;

            max-width: 350px;
        }


        /* =====================================================
           BAGIAN KANAN
        ===================================================== */

        .right-side {
            width: 50%;

            padding: 50px 45px;

            display: flex;
            flex-direction: column;
            justify-content: center;
        }


        .login-title {
            font-size: 24px;
            font-weight: bold;

            margin-bottom: 25px;
        }


        /* =====================================================
           ROLE BUTTON
        ===================================================== */

        .role-container {
            display: flex;
            justify-content: center;

            gap: 15px;

            margin-bottom: 25px;
        }


        .role-button {
            border: none;
            background: white;

            border: 1px solid #2874a6;

            color: #222;

            padding: 8px 25px;

            border-radius: 5px;

            cursor: pointer;

            font-size: 12px;
            font-weight: bold;

            transition: 0.2s;
        }


        .role-button:hover {
            background: #2874a6;
            color: white;
        }


        .role-button.active {
            background: #2874a6;
            color: white;
        }


        /* =====================================================
           FORM
        ===================================================== */

        .form-group {
            margin-bottom: 12px;
        }


        .input-box {
            width: 100%;
            height: 40px;

            border: 1px solid #2874a6;

            border-radius: 5px;

            padding: 0 12px;

            font-size: 12px;
            font-weight: bold;

            outline: none;
        }


        .input-box::placeholder {
            font-weight: bold;
            color: #777;
        }


        .input-box:focus {
            border-color: #145a86;

            box-shadow:
                0 0 0 2px rgba(40, 116, 166, 0.15);
        }


        /* =====================================================
           LOGIN BUTTON
        ===================================================== */

        .login-button {
            width: 100%;

            height: 42px;

            border: none;

            border-radius: 5px;

            background: #2874a6;

            color: white;

            font-weight: bold;

            cursor: pointer;

            margin-top: 10px;

            transition: 0.2s;
        }


        .login-button:hover {
            background: #145a86;
        }


        /* =====================================================
           ERROR
        ===================================================== */

        .error-message {
            background: #ffe5e5;

            color: #c0392b;

            border: 1px solid #ffbaba;

            padding: 10px;

            border-radius: 5px;

            font-size: 12px;

            margin-bottom: 15px;
        }


        /* =====================================================
           FOOTER
        ===================================================== */

        .access-text {
            text-align: center;

            font-size: 10px;

            color: #888;

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

                border-radius: 15px;
            }


            .left-side {
                width: 100%;

                min-height: 220px;

                padding: 30px;

                justify-content: center;
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
                font-size: 20px;
            }


            .role-container {
                gap: 8px;
            }


            .role-button {
                padding: 8px 18px;
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
                    🎓
                </div>

                <h1>Binaro</h1>

            </div>


            <div class="description">

                Selamat datang di Binaro pembelajaran SD Negeri Kalitapen 1!
                Silakan login untuk mengakses berbagai informasi pembelajaran,
                seperti jadwal mata pelajaran, materi pembelajaran, notikasi PR,
                dan ujian. Ayo, login sekarang dan lanjutkan perjalanan akademik
                Anda dengan lebih terstruktur dan efisien!

            </div>

        </div>



        <!-- =====================================================
             BAGIAN KANAN
        ====================================================== -->

        <div class="right-side">


            <div class="login-title">
                Login
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

                <div class="error-message" style="background:#e6f9ed;color:#1e7a3c;border-color:#b6e6c6;">

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
                     ROLE
                ============================================== -->

                <div class="role-container">


                    <button type="button" class="role-button active" data-role="siswa" onclick="changeRole('siswa')">
                        Siswa
                    </button>


                    <button type="button" class="role-button" data-role="guru" onclick="changeRole('guru')">
                        Guru
                    </button>


                    <button type="button" class="role-button" data-role="admin" onclick="changeRole('admin')">
                        Admin
                    </button>

                </div>


                <!--
                    Role yang dipilih akan dikirim
                    ke LoginController
                -->

                <input type="hidden" name="role" id="role" value="{{ old('role', 'siswa') }}">


                <!-- =============================================
                     USERNAME / NAMA
                ============================================== -->

                <div class="form-group" id="username-group" hidden>

                    <input type="text" name="username" id="username" class="input-box" placeholder="Nama / NIP Admin"
                        value="{{ old('username') }}">

                </div>


                <!-- =============================================
                     IDENTITAS
                ============================================== -->

                <div class="form-group" id="identity-group">

                    <input type="text" name="identity" id="identity" class="input-box" placeholder="NISN"
                        value="{{ old('identity') }}" required>

                </div>


                <!-- =============================================
                     PASSWORD
                ============================================== -->

                <div class="form-group">

                    <input type="password" name="password" class="input-box" placeholder="Password" required>

                </div>


                <!-- =============================================
                     LOGIN BUTTON
                ============================================== -->

                <button type="submit" class="login-button">
                    Login
                </button>


            </form>


            <div class="access-text" id="access-text">
                Akses Untuk Siswa
            </div>


        </div>

    </div>



    <!-- =========================================================
         JAVASCRIPT ROLE
    ========================================================= -->

    <script>

        function changeRole(role) {


            /*
            |--------------------------------------------------------------------------
            | Simpan role
            |--------------------------------------------------------------------------
            */

            document.getElementById('role').value = role;
            document.getElementById('identity').required = role !== 'admin';
            document.getElementById('identity-group').hidden = role === 'admin';
            document.getElementById('username').required = role === 'admin';
            document.getElementById('username-group').hidden = role !== 'admin';


            /*
            |--------------------------------------------------------------------------
            | Ambil semua tombol role
            |--------------------------------------------------------------------------
            */

            const buttons =
                document.querySelectorAll('.role-button');


            /*
            |--------------------------------------------------------------------------
            | Hilangkan active dari semua tombol
            |--------------------------------------------------------------------------
            */

            buttons.forEach(function (button) {

                button.classList.remove('active');

            });


            /*
            |--------------------------------------------------------------------------
            | Aktifkan tombol yang dipilih
            |--------------------------------------------------------------------------
            */

            const activeButton =
                document.querySelector(
                    '.role-button[data-role="' + role + '"]'
                );


            if (activeButton) {

                activeButton.classList.add('active');

            }


            /*
            |--------------------------------------------------------------------------
            | Ambil input
            |--------------------------------------------------------------------------
            */

            const username =
                document.getElementById('username');

            const identity =
                document.getElementById('identity');

            const accessText =
                document.getElementById('access-text');


            /*
            |--------------------------------------------------------------------------
            | SISWA
            |--------------------------------------------------------------------------
            */

            if (role === 'siswa') {

                identity.placeholder =
                    'NISN Siswa';

                accessText.innerText =
                    'Akses Untuk Siswa';

            }


            /*
            |--------------------------------------------------------------------------
            | GURU
            |--------------------------------------------------------------------------
            */

            else if (role === 'guru') {

                identity.placeholder =
                    'NIP Guru';

                accessText.innerText =
                    'Akses Untuk Guru';

            }


            /*
            |--------------------------------------------------------------------------
            | ADMIN
            |--------------------------------------------------------------------------
            */

            else if (role === 'admin') {

                username.placeholder =
                    'Nama atau NIP Admin';

                accessText.innerText =
                    'Akses Untuk Admin';

            }

        }


        /*
        |--------------------------------------------------------------------------
        | TAMPILKAN ROLE SETELAH VALIDASI ERROR
        |--------------------------------------------------------------------------
        */

        document.addEventListener('DOMContentLoaded', function () {

            const currentRole =
                document.getElementById('role').value;

            changeRole(currentRole);

        });

    </script>


</body>

</html>