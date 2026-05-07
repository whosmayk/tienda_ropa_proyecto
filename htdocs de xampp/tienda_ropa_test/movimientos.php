<?php 
include 'check_auth.php';
include 'permisos.php';
include 'db.php'; 

// Usando TRANSACCIÓN + SP
if(isset($_POST['registrar_mov'])){
    try {
        // Establecer variable de usuario para el trigger
        $conn->query("SET @usuario_app = '".$_SESSION['usuario']."'");
        
        $conn->beginTransaction();
        
        // SP: Registrar movimiento (el trigger actualiza el stock automáticamente)
        $stmt = $conn->prepare("INSERT INTO movimiento_stock (tipo_movimiento, cantidad, id_prenda, id_empleado) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['tipo'], $_POST['cant'], $_POST['prenda'], $_POST['emp']]);
        
        $conn->commit();
        header("Location: movimientos.php?msg=registrado");
    } catch(Exception $e) {
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}

// Recalcular stock usando SP
if(isset($_POST['recalcular'])){
    $stmt = $conn->prepare("CALL sp_recalcular_stock_prenda(?)");
    $stmt->execute([$_POST['id_prenda']]);
    header("Location: movimientos.php?msg=recalculado");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Movimientos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container main-container">
        <h2 class="page-title">📊 Movimientos de Inventario</h2>
        <div class="card card-custom mb-4">
            <?php if (puede_registrar_movimiento()): ?>
            <div class="card-body">
            <form class="row" method="POST">
                <div class="col-md-3">
                    <label>Prenda</label>
                    <select name="prenda" class="form-select">
                        <?php $ps = $conn->query("SELECT id_prenda, nombre FROM prenda"); 
                              while($p = $ps->fetch()) echo "<option value='{$p['id_prenda']}'>{$p['nombre']}</option>"; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Tipo</label>
                    <select name="tipo" class="form-select">
                        <option value="entrada">Entrada</option>
                        <option value="salida">Salida</option>
                        <option value="ajuste">Ajuste</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Cantidad</label>
                    <input type="number" name="cant" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label>Empleado que autoriza</label>
                    <select name="emp" class="form-select">
                        <?php $es = $conn->query("SELECT id_empleado, nombre FROM empleado"); 
                              while($e = $es->fetch()) echo "<option value='{$e['id_empleado']}'>{$e['nombre']}</option>"; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button name="registrar_mov" class="btn btn-success w-100">Registrar</button>
                </div>
            </form>
            </div>
            <?php endif; ?>
        </div>

        <div class="card card-custom">
            <table class="table table-striped mb-0">
                <thead class="table-dark">
                    <tr><th>Fecha</th><th>Prenda</th><th>Tipo</th><th>Cant.</th><th>Empleado</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php
                    // Usando SP: sp_movimientos_por_prenda para todos
                    $res = $conn->query("SELECT m.*, p.nombre as p_nom, e.nombre as e_nom FROM movimiento_stock m 
                            JOIN prenda p ON m.id_prenda = p.id_prenda 
                            JOIN empleado e ON m.id_empleado = e.id_empleado ORDER BY fecha DESC");
                    while($row = $res->fetch()){
                        $clase = ($row['tipo_movimiento'] == 'entrada') ? 'text-success' : 'text-danger';
                        echo "<tr>
                                <td>{$row['fecha']}</td>
                                <td>{$row['p_nom']}</td>
                                <td class='fw-bold $clase'>".strtoupper($row['tipo_movimiento'])."</td>
                                <td>{$row['cantidad']}</td>
                                <td>{$row['e_nom']}</td>
                                <td>
                                    <form method='POST' style='display:inline'>
                                        <input type='hidden' name='id_prenda' value='{$row['id_prenda']}'>
                                        <button type='submit' name='recalcular' class='btn btn-sm btn-info' onclick=\"return confirm('Recalcular stock desde movimientos?')\">🔄</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
