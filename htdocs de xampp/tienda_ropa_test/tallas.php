<?php 
include 'check_auth.php';
include 'permisos.php';
include 'db.php'; 

// 1. Lógica para INSERTAR (El ID se asigna solo por ser AUTO_INCREMENT)
if(isset($_POST['add'])){
    $conn->query("SET @usuario_app = '".$_SESSION['usuario']."'");
    $stmt = $conn->prepare("INSERT INTO talla (talla) VALUES (?)");
    $stmt->execute([$_POST['talla']]);  // Fixed extra bracket
    header("Location: tallas.php?msg=creado");
    exit;
}

// 2. Lógica para ACTUALIZAR
if(isset($_POST['update'])){
    $conn->query("SET @usuario_app = '".$_SESSION['usuario']."'");
    $stmt = $conn->prepare("UPDATE talla SET talla = ? WHERE id_talla = ?");
    $stmt->execute([$_POST['talla'], $_POST['id_talla']]);
    header("Location: tallas.php?msg=actualizado");
    exit;
}

// 3. Lógica para ELIMINAR (cambiado a POST)
if(isset($_POST['delete'])){
    try {
        $conn->query("SET @usuario_app = '".$_SESSION['usuario']."'");
        $stmt = $conn->prepare("DELETE FROM talla WHERE id_talla = ?");
        $stmt->execute([$_POST['delete_id']]);
        header("Location: tallas.php?msg=eliminado");
        exit;
    } catch (Exception $e) {
        $error = "No se puede eliminar esta talla porque tiene prendas asociadas.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tallas - Tienda Ropa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container main-container">
        <h2 class="page-title">📏 Tallas</h2>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show mt-3">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Formulario Lateral -->
            <?php if (puede_gestionar_catalogo()): ?>
            <div class="col-md-4 mb-4">
                <div class="card card-custom">
                    <div class="card-header card-header-custom">Nueva Talla</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small">Nombre de la Talla</label>
                                <input type="text" name="talla" class="form-control" placeholder="Ej. ech, eg" required>
                                <div class="form-text text-muted">El ID se asignará automáticamente.</div>
                            </div>
                            <button name="add" class="btn btn-primary w-100">➕ Guardar Talla</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Tabla de Tallas -->
            <div class="col-md-<?= puede_gestionar_catalogo() ? '8' : '12' ?> mb-4">
                <div class="card card-custom">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre de la Talla</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res = $conn->query("SELECT * FROM talla ORDER BY id_talla ASC");
                            while($row = $res->fetch()){
                                $id = htmlspecialchars($row['id_talla']);
                                $talla = htmlspecialchars($row['talla']);
                                echo "<tr>
                                        <td>{$id}</td>
                                        <td><strong>{$talla}</strong></td>
                                        <td class='text-center'>";
if (puede_gestionar_catalogo()) {
    echo "<div class='btn-group'>
        <button class='btn btn-sm btn-warning' 
            onclick='abrirEditar({$row['id_talla']}, \"".addslashes($row['talla'])."\")'>
            Editar
        </button>
        <form method='POST' style='display:inline' onsubmit='return confirm(\"¿Estás seguro de eliminar esta talla?\")'>
            <input type='hidden' name='delete_id' value='{$id}'>
            <button type='submit' name='delete' class='btn btn-sm btn-danger'>
                Eliminar
            </button>
        </form>
    </div>";
}
echo "                                      </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PARA EDITAR -->
    <div class="modal fade" id="modalEditarTalla" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modificar Talla</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_talla" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Nombre de la Talla</label>
                            <input type="text" name="talla" id="edit_talla" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" name="update" class="btn btn-primary">Actualizar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function abrirEditar(id, talla) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_talla').value = talla;
        
        var myModal = new bootstrap.Modal(document.getElementById('modalEditarTalla'));
        myModal.show();
    }
    </script>
</body>
</html>
