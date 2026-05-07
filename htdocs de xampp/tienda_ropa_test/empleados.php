<?php 
include 'check_auth.php';
include 'permisos.php';

if (!puede_gestionar_empleados()) {
    echo "<script>alert('No tienes permiso para gestionar empleados.'); window.location='index.php';</script>";
    exit;
}

include 'db.php'; 

// 1. Lógica para INSERTAR (Nuevo Empleado)
if(isset($_POST['add'])){
    $conn->query("SET @usuario_app = '".$_SESSION['usuario']."'");
    $stmt = $conn->prepare("INSERT INTO empleado (nombre, puesto) VALUES (?, ?)");
    $stmt->execute([$_POST['nombre'], $_POST['puesto']]);
    header("Location: empleados.php?msg=creado");
}

// 2. Lógica para ACTUALIZAR (Modificar Empleado existente)
if(isset($_POST['update'])){
    $conn->query("SET @usuario_app = '".$_SESSION['usuario']."'");
    $stmt = $conn->prepare("UPDATE empleado SET nombre = ?, puesto = ? WHERE id_empleado = ?");
    $stmt->execute([$_POST['nombre'], $_POST['puesto'], $_POST['id_empleado']]);
    header("Location: empleados.php?msg=actualizado");
}

// 3. Lógica para ELIMINAR
if(isset($_GET['delete'])){
    try {
        $conn->query("SET @usuario_app = '".$_SESSION['usuario']."'");
        $stmt = $conn->prepare("DELETE FROM empleado WHERE id_empleado = ?");
        $stmt->execute([$_GET['delete']]);
        header("Location: empleados.php?msg=eliminado");
    } catch (Exception $e) {
        // Error común: El empleado tiene registros asociados en la tabla 'registro'
        $error = "No se puede eliminar este empleado porque tiene registros de mercancía asociados.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Empleados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container main-container">
        <h2 class="page-title">👥 Empleados</h2>
        <div class="row">
            <!-- Formulario Lateral para registro rápido -->
            <div class="col-md-4 mb-4">
                <div class="card card-custom">
                    <div class="card-header card-header-custom">Registrar Nuevo Empleado</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small">Nombre Completo</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Puesto</label>
                                <select name="puesto" class="form-select">
                                    <option value="empleado">Empleado</option>
                                    <option value="gerente">Gerente</option>
                                </select>
                            </div>
                            <button name="add" class="btn btn-primary w-100">➕ Registrar</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabla de Empleados -->
            <div class="col-md-8 mb-4">
                <div class="card card-custom">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Puesto</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res = $conn->query("SELECT * FROM empleado");
                            while($row = $res->fetch()){
                                echo "<tr>
                                        <td>{$row['id_empleado']}</td>
                                        <td>{$row['nombre']}</td>
                                        <td><span class='badge " . ($row['puesto'] == 'gerente' ? 'bg-danger' : 'bg-info') . "'>{$row['puesto']}</span></td>
                                        <td class='text-center'>
                                            <div class='btn-group'>
                                                <button class='btn btn-sm btn-warning' 
                                                    onclick='abrirEditar({$row['id_empleado']}, \"{$row['nombre']}\", \"{$row['puesto']}\")'>
                                                    Editar
                                                </button>
                                                <a href='empleados.php?delete={$row['id_empleado']}' 
                                                   class='btn btn-sm btn-danger' 
                                                   onclick='return confirm(\"¿Estás seguro de eliminar este empleado?\")'>
                                                    Eliminar
                                                </a>
                                            </div>
                                        </td>
                                      </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PARA EDITAR EMPLEADO -->
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Actualizar Empleado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_empleado" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Empleado</label>
                            <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cambiar Puesto</label>
                            <select name="puesto" id="edit_puesto" class="form-select">
                                <option value="empleado">Empleado</option>
                                <option value="gerente">Gerente</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" name="update" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script para llenar el modal con los datos actuales -->
    <script>
    function abrirEditar(id, nombre, puesto) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_puesto').value = puesto;
        
        var myModal = new bootstrap.Modal(document.getElementById('modalEditar'));
        myModal.show();
    }
    </script>

</body>
</html>