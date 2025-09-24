<?php

// Configuración del proyecto
return [
    // Ruta base del proyecto (para MAMP: /martincarmona)
    'base_path' => '/martincarmona',
    
    // Configuración de la base de datos (para más adelante)
    'database' => [
        'host' => 'localhost',
        'port' => '8889', // Puerto por defecto de MAMP
        'name' => 'martincarmona',
        'user' => 'root',
        'password' => 'root'
    ],
    
    // Configuración de la aplicación
    'app' => [
        'name' => 'Sistema de Gestión de Tareas',
        'debug' => true
    ]
];
