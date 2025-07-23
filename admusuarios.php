<?php
session_start();
include 'db.php';



// Verificar si hay usuarios en la base de datos
$stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
$user_count = $stmt->fetchColumn();

if ($user_count == 0) {
    // Si no hay usuarios, mostrar formulario para crear el usuario inicial
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_initial_user'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO usuarios (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashed_password]);
        $message = "Usuario inicial creado con éxito.";
    }
} else {
    // Manejo de inclusión y eliminación de usuarios
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['add_user'])) {
            // Inclusión de usuario
            $username = $_POST['username'];
            $password = $_POST['password'];
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO usuarios (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hashed_password]);
            $message = "Usuario agregado con éxito.";
        } elseif (isset($_POST['delete_user'])) {
            // Eliminación de usuario
            $username = $_POST['username_delete'];

            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE username = ?");
            $stmt->execute([$username]);
            $message = "Usuario eliminado con éxito.";
        }
    }
}

// Obtener usuarios de la base de datos
$stmt = $pdo->query("SELECT * FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Administración de Usuarios</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Administración de Usuarios</h1>

        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($user_count == 0): ?>
            <h2 class="mt-5">Crear Usuario Inicial</h2>
            <form method="POST" class="mb-4">
                <div class="form-group">
                    <label for="username">Nombre de Usuario:</label>
                    <input type="text" class="form-control" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button type="submit" name="create_initial_user" class="btn btn-success">Crear Usuario Inicial</button>
            </form>
        <?php else: ?>
            <h2 class="mt-5">Agregar Usuario</h2>
            <form method="POST" class="mb-4">
                <div class="form-group">
                    <label for="username">Nombre de Usuario:</label>
                    <input type="text" class="form-control" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button type="submit" name="add_user" class="btn btn-success">Agregar Usuario</button>
            </form>

            <h2 class="mt-5">Eliminar Usuario</h2>
            <form method="POST" class="mb-4">
                <div class="form-group">
                    <label for="username_delete">Nombre de Usuario:</label>
                    <input type="text" class="form-control" name="username_delete" required>
                </div>
                <button type="submit" name="delete_user" class="btn btn-danger">Eliminar Usuario</button>
            </form>
        <?php endif; ?>

        <h2 class="mt-5">Lista de Usuarios</h2>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre de Usuario</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo $usuario['id']; ?></td>
                        <td><?php echo $usuario['username']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
