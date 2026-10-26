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

    // Opciones por módulo. 'accion' = permiso mínimo requerido,
    // 'excluye' = roles que NO ven la opción, 'params' = parámetros de la ruta
    'opciones' => [
        'MSC' => [
            ['nombre' => 'Ver comisiones',  'icono' => 'fa-list',            'ruta' => 'comisiones.index',      'accion' => 'leer'],
            ['nombre' => 'Nueva comisión',  'icono' => 'fa-plus',            'ruta' => 'comisiones.create',     'accion' => 'crear'],
            ['nombre' => 'Profesores',      'icono' => 'fa-chalkboard-user', 'ruta' => 'directorio.profesores', 'accion' => 'leer'],
            ['nombre' => 'Anulaciones',     'icono' => 'fa-ban',             'ruta' => 'comisiones.anuladas',   'accion' => 'eliminar'],
            ['nombre' => 'Reportes',        'icono' => 'fa-print',           'ruta' => 'reportes.index',        'accion' => 'leer', 'params' => ['modulo' => 'MSC']],
        ],
        'MA' => [
            ['nombre' => 'Usuarios',            'icono' => 'fa-users',       'ruta' => 'admin.usuarios.index',   'accion' => 'leer'],
            ['nombre' => 'Nuevo usuario',       'icono' => 'fa-user-plus',   'ruta' => 'admin.usuarios.create',  'accion' => 'crear'],
            ['nombre' => 'Permisos',            'icono' => 'fa-user-shield', 'ruta' => 'admin.permisos.index',   'accion' => 'actualizar'],
            ['nombre' => 'Plantillas de oficio','icono' => 'fa-file-lines',  'ruta' => 'admin.plantillas.index', 'accion' => 'actualizar'],
            ['nombre' => 'Bajas',               'icono' => 'fa-user-slash',  'ruta' => 'admin.usuarios.bajas',   'accion' => 'eliminar'],
            ['nombre' => 'Reportes',            'icono' => 'fa-print',       'ruta' => 'reportes.index',         'accion' => 'leer', 'params' => ['modulo' => 'MA']],
        ],
    ],
];
