<?php 
include 'check_auth.php';
include 'permisos.php';
include 'db.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Bitácora del Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container main-container">
        <h2 class="page-title">📋 Bitácora del Sistema</h2>
        
        <div class="card card-custom">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Tabla Afectada</th>
                        <th>Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM bitacora_sistema ORDER BY fecha_hora DESC";
                    $res = $conn->query($sql);
                    while($row = $res->fetch()){
                        echo "<tr>
                                <td>{$row['id_bitacora']}</td>
                                <td>{$row['usuario']}</td>
                                <td>{$row['accion']}</td>
                                <td><span class='badge bg-secondary'>{$row['tabla_afectada']}</span></td>
                                <td>{$row['fecha_hora']}</td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>