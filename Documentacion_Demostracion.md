# Documento de Demostración - Tienda Ropa

## 1. Procedimientos Almacenados (SP)

### 1.1 sp_categoria_nueva
| | Descripción |
| --- | --- |
| **Qué hace** | Inserta una nueva categoría en la tabla `categoria` |
| **Página UI** | `categorias.php` |
| **Dónde en la página** | Formulario lateral "Nueva Categoría" > campo "Nombre de la Categoría" + "Descripción" > botón "➕ Guardar Categoría" |
| **Ubicación en código** | `categorias.php` líneas 8-11: `$stmt = $conn->prepare("CALL sp_categoria_nueva(?, ?)");` |
| **Verificación** | Ver en tabla `categoria` o en la tabla de categorías de la página |

---

### 1.2 sp_movimientos_por_prenda
| | Descripción |
| --- | --- |
| **Qué hace** | Lista los movimientos de inventario de una prenda específica |
| **Página UI** | `movimientos.php` |
| **Dónde en la página** | Tabla de Movimientos (se muestra automáticamente al cargar la página) |
| **Ubicación en código** | `movimientos.php` línea 68: Query que muestra todos los movimientos con JOIN |
| **Verificación** | Ver en tabla `movimiento_stock` |

---

### 1.3 actualizar_precio_prenda
| | Descripción |
| --- | --- |
| **Qué hace** | Actualiza el precio de una prenda Y registra en historial `actualizacion` |
| **Página UI** | `actualizaciones.php` |
| **Dónde en la página** | Formulario "Cambiar Precio de Prenda" > seleccionar Prenda > colocar Nuevo Precio > clic botón "Actualizar" |
| **Ubicación en código** | `actualizaciones.php` líneas 16-19: `$stmt = $conn->prepare("CALL actualizar_precio_prenda(?, ?, ?)");` |
| **Verificación** | Ver en tablas `prenda` (precio nuevo) y `actualizacion` (historial) |

---

### 1.4 sp_recalcular_stock_prenda
| | Descripción |
| --- | --- |
| **Qué hace** | Recalcula el stock de una prenda sumando todos sus movimientos |
| **Página UI** | `index.php` y `movimientos.php` |
| **Dónde en la página** | En `index.php`: botón 🔄 al lado de "Editar" en cada fila de la tabla |
| | En `movimientos.php`: botón 🔄 en la columna "Acciones" de cada movimiento |
| **Ubicación en código** | `index.php` líneas 6-8: `$stmt = $conn->prepare("CALL sp_recalcular_stock_prenda(?)");` |
| **Verificación** | Ver campo `stock_actual` en tabla `prenda` |

---

## 2. Transacciones

### 2.1 Transacción en Movimientos
| | Descripción |
| --- | --- |
| **Qué hace** | Asegura que el movimiento se registre completamente o no se registre nada |
| **Página UI** | `movimientos.php` |
| **Dónde en la página** | Al registrar cualquier movimiento (entrada/salida/ajuste) |
| **Ubicación en código** | `movimientos.php` líneas 10-24: |
| | ```php |
| | $conn->beginTransaction(); |
| | $stmt = $conn->prepare("INSERT INTO movimiento_stock..."); |
| | $stmt->execute([...]); |
| | $conn->commit(); |
| | ``` |
| **Verificación** | Ver en tabla `movimiento_stock` y verificar stock en `prenda` |

---

### 2.2 Transacción en Cambio de Precio
| | Descripción |
| --- | --- |
| **Qué hace** | Asegura que el precio se actualice Y se registre en historial |
| **Página UI** | `actualizaciones.php` |
| **Dónde en la página** | Al actualizar precio de una prenda |
| **Ubicación en código** | `actualizaciones.php` líneas 14-21: |
| | ```php |
| | $conn->beginTransaction(); |
| | $stmt = $conn->prepare("CALL actualizar_precio_prenda..."); |
| | $stmt->execute([...]); |
| | $conn->commit(); |
| | ``` |
| **Verificación** | Ver en tablas `prenda` y `actualizacion` |

---

## 3. Triggers (Automáticos)

### 3.1 trg_prenda_insert
| | Descripción |
| --- | --- |
| **Qué hace** | Registra automáticamente en bitácora cuando se inserta una nueva prenda |
| **Cuándo se ejecuta** | Al crear una prenda en `crear.php` |
| **Ubicación en código** | En la base de datos MySQL (no en PHP) |
| **Verificación** | Ver en tabla `bitacora_sistema` |

---

### 3.2 trg_actualizar_stock_insert
| | Descripción |
| --- | --- |
| **Qué hace** | Actualiza el stock automáticamente al insertar un movimiento |
| **Cuándo se ejecuta** | Al registrar cualquier movimiento en `movimientos.php` |
| **Ubicación en código** | En la base de datos MySQL |
| **Verificación** | Ver campo `stock_actual` en tabla `prenda` después de registrar movimiento |

---

### 3.3 trg_actualizar_stock_delete
| | Descripción |
| --- | --- |
| **Qué hace** | Revierte el stock cuando se elimina un movimiento |
| **Cuándo se ejecuta** | Al eliminar un registro de `movimiento_stock` |
| **Ubicación en código** | En la base de datos MySQL |
| **Verificación** | Ver cambio en `stock_actual` de `prenda` |

