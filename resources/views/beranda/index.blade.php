<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    <title>Beranda - Sahabat Belajar</title>

    <style>
        /* =====================================================
           PALET WARNA
        ===================================================== */

        :root {
            --blue: #1E6091;
            --white: #FFFFFF;
            --orange: #FEA619;
            --navy: #0D1C2E;
            --cream: #FFEED3;
            --black: #000000;
            --blue-light: #E4EDFF;
            --background: #FBFBFB;
            --gray: #41474F;
            --blue-soft: #E6EEFF;
        }


        /* =====================================================
           RESET
        ===================================================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            min-height: 100%;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: var(--blue-soft);
            color: var(--navy);
        }

        a {
            text-decoration: none;
            color: inherit;
        }


        /* =====================================================
           APP
        ===================================================== */

        .app {
            width: 100%;
            max-width: none;
            min-height: 100vh;

            margin: 0;

            background: var(--background);

            position: relative;

            padding-bottom: 20px;
        }


        /* =====================================================
           SIDEBAR DESKTOP
        ===================================================== */

        .bottom-nav {
            position: fixed;

            top: 0;
            left: 0;
            bottom: 0;

            width: 230px;

            background: var(--white);

            display: flex;
            flex-direction: column;

            padding: 22px 15px;

            z-index: 1000;

            border-right: 1px solid var(--blue-soft);

            box-shadow: 2px 0 8px rgba(13, 28, 46, 0.05);

            transition: opacity 0.3s ease;
        }


        /* =====================================================
           LOGO SIDEBAR
        ===================================================== */

        .bottom-nav::before {
            content: "SAHABAT BELAJAR";

            display: block;

            color: var(--blue);

            font-size: 14px;
            font-weight: 800;

            padding: 10px 12px 24px;

            margin-bottom: 15px;

            border-bottom: 1px solid var(--blue-soft);
        }


        /* =====================================================
           NAV ITEM
        ===================================================== */

        .nav-item {
            width: 100%;
            min-height: 45px;

            color: var(--navy);

            display: flex;
            flex-direction: row;

            align-items: center;

            gap: 12px;

            padding: 0 13px;

            margin-bottom: 6px;

            border-radius: 8px;

            font-size: 12px;
            font-weight: 700;

            transition: .2s;
        }


        .nav-item:hover {
            background: var(--blue-light);

            color: var(--blue);
        }


        .nav-item.active {
            background: var(--blue);

            color: var(--white);
        }


        .nav-icon {
            width: 22px;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 17px;

            color: var(--blue);
        }


        .nav-item.active .nav-icon {
            color: var(--white);
        }


        /* =====================================================
           HEADER
        ===================================================== */

        .header {
            min-height: 105px;

            background: var(--blue);

            color: var(--white);

            padding: 18px 45px 16px;

            margin-left: 230px;

            position: relative;

            transition: margin-left 0.3s ease;
        }


        /* =====================================================
           TOMBOL MENU DI HEADER
        ===================================================== */

        .header-toggle {
            position: absolute;

            top: 18px;
            left: 18px;

            width: 42px;
            height: 42px;

            border: none;

            background: transparent;

            color: var(--white);

            font-size: 27px;
            font-weight: bold;

            cursor: pointer;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 8px;

            z-index: 20;

            transition: 0.2s;
        }


        .header-toggle:hover {
            background: rgba(255, 255, 255, 0.12);
        }


        /* Supaya isi header tidak menabrak tombol */

        .header {
            padding-left: 75px;
        }


        .header-top {
            display: flex;

            align-items: center;

            justify-content: space-between;
        }


        .brand {
            display: flex;

            align-items: center;

            gap: 12px;
        }


        .brand-icon {
            width: 43px;
            height: 43px;

            border-radius: 11px;

            background: var(--orange);

            color: var(--white);

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 19px;

            font-weight: 900;
        }


        .brand-text {
            line-height: 1.05;
        }


        .brand-title {
            color: var(--orange);

            font-size: 11px;

            font-weight: 800;

            text-transform: uppercase;
        }


        .brand-name {
            color: var(--white);

            font-size: 18px;

            font-weight: 800;

            margin-top: 4px;
        }


        .header-actions {
            display: flex;

            align-items: center;

            gap: 20px;
        }


        .notification {
            color: var(--white);

            font-size: 24px;

            position: relative;
        }


        .notification-dot {
            position: absolute;

            top: 0;
            right: -2px;

            width: 8px;
            height: 8px;

            background: var(--orange);

            border-radius: 50%;
        }


        .profile-circle {
            width: 43px;
            height: 43px;

            background: var(--cream);

            color: var(--blue);

            border-radius: 50%;

            border: 2px solid var(--white);

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 15px;

            font-weight: 800;
        }


        .student-info {
            margin-top: 13px;
        }


        .student-info h1 {
            color: var(--white);

            font-size: 20px;

            font-weight: 800;

            margin-bottom: 5px;
        }


        .student-info p {
            color: var(--white);

            font-size: 11px;

            font-weight: 600;
        }


        /* =====================================================
           CONTENT
        ===================================================== */

        .content {
            margin-left: 230px;

            padding: 25px 45px 25px;

            width: calc(100% - 230px);

            transition:
                margin-left 0.3s ease,
                width 0.3s ease;
        }


        /* =====================================================
           SIDEBAR DITUTUP
        ===================================================== */

        .app.sidebar-closed .bottom-nav {
            display: none;
        }


        .app.sidebar-closed .header {
            margin-left: 0;
        }


        .app.sidebar-closed .content {
            margin-left: 0;

            width: 100%;
        }


        /* =====================================================
           SECTION
        ===================================================== */

        .section {
            margin-bottom: 20px;
        }


        .section-title {
            margin-bottom: 9px;
        }


        .section-title h2 {
            color: var(--navy);

            font-size: 17px;

            font-weight: 800;
        }


        .section-title a {
            color: var(--blue);

            font-size: 12px;

            font-weight: 700;
        }


        /* =====================================================
           MENU FITUR
        ===================================================== */

        .quick-menu {
            display: grid;

            grid-template-columns: repeat(3, 1fr);

            gap: 15px;

            width: 100%;
        }


        .quick-card {
            height: 88px;

            border-radius: 13px;

            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            gap: 7px;

            color: var(--white);

            font-size: 14px;

            font-weight: 800;

            box-shadow:
                0 3px 8px rgba(13, 28, 46, 0.16);

            transition: .2s;
        }


        .quick-card:hover {
            transform: translateY(-3px);
        }


        .quick-card.blue {
            background: var(--blue);
        }


        .quick-card.orange {
            background: var(--orange);
        }


        .quick-icon {
            font-size: 28px;

            line-height: 1;
        }


        /* =====================================================
           DESKTOP CONTENT
        ===================================================== */

        @media (min-width: 901px) {

            .content {
                display: block;
            }


            .content>.section:nth-child(2) {
                width: 58%;

                display: inline-block;

                vertical-align: top;

                margin-right: 1.5%;
            }


            .content>.section:nth-child(3) {
                width: 40%;

                display: inline-block;

                vertical-align: top;
            }


            .content>.section:nth-child(3) .subject-grid {
                grid-template-columns: repeat(2, 1fr);
            }

        }


        /* =====================================================
           TUGAS
        ===================================================== */

        .task-list {
            display: flex;

            flex-direction: column;

            gap: 11px;
        }


        .task-card {
            background: var(--white);

            border-radius: 12px;

            overflow: hidden;

            box-shadow:
                0 2px 7px rgba(13, 28, 46, 0.12);

            border: 1px solid var(--blue-soft);
        }


        .task-main {
            min-height: 58px;

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 8px 12px;
        }


        .task-icon {
            width: 38px;
            height: 38px;

            border-radius: 8px;

            background: var(--blue-light);

            color: var(--blue);

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 16px;
        }


        .task-info {
            min-width: 0;

            flex: 1;
        }


        .task-mapel {
            color: var(--blue);

            font-size: 10px;

            font-weight: 800;

            text-transform: uppercase;

            margin-bottom: 5px;
        }


        .task-title {
            color: var(--navy);

            font-size: 14px;

            font-weight: 700;

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis;
        }


        .task-footer {
            min-height: 34px;
            height: 34px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 10px;

            color: var(--white);
        }


        .task-footer.blue {
            background: var(--blue);
        }


        .task-footer.orange {
            background: var(--orange);
        }


        .deadline {
            display: flex;

            align-items: center;

            font-size: 10px;

            font-weight: 600;

            white-space: nowrap;
        }


        .open-button {
            display: flex;

            align-items: center;

            justify-content: center;

            flex-shrink: 0;

            min-width: 68px;

            height: 26px;

            background: var(--white);

            color: var(--blue);

            border-radius: 13px;

            padding: 0 12px;

            font-size: 10px;

            font-weight: 800;

            margin-left: auto;
        }


        .all-tasks {
            margin-top: 12px;
        }


        .all-tasks a {
            color: var(--blue);

            font-size: 12px;

            font-weight: 700;
        }


        /* =====================================================
           MATA PELAJARAN
        ===================================================== */

        .subject-grid {
            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 14px;
        }


        .subject-card {
            background: var(--white);

            border-radius: 11px;

            min-height: 125px;

            padding: 11px;

            box-shadow:
                0 2px 7px rgba(13, 28, 46, 0.12);

            border: 1px solid var(--blue-soft);
        }


        .subject-top {
            margin-bottom: 11px;
        }


        .subject-icon {
            width: 38px;
            height: 38px;

            border-radius: 9px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 17px;

            font-weight: 800;
        }


        .subject-icon.blue {
            background: var(--blue-light);

            color: var(--blue);
        }


        .subject-icon.orange {
            background: var(--cream);

            color: var(--orange);
        }


        .badge {
            padding: 5px 8px;

            border-radius: 6px;

            font-size: 9px;

            font-weight: 700;

            display: inline-block;

            margin-top: 4px;
        }


        .badge.blue {
            background: var(--blue-light);

            color: var(--blue);
        }


        .badge.orange {
            background: var(--cream);

            color: var(--orange);
        }


        .subject-name {
            color: var(--navy);

            font-size: 14px;

            font-weight: 800;

            margin-bottom: 6px;
        }


        .subject-detail {
            color: var(--gray);

            font-size: 10px;

            margin-bottom: 12px;
        }


        .progress {
            width: 100%;

            height: 7px;

            background: var(--blue-soft);

            border-radius: 10px;

            overflow: hidden;
        }


        .progress-bar {
            height: 100%;

            border-radius: 10px;
        }


        .progress-bar.blue {
            background: var(--blue);
        }


        .progress-bar.orange {
            background: var(--orange);
        }


        /* =====================================================
           TABLET
        ===================================================== */

        @media (max-width: 900px) {

            body {
                background: var(--background);
            }


            .header {
                margin-left: 0;

                min-height: 110px;

                padding: 18px 25px;

                padding-left: 65px;
            }


            .content {
                margin-left: 0;

                width: 100%;

                padding: 20px 25px 80px;
            }


            .bottom-nav {
                position: fixed;

                left: 0;
                right: 0;

                top: auto;
                bottom: 0;

                width: 100%;

                height: 62px;

                min-height: 62px;

                padding: 0;

                background: var(--blue);

                display: grid;

                grid-template-columns: repeat(5, 1fr);

                flex-direction: row;

                border: none;

                box-shadow:
                    0 -2px 8px rgba(13, 28, 46, 0.15);
            }


            .bottom-nav::before {
                display: none;
            }


            .nav-item {
                width: auto;

                min-height: auto;

                margin: 0;

                padding: 5px 2px;

                border-radius: 0;

                color: var(--white);

                display: flex;

                flex-direction: column;

                justify-content: center;

                align-items: center;

                gap: 3px;

                font-size: 7px;
            }


            .nav-item:hover {
                background: transparent;

                color: var(--white);
            }


            .nav-item.active {
                background: transparent;

                color: var(--orange);
            }


            .nav-icon {
                width: auto;

                font-size: 16px;

                color: var(--white);
            }


            .nav-item.active .nav-icon {
                color: var(--orange);
            }


            .subject-grid {
                grid-template-columns: repeat(2, 1fr);
            }

        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 600px) {

            .header {
                min-height: 96px;

                padding: 12px 14px 10px;

                padding-left: 55px;
            }


            .header-toggle {
                top: 12px;

                left: 8px;

                width: 38px;
                height: 38px;

                font-size: 24px;
            }


            .brand-icon {
                width: 28px;
                height: 28px;

                font-size: 13px;
            }


            .brand-title {
                font-size: 8px;
            }


            .brand-name {
                font-size: 12px;
            }


            .student-info {
                margin-top: 10px;
            }


            .student-info h1 {
                font-size: 14px;
            }


            .student-info p {
                font-size: 7px;
            }


            .content {
                padding: 11px 9px 75px;
            }


            .section {
                margin-bottom: 14px;
            }


            .section-title {
                margin-bottom: 7px;
            }


            .section-title h2 {
                font-size: 10px;
            }


            .section-title a {
                font-size: 7px;
            }


            /* MENU */

            .quick-menu {
                gap: 5px;
            }


            .quick-card {
                height: 51px;

                border-radius: 9px;

                gap: 4px;

                font-size: 7px;
            }


            .quick-icon {
                font-size: 15px;
            }


            /* TUGAS */

            .task-list {
                gap: 7px;
            }


            .task-main {
                height: 48px;

                min-height: 48px;

                padding: 6px 8px;

                gap: 7px;
            }


            .task-icon {
                width: 22px;
                height: 22px;

                border-radius: 5px;

                font-size: 10px;
            }


            .task-mapel {
                font-size: 6px;

                margin-bottom: 2px;
            }


            .task-title {
                font-size: 8px;
            }


            .task-footer {
                height: 20px;

                min-height: 20px;

                padding: 0 7px;
            }


            .deadline {
                font-size: 6px;
            }


            .open-button {
                font-size: 6px;

                padding: 2px 7px;
            }


            .all-tasks {
                margin-top: 9px;
            }


            .all-tasks a {
                font-size: 7px;
            }


            /* MAPEL */

            .subject-grid {
                grid-template-columns: repeat(2, 1fr);

                gap: 6px;
            }


            .subject-card {
                min-height: 94px;

                padding: 7px;

                border-radius: 8px;
            }


            .subject-top {
                margin-bottom: 8px;
            }


            .subject-icon {
                width: 23px;
                height: 23px;

                border-radius: 5px;

                font-size: 10px;
            }


            .badge {
                padding: 3px 5px;

                font-size: 6px;
            }


            .subject-name {
                font-size: 8px;

                margin-bottom: 3px;
            }


            .subject-detail {
                font-size: 6px;

                margin-bottom: 7px;
            }


            .progress {
                height: 4px;
            }


            /* NAV MOBILE */

            .bottom-nav {
                height: 58px;

                min-height: 58px;
            }


            .nav-item {
                font-size: 6px;

                gap: 3px;
            }


            .nav-icon {
                font-size: 14px;
            }

        }
    </style>
