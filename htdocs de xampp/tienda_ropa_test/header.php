<?php
$usuario_logueado = isset($_SESSION['usuario']);
$es_admin = $usuario_logueado && $_SESSION['rol'] === 'Administrador';
$es_empleado = $usuario_logueado && $_SESSION['rol'] === 'Empleado';
$es_consulta = $usuario_logueado && $_SESSION['rol'] === 'Consulta';
$PuedeGestionar = $es_admin || $es_empleado;
?>
<link href="styles.css" rel="stylesheet">
<style>
    body { margin-bottom: 30px; }
    .table thead { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
</style>
<nav class="navbar navbar-expand-lg navbar-dark mb-4 navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="<?= $usuario_logueado ? 'index.php' : 'login.php' ?>">👕 TiendaRopa</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if ($usuario_logueado): ?>
                    <li class="nav-item"><a class="nav-link" href="index.php">Prendas</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Catálogos de gestión</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="categorias.php">Categorías</a></li>
                            <li><a class="dropdown-item" href="colores.php">Colores</a></li>
                            <li><a class="dropdown-item" href="tallas.php">Tallas</a></li>
                            <?php if ($es_admin): ?>
                            <li><a class="dropdown-item" href="empleados.php">Empleados</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="proveedores.php">Proveedores</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Historial y Stock</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="movimientos.php">Movimientos de Stock</a></li>
                            <li><a class="dropdown-item" href="actualizaciones.php">Cambios de Precios</a></li>
                            <li><a class="dropdown-item" href="registros.php">Registros de Entrada</a></li>
                            <?php if ($es_admin): ?>
                            <li><a class="dropdown-item" href="bitacora.php">Bitácora</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
            <?php if ($usuario_logueado): ?>
                <div class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <?= $_SESSION['icon'] ?> <?= htmlspecialchars($_SESSION['usuario']) ?>
                            <span class="badge badge-role ms-1"><?= $_SESSION['rol'] ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text text-muted small">
                                <strong>Rol:</strong> <?= $_SESSION['rol'] ?>
                            </span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-light btn-sm">Iniciar Sesión</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>