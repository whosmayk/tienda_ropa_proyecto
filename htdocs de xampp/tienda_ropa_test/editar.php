<?php 
include 'check_auth.php';
include 'db.php'; 

if (!isset($_GET['id'])) { header("Location: index.php"); exit; }

$id = $_GET['id'];

// 1. Obtener los datos actuales de la prenda antes de editar
$stmt = $conn->prepare("SELECT * FROM prenda WHERE id_prenda = ?");
$stmt->execute([$id]);
$prenda = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prenda) { header("Location: index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->query("SET @usuario_app = '".$_SESSION['usuario']."'");
    $nuevo_precio = $_POST['precio'];
    $precio_anterior = $prenda['precio'];
    $id_empleado = $_POST['id_empleado'];

    try {
        $conn->beginTransaction(); // Transacción

        // 2. Si el precio cambió, insertamos en la tabla actualizacion
        if ($nuevo_precio != $precio_anterior) {
            $sql_act = "INSERT INTO actualizacion (precio_anterior, precio_nuevo, id_prenda, id_empleado) 
                        VALUES (?, ?, ?, ?)";
            $stmt_act = $conn->prepare($sql_act);
            $stmt_act->execute([$precio_anterior, $nuevo_precio, $id, $id_empleado]);
        }

        // 3. Actualizamos los datos de la prenda
        $sql_upd = "UPDATE prenda SET nombre=?, precio=?, stock_actual=?, id_categoria=?, id_talla=?, id_color=? WHERE id_prenda=?";
        $conn->prepare($sql_upd)->execute([
            $_POST['nombre'], $nuevo_precio, $_POST['stock'], 
            $_POST['id_categoria'], $_POST['id_talla'], $_POST['id_color'], $id
        ]);

        $conn->commit(); // Guardamos todos los cambios
        header("Location: index.php");
    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Prenda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container main-container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-custom">
                    <div class="card-header card-header-custom">
                        <h4 class="mb-0">✏️ Modificar Prenda #<?= $id ?></h4>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nombre del Producto</label>
                                <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($prenda['nombre']) ?>" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Precio Actual ($)</label>
                                    <input type="number" step="0.01" name="precio" class="form-control" value="<?= $prenda['precio'] ?>" required>
                                    <small class="text-muted">Si cambias este valor, se generará un registro histórico.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Stock</label>
                                    <input type="number" name="stock" class="form-control" value="<?= $prenda['stock_actual'] ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-danger fw-bold">Empleado que realiza la modificación *</label>
                                <select name="id_empleado" class="form-select border-danger" required>
                                    <option value="">-- Seleccione su nombre --</option>
                                    <?php
                                    $res = $conn->query("SELECT id_empleado, nombre FROM empleado");
                                    while($row = $res->fetch()){ echo "<option value='{$row['id_empleado']}'>{$row['nombre']}</option>"; }
                                    ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Categoría</label>
                                    <select name="id_categoria" class="form-select">
                                        <?php
                                        $res = $conn->query("SELECT * FROM categoria");
                                        while($row = $res->fetch()){ 
                                            $selected = ($row['id_categoria'] == $prenda['id_categoria']) ? 'selected' : '';
                                            echo "<option value='{$row['id_categoria']}' $selected>{$row['nombre']}</option>"; 
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Talla</label>
                                    <select name="id_talla" class="form-select">
                                        <?php
                                        $res = $conn->query("SELECT * FROM talla");
                                        while($row = $res->fetch()){ 
                                            $selected = ($row['id_talla'] == $prenda['id_talla']) ? 'selected' : '';
                                            echo "<option value='{$row['id_talla']}' $selected>".strtoupper($row['talla'])."</option>"; 
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Color</label>
                                    <select name="id_color" class="form-select">
                                        <?php
                                        $res = $conn->query("SELECT * FROM color");
                                        while($row = $res->fetch()){ 
                                            $selected = ($row['id_color'] == $prenda['id_color']) ? 'selected' : '';
                                            echo "<option value='{$row['id_color']}' $selected>{$row['nombre']}</option>"; 
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-end">
                                <a href="index.php" class="btn btn-light me-2">Volver</a>
                                <button type="submit" class="btn btn-warning">Actualizar y Guardar Historial</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>