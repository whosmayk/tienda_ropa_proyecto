<?php 
include 'check_auth.php';
include 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Establecer variable de usuario para el trigger
    $conn->query("SET @usuario_app = '".$_SESSION['usuario']."'");
    
    $sql = "INSERT INTO prenda (nombre, precio, stock_actual, id_categoria, id_talla, id_color) 
            VALUES (:nombre, :precio, :stock, :cat, :talla, :color)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':nombre' => $_POST['nombre'],
        ':precio' => $_POST['precio'],
        ':stock'  => $_POST['stock'],
        ':cat'    => $_POST['id_categoria'],
        ':talla'  => $_POST['id_talla'],
        ':color'  => $_POST['id_color']
    ]);
    header("Location: index.php");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Prenda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container main-container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-custom">
                    <div class="card-header card-header-custom">
                        <h4 class="mb-0">➕ Registrar Nueva Prenda</h4>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nombre del Producto</label>
                                <input type="text" name="nombre" class="form-control" placeholder="Ej. Camisa Oxford" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Precio ($)</label>
                                    <input type="number" step="0.01" name="precio" class="form-control" placeholder="Ej. 500.00" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Stock Inicial</label>
                                    <input type="number" name="stock" class="form-control" value="0" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Categoría</label>
                                    <select name="id_categoria" class="form-select">
                                        <?php
                                        $res = $conn->query("SELECT * FROM categoria");
                                        while($row = $res->fetch()){ echo "<option value='{$row['id_categoria']}'>{$row['nombre']}</option>"; }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Talla</label>
                                    <select name="id_talla" class="form-select">
                                        <?php
                                        $res = $conn->query("SELECT * FROM talla");
                                        while($row = $res->fetch()){ echo "<option value='{$row['id_talla']}'>".strtoupper($row['talla'])."</option>"; }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Color</label>
                                    <select name="id_color" class="form-select">
                                        <?php
                                        $res = $conn->query("SELECT * FROM color");
                                        while($row = $res->fetch()){ echo "<option value='{$row['id_color']}'>{$row['nombre']}</option>"; }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-end">
                                <a href="index.php" class="btn btn-light me-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar Producto</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>