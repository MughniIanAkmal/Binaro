<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Profil Siswa' }} - {{ config('app.name', 'Binaro') }}</title>
    <!-- Bootstrap 5 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --bs-primary: #13527D;
            --bs-primary-rgb: 19, 82, 125;
        }
        body {
            background-color: #f1f5f9;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            min-height: 100vh;
        }
        .profile-container {
            max-width: 520px;
            margin: 0 auto;
        }
        .btn-primary {
            background-color: #13527D;
            border-color: #13527D;
        }
        .btn-primary:hover {
            background-color: #0E3D5D;
            border-color: #0E3D5D;
        }
        .text-primary {
            color: #13527D !important;
        }
        .border-primary-subtle {
            border-color: #b9d7ea !important;
        }
    </style>
</head>
<body class="bg-light">

    <div class="profile-container px-3 py-4">
        @yield('content')
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>