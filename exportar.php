<?php
session_start();
include 'db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}

// Verificar si se envió la fecha
if (!isset($_POST['fecha_exportacion'])) {
    $_SESSION['error'] = "Debe seleccionar una fecha.";
    header("Location: index.php");
    exit;
}

$fecha = $_POST['fecha_exportacion'];

// Consulta que filtra por fecha (considerando solo el día, no la hora)
$stmt = $pdo->prepare("SELECT * FROM registros 
                      WHERE DATE(hora_inicio) = ? 
                      OR (hora_final IS NOT NULL AND DATE(hora_final) = ?)");
$stmt->execute([$fecha, $fecha]);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($registros)) {
    $_SESSION['error'] = "No hay registros para la fecha seleccionada.";
    header("Location: index.php");
    exit;
}

header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="registros_'.str_replace('-', '', $fecha).'.txt"');

echo "PLACA\tNOMBRE\tENTRADA\tSALIDA\tESTADO\tTIEMPO ESTACIONADO (min)\n";

foreach ($registros as $registro) {
    $hora_entrada = new DateTime($registro['hora_inicio']);
    $hora_salida = $registro['hora_final'] ? new DateTime($registro['hora_final']) : null;
    
    $tiempo = 'N/A';
    if ($hora_salida) {
        $diff = $hora_entrada->diff($hora_salida);
        $tiempo = $diff->h * 60 + $diff->i; // Convertir a minutos
    }

    echo implode("\t", [
        $registro['placa'],
        $registro['nombre'],
        $registro['hora_inicio'],
        $registro['hora_final'] ?: 'PENDIENTE',
        $registro['auto_salio'] ? 'SALIÓ' : 'ESTACIONADO',
        $tiempo
    ]) . "\n";
}
exit;
?>
