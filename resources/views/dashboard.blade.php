<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Binaro</title>


    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {
            font-family: Arial, sans-serif;

            background: #f5f7fa;

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;
        }


        .dashboard {
            background: white;

            width: 500px;

            padding: 40px;

            border-radius: 15px;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.1);

            text-align: center;
        }


        h1 {
            color: #2874a6;

            margin-bottom: 20px;
        }


        .info {
            margin-bottom: 25px;

            line-height: 1.8;
        }


        .role {
            color: #2874a6;

            font-weight: bold;

            text-transform: uppercase;
        }


        .logout {
            background: #2874a6;

            color: white;

            border: none;

            padding: 12px 30px;

            border-radius: 5px;

            cursor: pointer;
        }


        .logout:hover {
            background: #145a86;
        }
    </style>

</head>


<body>


    <div class="dashboard">

        <h1>
            Binaro
        </h1>


        <div class="info">

            <p>
                Selamat datang,
                <strong>{{ session('nama') }}</strong>
            </p>

            <p>
                Username:
                {{ session('username') }}
            </p>

            <p>
                Role:
                <span class="role">
                    {{ session('role') }}
                </span>
            </p>

        </div>


        <form action="{{ route('logout') }}" method="POST">

            @csrf

            <button type="submit" class="logout">
                Logout
            </button>

        </form>

    </div>


</body>

</html>