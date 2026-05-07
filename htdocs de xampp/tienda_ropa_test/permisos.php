<?php
function puede_crear() {
    return in_array($_SESSION['rol'], ['Administrador', 'Empleado']);
}

function puede_editar() {
    return in_array($_SESSION['rol'], ['Administrador', 'Empleado']);
}

function puede_eliminar() {
    return $_SESSION['rol'] === 'Administrador';
}

function puede_gestionar_empleados() {
    return $_SESSION['rol'] === 'Administrador';
}

function puede_gestionar_catalogo() {
    return in_array($_SESSION['rol'], ['Administrador', 'Empleado']);
}

function puede_registrar_movimiento() {
    return in_array($_SESSION['rol'], ['Administrador', 'Empleado']);
}

function puede_registrar_entrada() {
    return in_array($_SESSION['rol'], ['Administrador', 'Empleado']);
}

function es_solo_consulta() {
    return $_SESSION['rol'] === 'Consulta';
}