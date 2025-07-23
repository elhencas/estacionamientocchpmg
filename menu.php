<?php
// menu.php
session_start();

// Configuración de credenciales (en producción usar base de datos)
$valid_username = "admin";
$valid_password_hash = password_hash("cchpmg", PASSWORD_DEFAULT); // Contraseña hasheada

// Verificar si el usuario ya está autenticado
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    // Usuario ya autenticado, mostrar menú completo
    $is_authenticated = true;
} else {
    $is_authenticated = false;
    
    // Procesar formulario de login si se envió
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        if (!empty($username) && !empty($password)) {
            if ($username === $valid_username && password_verify($password, $valid_password_hash)) {
                $_SESSION['authenticated'] = true;
                $_SESSION['username'] = $username;
                $is_authenticated = true;
            } else {
                $login_error = "Credenciales incorrectas";
            }
        } else {
            $login_error = "Por favor complete todos los campos";
        }
    }
}

// Procesar logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: menu.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .transition-all {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50">
    <header class="gradient-bg text-white shadow-lg">
        <div class="container mx-auto px-4 py-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold">
                    <i class="fas fa-cogs mr-2"></i>Sistema de Gestión
                </h1>
                <?php if ($is_authenticated): ?>
                <div class="flex items-center space-x-4">
                    <span class="text-sm"><i class="fas fa-user mr-1"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="?logout=1" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-all">
                        <i class="fas fa-sign-out-alt mr-1"></i> Salir
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <?php if (!$is_authenticated): ?>
        <!-- Formulario de Login -->
        <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden">
            <div class="gradient-bg py-4 px-6">
                <h2 class="text-xl font-semibold text-white">Inicio de Sesión</h2>
            </div>
            <div class="p-6">
                <?php if (isset($login_error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($login_error); ?></span>
                </div>
                <?php endif; ?>
                
                <form action="menu.php" method="post">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                            Usuario
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                               id="username" name="username" type="text" placeholder="Ingrese su usuario" required>
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                            Contraseña
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" 
                               id="password" name="password" type="password" placeholder="Ingrese su contraseña" required>
                    </div>
                    <div class="flex items-center justify-between">
                        <button class="gradient-bg hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition-all" 
                                type="submit" name="login">
                            <i class="fas fa-sign-in-alt mr-2"></i> Ingresar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <!-- Menú Principal -->
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800">Menú Principal Estacionamiento</h2>
            <p class="text-gray-600">Seleccione una opción del sistema</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
            <a href="admusuarios.php" class="block">
                <div class="bg-white rounded-lg overflow-hidden shadow-md card-hover transition-all h-full">
                    <div class="p-6">
                        <div class="text-blue-600 text-4xl mb-4">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <h3 class="font-bold text-xl mb-2 text-gray-800">Administración de Usuarios</h3>
                        <p class="text-gray-600">Gestión completa de usuarios del sistema</p>
                    </div>
                </div>
            </a>
            
            <a href="index.php" class="block">
                <div class="bg-white rounded-lg overflow-hidden shadow-md card-hover transition-all h-full">
                    <div class="p-6">
                        <div class="text-green-600 text-4xl mb-4">
                            <i class="fas fa-home"></i>
                        </div>
                        <h3 class="font-bold text-xl mb-2 text-gray-800">Página Principal</h3>
                        <p class="text-gray-600">Acceso al sistema principal Estacionamientos</p>
                    </div>
                </div>
            </a>
        </div>
        <?php endif; ?>
    </main>

    <footer class="bg-gray-800 text-white py-6 mt-12">
        <div class="container mx-auto px-4 text-center">
            <p>Sistema de Gestión &copy; <?php echo date('Y'); ?></p>
            <p class="text-gray-400 text-sm mt-2">Versión 1.0.0</p>
        </div>
    </footer>
</body>
</html>
