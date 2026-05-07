-- ============================================================================
-- ROLES, USUARIOS Y PRIVILEGIOS - VISTAS
-- ============================================================================
-- NOTA: Los roles y usuarios MySQL tienen problemas de corrupciÃ³n en este servidor.
-- Las vistas funcionan correctamente.
-- Los usuarios y roles de la aplicaciÃ³n se manejan internamente con $_SESSION
-- ============================================================================

USE tienda_ropa_test;


-- ============================================================================
-- USUARIOS MySQL
-- ============================================================================

-- CREATE USER 'admin_tienda'@'localhost' IDENTIFIED BY 'Admin123!';
-- CREATE USER 'empleado_tienda'@'localhost' IDENTIFIED BY 'Empleado123!';
-- CREATE USER 'consulta_tienda'@'localhost' IDENTIFIED BY 'Consulta123!';
-- CREATE ROLE 'rol_admin';
-- CREATE ROLE 'rol_empleado';
-- CREATE ROLE 'rol_consulta';
-- GRANT ALL PRIVILEGES ON tienda_ropa_test.* TO 'rol_admin';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON tienda_ropa_test.prenda TO 'rol_empleado';
-- (mÃ¡s privilegios...)
-- GRANT 'rol_admin' TO 'admin_tienda'@'localhost';