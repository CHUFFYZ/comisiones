<?php

return [
    'iconos' => [
        'MSC'  => 'fa-file-contract',
        'MP'   => 'fa-house',
        'MGSU' => 'fa-globe',
        'MA'   => 'fa-user-shield',
    ],
    'icono_default' => 'fa-cube',

    // Módulos que NO se listan como módulo (MGSU = acceso a todo, MP = panel principal)
    'ocultos' => ['MGSU', 'MP'],

    // Clave del módulo que da acceso total
    'modulo_global' => 'MGSU',

    'creables' => [
        'Administrador'       => ['SecretariaAcademica', 'Secretaria', 'Director', 'Profesor'],
        'SecretariaAcademica' => ['Secretaria'],
    ],
    'asignables' => [
        'Administrador'       => ['MSC', 'MA'],
        'SecretariaAcademica' => ['MSC'],
    ],

    // Opciones por módulo. 'accion' = permiso mínimo requerido
    // (Constancias ya no es opción: se gestiona dentro de cada comisión)
    'opciones' => [
        'MSC' => [
            ['nombre' => 'Ver comisiones',  'icono' => 'fa-list',        'ruta' => 'comisiones.index',      'accion' => 'leer'],
            ['nombre' => 'Nueva comisión',  'icono' => 'fa-plus',        'ruta' => 'comisiones.create',     'accion' => 'crear'],
            ['nombre' => 'Anulaciones',     'icono' => 'fa-ban',         'ruta' => 'comisiones.anuladas',   'accion' => 'eliminar'],
        ],
        'MA' => [
            ['nombre' => 'Usuarios',        'icono' => 'fa-users',       'ruta' => 'admin.usuarios.index',  'accion' => 'leer'],
            ['nombre' => 'Nuevo usuario',   'icono' => 'fa-user-plus',   'ruta' => 'admin.usuarios.create', 'accion' => 'crear'],
            ['nombre' => 'Permisos',        'icono' => 'fa-user-shield', 'ruta' => 'admin.permisos.index',  'accion' => 'actualizar'],
            ['nombre' => 'Bajas',           'icono' => 'fa-user-slash',  'ruta' => 'admin.usuarios.bajas',  'accion' => 'eliminar'],
        ],
    ],
];