---

### 3.4 trg_bitacora_cambio_precio
| | Descripción |
| --- | --- |
| **Qué hace** | Registra en bitácora cuando cambia el precio de una prenda |
| **Cuándo se ejecuta** | Al actualizar precio en `actualizaciones.php` |
| **Ubicación en código** | En la base de datos MySQL |
| **Verificación** | Ver en tabla `bitacora_sistema` |

---

## 4. Vistas

### 4.1 vw_prendas_detalle
| | Descripción |
| --- | --- |
| **Qué muestra** | Prendas con su categoría, talla y color |
| **Dónde usar** | Query personalizada en `index.php` |
| **Código** | JOIN de `prenda` con `categoria`, `talla`, `color` |

---

### 4.2 vw_movimientos_stock
| | Descripción |
| --- | --- |
| **Qué muestra** | Movimientos con nombre de prenda y empleado |
| **Dónde usar** | Query en `movimientos.php` |
| **Código** | JOIN de `movimiento_stock` con `prenda` y `empleado` |

---

### 4.3 vw_historial_precios
| | Descripción |
| --- | --- |
| **Qué muestra** | Historial de precios con nombres de prenda y empleado |
| **Dónde usar** | Query en `actualizaciones.php` |
| **Código** | JOIN de `actualizacion` con `prenda` y `empleado` |

---

### 4.4 vw_registro_proveedores
| | Descripción |
| --- | --- |
| **Qué muestra** | Registros con nombres de prenda, empleado y proveedor |
| **Dónde usar** | Query en `registros.php` |
| **Código** | JOIN de `registro` con `prenda`, `empleado`, `proveedor` |

---

## 5. Bitácora

### 5.1 Página de Bitácora
| | Descripción |
| --- | --- |
| **Qué muestra** | Registros de todos los cambios en el sistema |
| **Página UI** | `bitacora.php` |
| **Dónde en la página** | Menú "Historial y Stock" > "Bitácora" (solo Administrador) |
| **Verificación** | Ver en tabla `bitacora_sistema` o directamente en la página |

---

## 6. Resumen Rápido

| Elemento | Página | Ubicación UI | Código (línea) |
| --- | --- | --- | --- |
| `sp_categoria_nueva` | categorias.php | Formulario Nueva Categoría (8-11) |
| `sp_movimientos_por_prenda` | movimientos.php | Tabla de movimientos (68) |
| `actualizar_precio_prenda` | actualizaciones.php | Formulario cambiar precio (16-19) |
| `sp_recalcular_stock_prenda` | index.php | Botón 🔄 en tabla (6-8) |
| TRANSACCIÓN movimientos | movimientos.php | Al registrar (10-24) |
| TRANSACCIÓN precio | actualizaciones.php | Al actualizar (14-21) |
| Trigger stock | automático | DB MySQL |
| Trigger bitácora | automático | DB MySQL |
| Eliminar categoría | categorias.php | Botón "Eliminar" en tabla |
| **_BITÁCORA** | **bitacora.php** | **Menú Historial y Stock** |

---

## 7. Cómo Demonstrar

1. **Categoría nueva** → Ir a Categorías > crear > verificar en tabla
2. **Movimiento stock** → Ir a Movimientos > registrar entrada > verificar stock en Inventario
3. **Cambio precio** → Ir a Historial Precios > cambiar precio > verificar en tabla actualizacion
4. **Recalcular stock** → Ir a Inventario > clic 🔄 > verificar stock
5. **Ver bitácora** → Ir a Historial y Stock > Bitácora (solo Administrador)

---

## 8. Notas Importantes

### Cómo actualizar los archivos en el servidor
Los archivos están en: **D:\ITA 2026\Taller de DB\P_Tienda_Ropa\CRUD Tienda Ropa\tienda_ropa_test\**

Para que funcionen en el servidor local, copia todos los archivos a:
**C:\xampp\htdocs\tienda_ropa_test\**

### Verificar que los SP y Triggers estén creados
En phpMyAdmin > tienda_ropa_test > Procedimientos o Triggers

### Registro de Usuario en Bitácora
Para que el usuario que se loggea sea quien se registre en la bitácora:

1. **SP actualizados**: Ahora reciben el usuario como parámetro
2. **Triggers actualizados**: Usan `@usuario_app` establecida desde PHP
3. **PHP establece**: `$conn->query("SET @usuario_app = '".$_SESSION['usuario']."'");`

**Archivos que establecen @usuario_app:**
- `crear.php` - Al crear nueva prenda
- `editar.php` - Al editar/prenda
- `eliminar.php` - Al eliminar prenda
- `actualizaciones.php` - Al actualizar precio
- `movimientos.php` - Al registrar movimiento
- `tallas.php` - Al gestionar tallas (INSERT/UPDATE/DELETE)
- `colores.php` - Al gestionar colores (INSERT/UPDATE/DELETE)
- `categorias.php` - Al gestionar categorías (INSERT/UPDATE/DELETE)
- `proveedores.php` - Al gestionar proveedores (INSERT/UPDATE/DELETE)
- `empleados.php` - Al gestionar empleados (INSERT/UPDATE/DELETE)
- `registros.php` - Al registrar mercancía