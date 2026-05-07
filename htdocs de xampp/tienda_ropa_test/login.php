<?php
session_start();

if (isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $usuarios_validos = [
        'admin_tienda' => ['password' => 'Admin123!', 'rol' => 'Administrador', 'icon' => '👑'],
        'empleado_tienda' => ['password' => 'Empleado123!', 'rol' => 'Empleado', 'icon' => '👤'],
        'consulta_tienda' => ['password' => 'Consulta123!', 'rol' => 'Consulta', 'icon' => '🔍']
    ];

    if (isset($usuarios_validos[$username]) && $usuarios_validos[$username]['password'] === $password) {
        $_SESSION['usuario'] = $username;
        $_SESSION['rol'] = $usuarios_validos[$username]['rol'];
        $_SESSION['icon'] = $usuarios_validos[$username]['icon'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tienda de Ropa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="login-header">
                        <h2 class="mb-0">👕 TiendaRopa</h2>
                        <p class="mb-0 mt-2">Sistema de Gestión</p>
                    </div>
                    <div class="p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Usuario</label>
                                <input type="text" name="username" class="form-control" 
                                       placeholder="Ingresa tu usuario" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contraseña</label>
                                <input type="password" name="password" class="form-control" 
                                       placeholder="Ingresa tu contraseña" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                Iniciar Sesión
                            </button>
                        </form>

                        <hr class="my-4">
                        <div class="text-muted small">
                            <p class="mb-2"><strong>Usuarios de prueba:</strong></p>
                            <ul class="mb-0">
                                <li>admin_tienda / Admin123! (Admin)</li>
                                <li>empleado_tienda / Empleado123! (Empleado)</li>
                                <li>consulta_tienda / Consulta123! (Consulta)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>