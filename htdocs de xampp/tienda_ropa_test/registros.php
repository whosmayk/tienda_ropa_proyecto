<?php 
include 'check_auth.php';
include 'permisos.php';
include 'db.php'; 

// Lógica para insertar un nuevo registro de recepción
if(isset($_POST['registrar_recepcion'])){
    try {
        $conn->query("SET @usuario_app = '".$_SESSION['usuario']."'");
        $stmt = $conn->prepare("INSERT INTO registro (id_prenda, id_empleado, id_proveedor) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['id_prenda'], $_POST['id_empleado'], $_POST['id_proveedor']]);
        header("Location: registros.php?msj=ok");
    } catch (Exception $e) {
        $error = "Error al registrar: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registros de Entrada - Tienda Ropa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container main-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="page-title">📥 Recepciones de Mercancía</h2>
            <?php if (puede_registrar_entrada()): ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistro">
                ➕ Nueva Recepción
            </button>
            <?php endif; ?>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="card card-custom">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Prenda Recibida</th>
                        <th>Empleado Receptor</th>
                        <th>Proveedor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT r.fecha_registro, p.nombre as prenda, e.nombre as empleado, prov.nombre as proveedor
                            FROM registro r
                            JOIN prenda p ON r.id_prenda = p.id_prenda
                            JOIN empleado e ON r.id_empleado = e.id_empleado
                            JOIN proveedor prov ON r.id_proveedor = prov.id_proveedor
                            ORDER BY r.fecha_registro DESC";
                    
                    $res = $conn->query($sql);
                    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>
                                <td>" . date('d/m/Y H:i', strtotime($row['fecha_registro'])) . "</td>
                                <td><strong>{$row['prenda']}</strong></td>
                                <td>{$row['empleado']}</td>
                                <td><span class='badge bg-secondary'>{$row['proveedor']}</span></td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para Nuevo Registro -->
    <div class="modal fade" id="modalRegistro" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Entrada de Mercancía</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Seleccionar Prenda</label>
                            <select name="id_prenda" class="form-select" required>
                                <?php
                                $items = $conn->query("SELECT id_prenda, nombre FROM prenda");
                                while($i = $items->fetch()) echo "<option value='{$i['id_prenda']}'>{$i['nombre']}</option>";
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Empleado que recibe</label>
                            <select name="id_empleado" class="form-select" required>
                                <?php
                                $emps = $conn->query("SELECT id_empleado, nombre FROM empleado");
                                while($e = $emps->fetch()) echo "<option value='{$e['id_empleado']}'>{$e['nombre']}</option>";
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Proveedor</label>
                            <select name="id_proveedor" class="form-select" required>
                                <?php
                                $provs = $conn->query("SELECT id_proveedor, nombre FROM proveedor");
                                while($pr = $provs->fetch()) {
                                    // CORRECCIÓN AQUÍ: Antes decía $row, debe decir $pr
                                    echo "<option value='{$pr['id_proveedor']}'>{$pr['nombre']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" name="registrar_recepcion" class="btn btn-primary">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>