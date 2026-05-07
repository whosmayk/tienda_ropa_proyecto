<?php 
include 'check_auth.php';
include 'permisos.php';
include 'db.php'; 

// 1. Lógica para INSERTAR
if(isset($_POST['add'])){
    try {
        $conn->query("SET @usuario_app = '".$_SESSION['usuario']."'");
        $stmt = $conn->prepare("INSERT INTO proveedor (nombre, telefono, direccion) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['nombre'], $_POST['telefono'], $_POST['direccion']]);
        header("Location: proveedores.php?msg=creado");
    } catch (Exception $e) {
        $error = "Error al crear: " . $e->getMessage();
    }
}

// 2. Lógica para ACTUALIZAR
if(isset($_POST['update'])){
    try {
        $conn->query("SET @usuario_app = '".$_SESSION['usuario']."'");
        $stmt = $conn->prepare("UPDATE proveedor SET nombre = ?, telefono = ?, direccion = ? WHERE id_proveedor = ?");
        $stmt->execute([$_POST['nombre'], $_POST['telefono'], $_POST['direccion'], $_POST['id_proveedor']]);
        header("Location: proveedores.php?msg=actualizado");
    } catch (Exception $e) {
        $error = "Error al actualizar: " . $e->getMessage();
    }
}

// 3. Lógica para ELIMINAR
if(isset($_GET['delete'])){
    try {
        $conn->query("SET @usuario_app = '".$_SESSION['usuario']."'");
        $stmt = $conn->prepare("DELETE FROM proveedor WHERE id_proveedor = ?");
        $stmt->execute([$_GET['delete']]);
        header("Location: proveedores.php?msg=eliminado");
    } catch (Exception $e) {
        // Error común: El proveedor tiene registros asociados en la tabla 'registro'
        $error = "No se puede eliminar este proveedor porque tiene registros de mercancía asociados.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proveedores - Tienda Ropa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container main-container">
        <h2 class="page-title">🚚 Proveedores</h2>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Formulario Lateral -->
            <?php if (puede_gestionar_catalogo()): ?>
            <div class="col-md-4 mb-4">
                <div class="card card-custom">
                    <div class="card-header card-header-custom">Nuevo Proveedor</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small">Nombre / Empresa</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Teléfono (10 dígitos)</label>
                                <input type="text" name="telefono" class="form-control" maxlength="10">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Dirección</label>
                                <textarea name="direccion" class="form-control" rows="2"></textarea>
                            </div>
                            <button name="add" class="btn btn-primary w-100">➕ Guardar Proveedor</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Tabla de Proveedores -->
            <div class="col-md-<?= puede_gestionar_catalogo() ? '8' : '12' ?> mb-4">
                <div class="card card-custom">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Proveedor</th>
                                <th>Contacto</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res = $conn->query("SELECT * FROM proveedor");
                            while($row = $res->fetch()){
                                $nom_js = addslashes($row['nombre']);
                                $dir_js = addslashes($row['direccion']);
echo "<tr>
                                        <td>{$row['id_proveedor']}</td>
                                        <td>
                                            <strong>{$row['nombre']}</strong><br>
                                            <small class='text-muted'>{$row['direccion']}</small>
                                        </td>
                                        <td>{$row['telefono']}</td>
                                        <td class='text-center'>";
if (puede_gestionar_catalogo()) {
    echo "<div class='btn-group'>
        <button class='btn btn-sm btn-warning' 
            onclick='abrirEditar({$row['id_proveedor']}, \"{$nom_js}\", \"{$row['telefono']}\", \"{$dir_js}\")'>
            Editar
        </button>
        <a href='proveedores.php?delete={$row['id_proveedor']}' 
           class='btn btn-sm btn-danger' 
           onclick='return confirm(\"¿Estás seguro de eliminar este proveedor?\")'>
            Eliminar
        </a>
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
    <div class="modal fade" id="modalEditarProv" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modificar Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_proveedor" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Nombre / Empresa</label>
                            <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" id="edit_telefono" class="form-control" maxlength="10">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <textarea name="direccion" id="edit_direccion" class="form-control" rows="3"></textarea>
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
    function abrirEditar(id, nombre, telefono, direccion) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_telefono').value = telefono;
        document.getElementById('edit_direccion').value = direccion;
        
        var myModal = new bootstrap.Modal(document.getElementById('modalEditarProv'));
        myModal.show();
    }
    </script>
</body>
</html>