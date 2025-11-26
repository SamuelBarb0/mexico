<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            font-size: 24px;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }

        .user-name {
            color: #333;
            font-weight: 500;
        }

        .btn-logout {
            padding: 8px 20px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-logout:hover {
            background: #c0392b;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .welcome-section {
            background: white;
            border-radius: 10px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .welcome-section h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
        }

        .welcome-section p {
            color: #666;
            font-size: 16px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 15px;
        }

        .card-icon.purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .card-icon.blue {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .card-icon.green {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .card-icon.orange {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .card h3 {
            color: #333;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .card p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        .card-number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">Dashboard</div>
            <div class="navbar-menu">
                <div class="user-info">
                    <div class="user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                    <span class="user-name">{{ $user->name }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-logout">Cerrar Sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-section">
            <h1>Bienvenido, {{ $user->name }}</h1>
            <p>Este es tu panel de control. Aquí podrás gestionar toda la información de tu sistema.</p>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <div class="card-icon purple">📊</div>
                <h3>Estadísticas</h3>
                <p>Visualiza y analiza las métricas más importantes de tu sistema.</p>
                <div class="card-number">0</div>
            </div>

            <div class="card">
                <div class="card-icon blue">👥</div>
                <h3>Usuarios</h3>
                <p>Administra los usuarios y sus permisos en el sistema.</p>
                <div class="card-number">1</div>
            </div>

            <div class="card">
                <div class="card-icon green">✓</div>
                <h3>Tareas</h3>
                <p>Gestiona las tareas pendientes y completadas.</p>
                <div class="card-number">0</div>
            </div>

            <div class="card">
                <div class="card-icon orange">⚙️</div>
                <h3>Configuración</h3>
                <p>Personaliza las opciones de tu cuenta y sistema.</p>
            </div>
        </div>
    </div>
</body>
</html>
