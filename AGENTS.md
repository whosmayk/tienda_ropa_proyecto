# AGENTS.md - Tienda Ropa Proyecto

## Tech Stack
- **PHP** (plain PHP, no framework)
- **MySQL/MariaDB** via XAMPP
- **Bootstrap 5** for UI
- **PDO** for database connections

## Project Structure
```
htdocs de xampp/tienda_ropa_test/   # Main application code
phpMyAdmin/                        # Database schema + stored procedures
.agents/skills/                   # OpenCode skills (accessibility, frontend-design, seo)
```

## Running the App
1. Start XAMPP (Apache + MySQL)
2. Import `phpMyAdmin/tienda_ropa_test.sql` into phpMyAdmin
3. Copy `htdocs de xampp/tienda_ropa_test/*` to `C:\xampp\htdocs\tienda_ropa_test\`
4. Access at `http://localhost/tienda_ropa_test/`

## Database Credentials
- Host: `localhost`
- DB: `tienda_ropa_test`
- User: `root`
- Password: `` (empty)

## Key Architecture
- `db.php` - PDO connection (charset: utf8)
- `check_auth.php` - Session authentication
- `permisos.php` - Role-based permissions (Administrador, etc.)
- `header.php` - Navigation menu based on permissions

## Important Conventions
- Every writable page sets `@usuario_app` session variable before DB operations: `$conn->query("SET @usuario_app = '".$_SESSION['usuario']."'");`
- Uses stored procedures (`CALL sp_*`) for inventory operations
- Triggers handle automatic stock updates and audit logging
- Sort/search parameters in URL preserve state across requests

## Reference Files
- `Documentacion_Demostracion.md` - Full demo walkthrough with SP/trigger locations
- `.agents/skills/` - Contains accessibility, frontend-design, and SEO skills for OpenCode