</head>


<body>

    <div class="app" id="app">


        <!-- =====================================================
             HEADER
        ====================================================== -->

        <header class="header">


            <!-- TOMBOL SIDEBAR -->

            <button class="header-toggle" id="sidebarToggle" type="button" aria-label="Buka atau tutup sidebar">

                ☰

            </button>


            <div class="header-top">


                <div class="brand">

                    <div class="brand-icon">
                        ★
                    </div>


                    <div class="brand-text">

                        <div class="brand-title">
                            SAHABAT BELAJAR
                        </div>


                        <div class="brand-name">
                            Beranda
                        </div>

                    </div>

                </div>


                <div class="header-actions">

                    <div class="notification">

                        ♟

                        <span class="notification-dot"></span>

                    </div>


                    <div class="profile-circle">

                        {{ strtoupper(substr($nama, 0, 1)) }}

                    </div>

                </div>

            </div>


            <div class="student-info">

                <h1>
                    Halo, {{ $nama }}!
                </h1>


                <p>
                    ✦ {{ $kelas }} • {{ $sekolah }}
                </p>

            </div>

        </header>



        <!-- =====================================================
             CONTENT
        ====================================================== -->

        <main class="content">


            <!-- =================================================
                 MENU
            ================================================== -->

            <section class="section">


                <div class="section-title">

                    <h2>
                        Menu
                    </h2>

                </div>


                <div class="quick-menu">


                    <a href="#" class="quick-card orange">

                        <span class="quick-icon">
                            ▣
                        </span>

                        <span>
                            Mapel
                        </span>

                    </a>


                    <a href="#" class="quick-card blue">

                        <span class="quick-icon">
                            ▤
                        </span>

                        <span>
                            Ujian
                        </span>

                    </a>


                    <a href="#" class="quick-card blue">

                        <span class="quick-icon">
                            ▦
                        </span>

                        <span>
                            Jadwal
                        </span>

                    </a>

                </div>

            </section>



            <!-- =================================================
                 TUGAS
            ================================================== -->

            <section class="section">


                <div class="section-title">

                    <h2>
                        Tugas & PR Mendatang
                    </h2>

                </div>


                <div class="task-list">


                    @foreach ($tugas as $item)

                        <div class="task-card">


                            <div class="task-main">


                                <div class="task-icon">
                                    ▣
                                </div>


                                <div class="task-info">


                                    <div class="task-mapel">

                                        {{ $item['mapel'] }}

                                    </div>


                                    <div class="task-title">

                                        {{ $item['judul'] }}

                                    </div>


                                </div>

                            </div>


                            <div class="task-footer {{ $item['warna'] }}">


                                <span class="deadline">

                                    ⏱ Tenggat: {{ $item['deadline'] }}

                                </span>


                                <a href="#" class="open-button">

                                    Buka ›

                                </a>

                            </div>

                        </div>

                    @endforeach


                </div>


                <div class="all-tasks">

                    <a href="#">

                        Lihat Semua Tugas ({{ $tugas_total }}) →

                    </a>

                </div>

            </section>



            <!-- =================================================
                 MATA PELAJARAN
            ================================================== -->

            <section class="section">


                <div class="section-title">

                    <h2>
                        Mata Pelajaran
                    </h2>


                    <a href="#">
                        Semua
                    </a>

                </div>


                <div class="subject-grid">


                    @foreach ($mapel as $item)


                        <div class="subject-card">


                            <div class="subject-top">


                                <div class="subject-icon {{ $item['warna'] }}">

                                    {{ $item['icon'] }}

                                </div>


                                <span class="badge {{ $item['warna'] }}">

                                    {{ $item['badge'] }}

                                </span>

                            </div>


                            <div class="subject-name">

                                {{ $item['nama'] }}

                            </div>


                            <div class="subject-detail">

                                {{ $item['materi'] }}

                            </div>


                            <div class="progress">


                                <div class="progress-bar {{ $item['warna'] }}" style="width: {{ $item['progress'] }}%;">

                                </div>

                            </div>

                        </div>


                    @endforeach


                </div>

            </section>


        </main>



        <!-- =====================================================
             SIDEBAR
        ====================================================== -->

        <nav class="bottom-nav">


            <a href="{{ route('beranda') }}" class="nav-item active">

                <span class="nav-icon">
                    ⌂
                </span>

                <span>
                    Beranda
                </span>

            </a>


            <a href="#" class="nav-item">

                <span class="nav-icon">
                    ▣
                </span>

                <span>
                    Mapel
                </span>

            </a>


            <a href="#" class="nav-item">

                <span class="nav-icon">
                    ?
                </span>

                <span>
                    Ujian
                </span>

            </a>


            <a href="#" class="nav-item">

                <span class="nav-icon">
                    ▦
                </span>

                <span>
                    Jadwal
                </span>

            </a>


            <a href="#" class="nav-item">

                <span class="nav-icon">
                    ♙
                </span>

                <span>
                    Profil
                </span>

            </a>


        </nav>


    </div>



    <!-- =====================================================
         JAVASCRIPT SIDEBAR
    ====================================================== -->

    <script>

        const sidebarToggle =
            document.getElementById('sidebarToggle');

        const app =
            document.getElementById('app');


        sidebarToggle.addEventListener('click', function () {

            app.classList.toggle('sidebar-closed');

        });

    </script>


</body>

</html>