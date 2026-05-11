<?php 
include 'check_auth.php';
include 'permisos.php';
include 'db.php'; 

// Recalcular stock usando SP
if(isset($_POST['recalcular_stock'])){
    $stmt = $conn->prepare("CALL sp_recalcular_stock_prenda(?)");
    $stmt->execute([$_POST['id_prenda']]);
    header("Location: index.php?msg=recalculado");
}

// 1. Configuración de Ordenamiento
$columnas_permitidas = [
    'id' => 'p.id_prenda',
    'nombre' => 'p.nombre',
    'precio' => 'p.precio',
    'stock' => 'p.stock_actual',
    'categoria' => 'c.nombre',
    'talla' => 't.talla',
    'color' => 'col.nombre'
];

$sort = isset($_GET['sort']) && array_key_exists($_GET['sort'], $columnas_permitidas) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'DESC' : 'ASC';

// 2. Configuración de Búsqueda
$search = isset($_GET['search']) ? $_GET['search'] : '';

// 3. Función para generar links que mantengan tanto el orden como la búsqueda
function getHeaderLink($col_key, $label, $current_sort, $current_order, $current_search) {
    $next_order = ($current_sort === $col_key && $current_order === 'ASC') ? 'desc' : 'asc';
    $icon = ($current_sort === $col_key) ? ($current_order === 'ASC' ? ' ▲' : ' ▼') : '';
    
    // Construimos la URL manteniendo el parámetro de búsqueda si existe
    $url = "?sort={$col_key}&order={$next_order}";
    if (!empty($current_search)) {
        $url .= "&search=" . urlencode($current_search);
    }
    
    return "<a href='{$url}' class='text-white text-decoration-none d-block fw-bold'>{$label}{$icon}</a>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario - Tienda de Ropa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container main-container">
        <div class="row mb-4">
            <div class="col-md-4">
                <h2 class="page-title">📦 Inventario</h2>
            </div>
            <!-- Formulario de Búsqueda -->
            <div class="col-md-5">
                <form method="GET" class="d-flex">
                    <input type="hidden" name="sort" value="<?= $sort ?>">
                    <input type="hidden" name="order" value="<?= $order ?>">
                    
                    <input type="text" name="search" class="form-control me-2" 
                           placeholder="Buscar por nombre, color, talla..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <?php if(!empty($search)): ?>
                        <a href="index.php" class="btn btn-outline-light ms-1">Limpiar</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-3 text-end">
                <?php if (puede_crear()): ?>
                <a href="crear.php" class="btn btn-success">➕ Nueva Prenda</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card card-custom">
            <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th><?= getHeaderLink('id', 'ID', $sort, $order, $search) ?></th>
                        <th><?= getHeaderLink('nombre', 'Nombre', $sort, $order, $search) ?></th>
                        <th><?= getHeaderLink('precio', 'Precio', $sort, $order, $search) ?></th>
                        <th><?= getHeaderLink('stock', 'Stock', $sort, $order, $search) ?></th>
                        <th><?= getHeaderLink('categoria', 'Categoría', $sort, $order, $search) ?></th>
                        <th><?= getHeaderLink('talla', 'Talla', $sort, $order, $search) ?></th>
                        <th><?= getHeaderLink('color', 'Color', $sort, $order, $search) ?></th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Construcción de la consulta con filtros
                    $columna_sql = $columnas_permitidas[$sort];
                    
                    $sql = "SELECT p.*, c.nombre as cat_nom, t.talla as talla_nom, col.nombre as color_nom 
                            FROM prenda p
                            JOIN categoria c ON p.id_categoria = c.id_categoria
                            JOIN talla t ON p.id_talla = t.id_talla
                            JOIN color col ON p.id_color = col.id_color";

                    // Condiciones
                    if (!empty($search)) {
                        $sql .= " WHERE p.nombre LIKE :busqueda 
                                  OR c.nombre LIKE :busqueda 
                                  OR col.nombre LIKE :busqueda 
                                  OR t.talla LIKE :busqueda 
                                  OR p.precio LIKE :busqueda 
                                  OR p.id_prenda = :busqueda_exacta";
                    }

                    $sql .= " ORDER BY {$columna_sql} {$order}";

                    $stmt = $conn->prepare($sql);
                    
                    if (!empty($search)) {
                        $term = "%$search%";
                        $stmt->bindParam(':busqueda', $term);
                        $stmt->bindParam(':busqueda_exacta', $search);
                    }
                    
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $id = htmlspecialchars($row['id_prenda']);
                            $nombre = htmlspecialchars($row['nombre']);
                            $precio = htmlspecialchars($row['precio']);
                            $stock = htmlspecialchars($row['stock_actual']);
                            $cat = htmlspecialchars($row['cat_nom']);
                            $talla = htmlspecialchars($row['talla_nom']);
                            $color = htmlspecialchars($row['color_nom']);
                            echo "<tr>
                                    <td>{$id}</td>
                                    <td><strong>{$nombre}</strong></td>
                                    <td>\${$precio}</td>
                                    <td>{$stock}</td>
                                    <td>{$cat}</td>
                                    <td class='text-uppercase'>{$talla}</td>
                                    <td>{$color}</td>
                                    <td class='text-center'>";
if (puede_editar()) {
    echo "<a href='editar.php?id={$id}' class='btn btn-sm btn-outline-warning'>Editar</a> ";
    echo "<form method='POST' style='display:inline'>
            <input type='hidden' name='id_prenda' value='{$id}'>
            <button type='submit' name='recalcular_stock' class='btn btn-sm btn-outline-info' onclick=\"return confirm('Recalcular stock?')\">🔄</button>
          </form>";
}
if (puede_eliminar()) {
    echo "<a href='eliminar.php?id={$id}' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"¿Eliminar?\")'>Borrar</a>";
}
echo "   </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center py-4 text-muted'>No se encontraron prendas que coincidan con la búsqueda.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</body>
</html>