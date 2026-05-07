<?php 
include 'check_auth.php';
include 'permisos.php';
include 'db.php'; 

// Usar SP para actualizar precio con transacción
if(isset($_POST['actualizar_precio'])){
    try {
        // Establecer variable de usuario para el trigger
        $conn->query("SET @usuario_app = '".$_SESSION['usuario']."'");
        
        $conn->beginTransaction();
        
        // SP: Actualizar precio y registrar en historial (ahora con usuario)
        $stmt = $conn->prepare("CALL actualizar_precio_prenda(?, ?, ?, ?)");
        $stmt->execute([$_POST['id_prenda'], $_POST['nuevo_precio'], $_POST['id_empleado'], $_SESSION['usuario']]);
        
        $conn->commit();
        header("Location: actualizaciones.php?msg=actualizado");
    } catch(Exception $e) {
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Historial de Precios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container main-container">
        <h2 class="page-title">💰 Historial de Actualización de Precios</h2>
        
        <?php if (puede_editar()): ?>
        <div class="card card-custom mb-4">
            <div class="card-header card-header-custom">Cambiar Precio de Prenda</div>
            <div class="card-body">
                <form method="POST" class="row">
                    <div class="col-md-4">
                        <label>Prenda</label>
                        <select name="id_prenda" class="form-select">
                            <?php $ps = $conn->query("SELECT id_prenda, nombre, precio FROM prenda"); 
                                  while($p = $ps->fetch()) echo "<option value='{$p['id_prenda']}'>{$p['nombre']} (\${$p['precio']})</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Nuevo Precio</label>
                        <input type="number" step="0.01" name="nuevo_precio" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>Empleado</label>
                        <select name="id_empleado" class="form-select">
                            <?php $es = $conn->query("SELECT id_empleado, nombre FROM empleado"); 
                                  while($e = $es->fetch()) echo "<option value='{$e['id_empleado']}'>{$e['nombre']}</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button name="actualizar_precio" class="btn btn-success w-100">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
        <div class="card card-custom">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Fecha</th><th>Prenda</th><th>Precio Anterior</th><th>Precio Nuevo</th><th>Empleado</th></tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT a.*, p.nombre as p_nom, e.nombre as e_nom FROM actualizacion a 
                            JOIN prenda p ON a.id_prenda = p.id_prenda 
                            JOIN empleado e ON a.id_empleado = e.id_empleado ORDER BY fecha DESC";
                    $res = $conn->query($sql);
                    while($row = $res->fetch()){
                        echo "<tr>
                                <td>{$row['fecha']}</td>
                                <td>{$row['p_nom']}</td>
                                <td><s>\${$row['precio_anterior']}</s></td>
                                <td class='text-success fw-bold'>\${$row['precio_nuevo']}</td>
                                <td>{$row['e_nom']}</td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>