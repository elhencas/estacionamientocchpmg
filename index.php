<?php
session_start();
include 'db.php';

// Establecer la zona horaria a GMT-6
date_default_timezone_set('America/Belize'); // GMT-6

// Verificar si el usuario ya ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php"); // Redirigir al formulario de login
    exit;
}

// Manejo de registro de entrada/salida
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['salida'])) {
        $placa = $_POST['placa_salida'];
        $hora_final = date('Y-m-d H:i:s');

        $stmt = $pdo->prepare("UPDATE registros SET hora_final = ?, auto_salio = TRUE WHERE placa = ? AND auto_salio = FALSE");
        $stmt->execute([$hora_final, $placa]);
        echo "<div class='alert alert-success'>Salida registrada con éxito.</div>";
    } else {
        $placa = $_POST['placa'];
        $nombre = $_POST['nombre'];
        $hora_inicio = date('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO registros (placa, nombre, hora_inicio) VALUES (?, ?, ?)");
        $stmt->execute([$placa, $nombre, $hora_inicio]);
        echo "<div class='alert alert-success'>Auto registrado con éxito.</div>";
    }
}

// Obtener filtro de estado
$filtro_estado = $_GET['estado'] ?? 'todos'; // 'todos', 'ingresados', 'salidos'

// Consulta SQL basada en el filtro
$sql = "SELECT id, placa, nombre, hora_inicio, hora_final, 
       TIMESTAMPDIFF(MINUTE, hora_inicio, IFNULL(hora_final, NOW())) as minutos, 
       auto_salio 
       FROM registros 
       WHERE DATE(hora_inicio) = CURDATE()";

if ($filtro_estado === 'ingresados') {
    $sql .= " AND auto_salio = FALSE";
} elseif ($filtro_estado === 'salidos') {
    $sql .= " AND auto_salio = TRUE";
}

$sql .= " ORDER BY hora_inicio DESC";

$stmt = $pdo->query($sql);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar vehículos por estado
$total_ingresados = count(array_filter($registros, fn($r) => !$r['auto_salio']));
$total_salidos = count(array_filter($registros, fn($r) => $r['auto_salio']));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Registro de Estacionamiento</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Estilos personalizados para los radio buttons */
        .radio-toolbar {
            margin: 10px 0;
        }
        
        .radio-toolbar input[type="radio"] {
            opacity: 0;
            position: fixed;
            width: 0;
        }
        
        .radio-toolbar label {
            display: inline-block;
            background-color: #f1f1f1;
            padding: 8px 15px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
            cursor: pointer;
        }
        
        .radio-toolbar input[type="radio"]:checked + label {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Registro de Estacionamiento</h1>
        
        <!-- Botón de Cerrar Sesión -->
        <div class="text-right mb-3">
            <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
        </div>

        <!-- Contadores -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Hoy</h5>
                        <p class="card-text display-4"><?= count($registros) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Ingresados</h5>
                        <p class="card-text display-4"><?= $total_ingresados ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Salidos</h5>
                        <p class="card-text display-4"><?= $total_salidos ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Radio Buttons para filtrar -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Filtrar por estado:</h5>
            </div>
            <div class="card-body">
                <form method="get" id="filter-form">
                    <div class="radio-toolbar">
                        <input type="radio" id="radioTodos" name="estado" value="todos" 
                            <?= ($filtro_estado == 'todos') ? 'checked' : '' ?>>
                        <label for="radioTodos">Todos</label>
                        
                        <input type="radio" id="radioIngresados" name="estado" value="ingresados" 
                            <?= ($filtro_estado == 'ingresados') ? 'checked' : '' ?>>
                        <label for="radioIngresados">Ingresados</label>
                        
                        <input type="radio" id="radioSalidos" name="estado" value="salidos" 
                            <?= ($filtro_estado == 'salidos') ? 'checked' : '' ?>>
                        <label for="radioSalidos">Salidos</label>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // Auto-submit form when radio button changes
            document.querySelectorAll('.radio-toolbar input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.getElementById('filter-form').submit();
                });
            });
        </script>
        
        <!-- Formularios de entrada/salida -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5>Registrar Entrada</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label for="placa">Placa:</label>
                                <input type="text" class="form-control" name="placa" required>
                            </div>
                            <div class="form-group">
                                <label for="nombre">Nombre:</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                Registrar Entrada
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5>Registrar Salida</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label for="placa_salida">Placa:</label>
                                <input type="text" class="form-control" name="placa_salida" required>
                            </div>
                            <button type="submit" name="salida" class="btn btn-warning">
                                Registrar Salida
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Botón para exportar a texto -->
                <div class="mt-4">
                    <form action="exportar.php" method="post">
                        <div class="form-group">
                            <label for="fecha_exportacion">Seleccione el día a exportar:</label>
                            <input type="date" class="form-control" id="fecha_exportacion" name="fecha_exportacion" 
                            value="<?php echo date('Y-m-d'); ?>" required> 
                        </div>
                        <button type="submit" class="btn btn-info mt-2">
                            <i class="fas fa-file-export"></i> Exportar Datos
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabla de registros -->
        <div class="card">
            <div class="card-header">
                <h5>Registros del Día</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Placa</th>
                            <th>Nombre</th>
                            <th>Hora Entrada</th>
                            <th>Hora Salida</th>
                            <th>Tiempo (min)</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registros)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No hay registros para mostrar</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($registros as $registro): ?>
                                <tr class="<?= $registro['auto_salio'] ? 'table-success' : 'table-info' ?>">
                                    <td><?= $registro['id'] ?></td>
                                    <td><?= $registro['placa'] ?></td>
                                    <td><?= $registro['nombre'] ?></td>
                                    <td><?= date('H:i:s', strtotime($registro['hora_inicio'])) ?></td>
                                    <td><?= $registro['hora_final'] ? date('H:i:s', strtotime($registro['hora_final'])) : '--' ?></td>
                                    <td><?= $registro['minutos'] ?? '0' ?></td>
                                    <td>
                                        <?php if ($registro['auto_salio']): ?>
                                            <span class="badge badge-success">Salió</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">Estacionado</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
