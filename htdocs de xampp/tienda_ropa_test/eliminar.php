<?php
include 'check_auth.php';
include 'permisos.php';

if (!puede_eliminar()) {
    echo "<script>alert('No tienes permiso para eliminar prendas.'); window.location='index.php';</script>";
    exit;
}

include 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $conn->query("SET @usuario_app = '".$_SESSION['usuario']."'");
        $stmt = $conn->prepare("DELETE FROM prenda WHERE id_prenda = ?");
        $stmt->execute([$id]);
    } catch (Exception $e) {
        // Si la prenda está siendo usada en las tablas registro o actualizacion
        echo "<script>alert('No se puede eliminar: Esta prenda tiene registros asociados en otras tablas.'); window.location='index.php';</script>";
        exit;
    }
}

header("Location: index.php");
?>