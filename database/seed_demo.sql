-- =============================================================================
-- SEED DEMO: Cuenta demo para presentación a inversores
-- Usuario: demo@martincarmona.com / password: Demo2024!
-- Rol: empresa
-- Datos ficticios pero realistas — olivar en Jaén, ~2 años de actividad
-- =============================================================================
-- INSTRUCCIONES:
--   1. Ejecutar este script contra la base de datos (local o producción)
--   2. Login: demo@martincarmona.com / Demo2024!
--   3. Para limpiar: DELETE FROM usuarios WHERE email = 'demo@martincarmona.com'
--      (el resto se borra en cascada por id_user)
-- =============================================================================

SET @NOW = NOW();

-- =============================================================================
-- 1. USUARIO DEMO
-- =============================================================================
-- Password: Demo2024! (hash generado con PASSWORD_DEFAULT)
INSERT INTO usuarios (name, email, password, rol, created_at, updated_at)
VALUES ('Finca Olivares Demo', 'demo@martincarmona.com',
        '$2y$10$OMTuo1Ziv7AG1jhYGt3vZeDaNsIXNavjAYXAgFQiwiBA9c4TbjeZq',
        'empresa', '2024-01-15 10:00:00', @NOW);

SET @DEMO_USER = LAST_INSERT_ID();

-- =============================================================================
-- 2. PROPIETARIOS (dueños de parcelas)
-- =============================================================================
INSERT INTO propietarios (nombre, apellidos, dni, telefono, email, id_user) VALUES
('Antonio',   'García López',      '28456789A', '953 22 14 50', 'antonio.garcia@email.es',    @DEMO_USER),
('María José','Martínez Ruiz',     '28567890B', '953 33 25 61', 'mjose.martinez@email.es',    @DEMO_USER),
('Francisco', 'Hernández Moreno',  '28678901C', '953 44 36 72', 'fhernandez@email.es',        @DEMO_USER),
('Carmen',    'López Jiménez',     '28789012D', '953 55 47 83', 'carmen.lopez@email.es',       @DEMO_USER);

SET @PROP1 = LAST_INSERT_ID();
SET @PROP2 = @PROP1 + 1;
SET @PROP3 = @PROP1 + 2;
SET @PROP4 = @PROP1 + 3;

-- =============================================================================
-- 3. PARCELAS (8 fincas de olivar en Jaén)
-- =============================================================================
INSERT INTO parcelas (nombre, olivos, ubicacion, propietario, propietario_id, hidrante, descripcion, referencia_catastral, tipo_olivos, año_plantacion, tipo_plantacion, riego_secano, corta, id_user) VALUES
('Los Cerros',         480, 'Ctra. Jaén-Córdoba km 12',     'Antonio',   @PROP1, 15, 'Parcela principal, buen rendimiento',       '23001A001000010000AB', 'Picual',    2005, 'tradicional',     'riego',  'siempre', @DEMO_USER),
('La Vega Alta',       320, 'Camino de la Vega s/n',        'Antonio',   @PROP1, 16, 'Zona llana con buen acceso',                '23001A001000020000CD', 'Picual',    2010, 'intensivo',       'riego',  'par',     @DEMO_USER),
('El Cerrejón',        260, 'Paraje El Cerrejón',           'María José',@PROP2, 22, 'Terreno en pendiente, difícil acceso',      '23001A002000030000EF', 'Hojiblanca',1998, 'tradicional',     'secano', 'impar',   @DEMO_USER),
('Cortijo La Esperanza',550,'Ctra. Mancha Real km 5',       'María José',@PROP2, 23, 'La finca más grande, olivos centenarios',   '23001A002000040000GH', 'Picual',    1985, 'tradicional',     'riego',  'siempre', @DEMO_USER),
('Las Navas',          180, 'Camino Las Navas',             'Francisco', @PROP3, 30, 'Plantación joven superintensiva',           '23001A003000050000IJ', 'Arbequina', 2020, 'superintensivo',  'riego',  'siempre', @DEMO_USER),
('El Almendral',       410, 'Paraje El Almendral',          'Francisco', @PROP3, 31, 'Mixto olivos y almendros en la linde',      '23001A003000060000KL', 'Picual',    2002, 'tradicional',     'riego',  'par',     @DEMO_USER),
('Cerro del Águila',   290, 'Cerro del Águila, Pegalajar',  'Carmen',    @PROP4, 40, 'Parcela en sierra, aceituna de calidad',    '23001A004000070000MN', 'Picual',    1995, 'tradicional',     'secano', 'impar',   @DEMO_USER),
('La Cañada',          200, 'Vega de La Cañada',            'Carmen',    @PROP4, 41, 'Zona de vega, suelo fértil',                '23001A004000080000OP', 'Hojiblanca',2015, 'intensivo',       'riego',  'siempre', @DEMO_USER);

SET @PAR1 = LAST_INSERT_ID();
SET @PAR2 = @PAR1 + 1;
SET @PAR3 = @PAR1 + 2;
SET @PAR4 = @PAR1 + 3;
SET @PAR5 = @PAR1 + 4;
SET @PAR6 = @PAR1 + 5;
SET @PAR7 = @PAR1 + 6;
SET @PAR8 = @PAR1 + 7;

-- =============================================================================
-- 4. TRABAJADORES (6 empleados)
-- =============================================================================
INSERT INTO trabajadores (nombre, apellidos, dni, ss, alta_ss, telefono, email, direccion, especialidad, fecha_contratacion, estado, cuadrilla, id_user) VALUES
('Manuel',    'Ruiz Ortega',       '28111222A', '28/12345678/01', '2023-11-01', '654 111 222', 'manuel.ruiz@email.es',    'C/ Olivo 12, Jaén',         'Tractorista',           '2023-11-01', 'activo',   0, @DEMO_USER),
('José Luis', 'Fernández Gómez',   '28222333B', '28/23456789/02', '2024-01-15', '654 222 333', 'joseluis.fdez@email.es',  'Avda. Andalucía 45, Jaén',  'Podador',               '2024-01-15', 'activo',   0, @DEMO_USER),
('Rafael',    'Moreno Castillo',   '28333444C', '28/34567890/03', '2024-02-01', '654 333 444', 'rafael.moreno@email.es',  'C/ San Juan 8, Mancha Real','Peón agrícola',         '2024-02-01', 'activo',   0, @DEMO_USER),
('Ana María', 'Jiménez Torres',    '28444555D', '28/45678901/04', '2024-03-01', '654 444 555', 'anamaria.jt@email.es',    'C/ Nueva 23, Pegalajar',    'Tratamientos',          '2024-03-01', 'activo',   0, @DEMO_USER),
('Pedro',     'López Navarro',     '28555666E', '28/56789012/05', '2024-06-01', '654 555 666', 'pedro.lopez@email.es',    'C/ Real 5, Jaén',           'Peón agrícola',         '2024-06-01', 'activo',   0, @DEMO_USER),
('Cuadrilla Hermanos Ruiz', NULL,  NULL,         NULL,             NULL,         '953 66 77 88', NULL,                     NULL,                         'Recolección aceituna',  '2024-10-01', 'activo',   1, @DEMO_USER);

SET @TRAB1 = LAST_INSERT_ID();
SET @TRAB2 = @TRAB1 + 1;
SET @TRAB3 = @TRAB1 + 2;
SET @TRAB4 = @TRAB1 + 3;
SET @TRAB5 = @TRAB1 + 4;
SET @TRAB6 = @TRAB1 + 5;

-- =============================================================================
-- 5. TRABAJOS (tipos de trabajo — replicados de producción)
-- =============================================================================
INSERT INTO trabajos (nombre, descripcion, precio_hora, categoria, id_user) VALUES
-- 🔷 Riego
('Abrir Riego',                       'Abrir hidrante para iniciar riego',           9.23, 'riego',        @DEMO_USER),
('Cerrar Riego',                      'Cerrar hidrante tras riego',                  9.23, 'riego',        @DEMO_USER),
('Echar a andar riego',               'Poner en marcha sistema de riego',            0,    'riego',        @DEMO_USER),
('Estirar gomas',                     'Extender mangueras de riego',                 0,    'riego',        @DEMO_USER),
('Quitar vareta',                     'Retirar varetas de riego',                    0,    'riego',        @DEMO_USER),
-- 🔵 Tratamiento
('Echar sulfato con atomizadora',     'Tratamiento con sulfato de cobre',            0,    'tratamiento',  @DEMO_USER),
('Echar herbicida con tractor',       'Aplicar herbicida con tractor',               0,    'tratamiento',  @DEMO_USER),
('Echar herbicida con mochila',       'Aplicar herbicida con mochila pulverizadora', 0,    'tratamiento',  @DEMO_USER),
('Echar hoja con tractor',            'Tratamiento foliar con tractor',              0,    'tratamiento',  @DEMO_USER),
('Echar abono con riego',             'Fertirrigación',                              0,    'tratamiento',  @DEMO_USER),
('Echar abono con abonadora',         'Aplicar abono con maquinaria',                0,    'tratamiento',  @DEMO_USER),
('Echar abono jarrillos',             'Aplicar abono manual con jarrillos',          0,    'tratamiento',  @DEMO_USER),
('Echar estiercol',                   'Abonar con estiércol',                        0,    'tratamiento',  @DEMO_USER),
-- 🟢 Campo
('Desbrozar con tractor',             'Desbroce mecanizado',                         0,    'campo',        @DEMO_USER),
('Pasar grada pinches',               'Laboreo con grada de púas',                   0,    'campo',        @DEMO_USER),
('Pasar Rulo tractor',                'Pasar rulo con tractor',                      0,    'campo',        @DEMO_USER),
('Pasar Rastra Tractor',              'Laboreo con rastra',                          0,    'campo',        @DEMO_USER),
('Pasar Grada discos tractor',        'Laboreo con grada de discos',                 0,    'campo',        @DEMO_USER),
-- 🟠 Recolección
('Recoger aceituna',                  'Recolección de aceituna',                     0,    'recoleccion',  @DEMO_USER),
('Recoger o acordonar desnate',       'Recoger restos de poda',                      0,    'recoleccion',  @DEMO_USER),
('Cargar palos',                      'Carga de leña y ramas',                       0,    'recoleccion',  @DEMO_USER),
('Llevar remolque palos',             'Transporte de leña con remolque',             0,    'recoleccion',  @DEMO_USER),
-- 🟢 Campo
('Arreglar Plantones',                'Mantenimiento de plantones jóvenes',          9.23, 'campo',        @DEMO_USER),
-- 🟡 Mantenimiento
('Arreglo tractor',                   'Reparación de tractor',                       0,    'mantenimiento',@DEMO_USER),
('Arreglar Máquinas',                 'Reparación de maquinaria agrícola',           0,    'mantenimiento',@DEMO_USER),
('Mantenimiento vehiculos',           'Mantenimiento general de vehículos',          0,    'mantenimiento',@DEMO_USER),
-- 🔵 Tratamiento
('Preparar mochilas',                 'Preparar mochilas pulverizadoras',            0,    'tratamiento',  @DEMO_USER),
-- 🟡 Mantenimiento
('Limpiar cuadra',                    'Limpieza de instalaciones',                   0,    'mantenimiento',@DEMO_USER),
-- 🔷 Riego
('Regar plantones',                   'Riego manual de plantones',                   0,    'riego',        @DEMO_USER),
-- 🟢 Campo
('Plantar plantones',                 'Plantación de olivos nuevos',                 0,    'campo',        @DEMO_USER),
-- 🟣 Poda
('Corta plantones',                   'Poda de plantones',                           0,    'poda',         @DEMO_USER),
-- 🟠 Recolección
('Recoger raigones',                  'Recoger fragmentos de raíz',                  0,    'recoleccion',  @DEMO_USER),
-- ⚪ Otro
('Papeleo y burocracia',              'Gestión administrativa',                      0,    'otro',         @DEMO_USER),
('Formación',                         'Formación y capacitación',                    0,    'otro',         @DEMO_USER),
('Compras',                           'Compra de materiales y suministros',          0,    'otro',         @DEMO_USER),
-- 🟢 Campo
('Quitar hierba con desbrozadora',    'Desbroce manual con desbrozadora',            0,    'campo',        @DEMO_USER),
-- 🟣 Poda
('Cortar desnate',                    'Cortar restos de poda',                       0,    'poda',         @DEMO_USER),
('Picar desnate',                     'Triturar desnate',                            0,    'poda',         @DEMO_USER),
('Picar Ramón',                       'Triturar ramas gruesas',                      0,    'poda',         @DEMO_USER),
('Acordonar Ramón',                   'Amontonar ramas para recogida',               0,    'poda',         @DEMO_USER),
('Escamujar',                         'Poda de aclareo',                             0,    'poda',         @DEMO_USER),
-- 🟠 Recolección
('Soplar suelo sopladora',            'Limpiar suelo con sopladora',                 9.23, 'recoleccion',  @DEMO_USER),
('Soplar suelo tractor',              'Limpiar suelo con soplador de tractor',       0,    'recoleccion',  @DEMO_USER),
-- 🔵 Tratamiento
('Preparar cuba',                     'Preparar depósito para tratamiento',          0,    'tratamiento',  @DEMO_USER),
-- 🟢 Campo
('Abrir zanja retro',                 'Apertura de zanjas con retroexcavadora',      0,    'campo',        @DEMO_USER),
('Hacer suelos con mano hierro',      'Laboreo manual del suelo',                    0,    'campo',        @DEMO_USER),
-- 🟡 Mantenimiento
('Pasar itv maquinaria',              'Inspección técnica de maquinaria',            0,    'mantenimiento',@DEMO_USER);

SET @TRAB_ABRIR_RIEGO       = LAST_INSERT_ID();
SET @TRAB_CERRAR_RIEGO      = @TRAB_ABRIR_RIEGO + 1;
SET @TRAB_SULFATO            = @TRAB_ABRIR_RIEGO + 5;
SET @TRAB_HERB_TRACTOR       = @TRAB_ABRIR_RIEGO + 6;
SET @TRAB_HERB_MOCHILA       = @TRAB_ABRIR_RIEGO + 7;
SET @TRAB_HOJA_TRACTOR       = @TRAB_ABRIR_RIEGO + 8;
SET @TRAB_ABONO_RIEGO        = @TRAB_ABRIR_RIEGO + 9;
SET @TRAB_ABONO_ABONADORA    = @TRAB_ABRIR_RIEGO + 10;
SET @TRAB_DESBROZAR          = @TRAB_ABRIR_RIEGO + 13;
SET @TRAB_GRADA_PINCHES      = @TRAB_ABRIR_RIEGO + 14;
SET @TRAB_GRADA_DISCOS       = @TRAB_ABRIR_RIEGO + 17;
SET @TRAB_RECOGER_ACEITUNA   = @TRAB_ABRIR_RIEGO + 18;
SET @TRAB_ACORDONAR_DESNATE  = @TRAB_ABRIR_RIEGO + 19;
SET @TRAB_ARREGLAR_PLANTONES = @TRAB_ABRIR_RIEGO + 22;
SET @TRAB_MANT_VEHICULOS     = @TRAB_ABRIR_RIEGO + 25;
SET @TRAB_PLANTAR            = @TRAB_ABRIR_RIEGO + 29;
SET @TRAB_ESCAMUJAR          = @TRAB_ABRIR_RIEGO + 40;
SET @TRAB_SOPLAR_TRACTOR     = @TRAB_ABRIR_RIEGO + 42;
SET @TRAB_PICAR_RAMON        = @TRAB_ABRIR_RIEGO + 38;
SET @TRAB_PAPELEO            = @TRAB_ABRIR_RIEGO + 32;

-- =============================================================================
-- 6. PROVEEDORES
-- =============================================================================
INSERT INTO proveedores (nombre, telefono, ubicacion, descripcion, razon_social, cif, direccion, email, contacto_principal, sector, estado, id_user) VALUES
('Agrícola San Fernando',   '953 25 10 10', 'Jaén',          'Suministros agrícolas y fitosanitarios',         'Agrícola San Fernando S.L.',    'B23456789', 'Pol. Ind. Los Olivares 14, Jaén',   'info@agricolasanfernando.es',  'Fernando Ruiz',    'Fitosanitarios',   'activo', @DEMO_USER),
('Riegos del Sur',          '953 26 20 20', 'Jaén',          'Instalación y mantenimiento de riego por goteo', 'Riegos del Sur S.A.',           'A23567890', 'C/ Agua 3, Jaén',                   'comercial@riegosdelsur.es',    'Laura Pérez',      'Riego',            'activo', @DEMO_USER),
('Talleres Hermanos Ortega','953 27 30 30', 'Mancha Real',   'Reparación de maquinaria agrícola',              'Hnos. Ortega Maquinaria S.L.', 'B23678901', 'Ctra. Mancha Real km 2',            'taller@hnosortega.es',         'Miguel Ortega',    'Maquinaria',       'activo', @DEMO_USER),
('Cooperativa San Isidro',  '953 28 40 40', 'Jaén',          'Cooperativa olivarera, entrega de aceituna',     'Coop. San Isidro de Jaén',     'F23789012', 'Camino de la Almazara s/n',         'socios@sanisidro.coop',        'Isabel Martín',    'Almazara',         'activo', @DEMO_USER),
('Combustibles Linares',    '953 29 50 50', 'Linares',       'Gasóleo agrícola y gasolina',                    'Combustibles Linares S.L.',    'B23890123', 'Pol. Ind. Linares Norte 7',         'pedidos@comblinares.es',       'Andrés López',     'Combustible',      'activo', @DEMO_USER),
('Viveros La Loma',         '953 30 60 60', 'Úbeda',         'Plantones de olivo certificados',                'Viveros La Loma S.L.',         'B23901234', 'Ctra. Úbeda-Baeza km 4',            'ventas@viverosdelaloma.es',    'Rocío Herrera',    'Viveros',          'activo', @DEMO_USER);

SET @PROV1 = LAST_INSERT_ID();
SET @PROV2 = @PROV1 + 1;
SET @PROV3 = @PROV1 + 2;
SET @PROV4 = @PROV1 + 3;
SET @PROV5 = @PROV1 + 4;
SET @PROV6 = @PROV1 + 5;

-- =============================================================================
-- 7. VEHÍCULOS
-- =============================================================================
INSERT INTO vehiculos (nombre, matricula, precio_seguro, fecha_matriculacion, seguro, pasa_itv, id_user) VALUES
('Tractor John Deere 5075E',    'J-4521-BL',  420.00, '2018-03-15', 'Mapfre Agropecuaria', '2026-06-15', @DEMO_USER),
('Tractor Kubota M5091',        'J-8834-CM',  480.00, '2021-06-01', 'Mapfre Agropecuaria', '2026-09-20', @DEMO_USER),
('Pick-up Toyota Hilux',        '2987-KLM',   350.00, '2019-09-10', 'AXA Seguros',         '2026-04-10', @DEMO_USER),
('Motocarro Piaggio Ape 50',    '1456-HNR',   180.00, '2016-05-20', 'Línea Directa',       '2026-11-30', @DEMO_USER),
('Sopladora Stihl BR 800 C-E',  NULL,         NULL,   NULL,          NULL,                  NULL,          @DEMO_USER),
('Cuba tratamientos Benza 200L', NULL,         NULL,   NULL,          NULL,                  NULL,          @DEMO_USER);

SET @VEH1 = LAST_INSERT_ID();
SET @VEH2 = @VEH1 + 1;
SET @VEH3 = @VEH1 + 2;

-- =============================================================================
-- 8. HERRAMIENTAS
-- =============================================================================
INSERT INTO herramientas (nombre, cantidad, fecha_compra, descripcion, precio, id_user) VALUES
('Motosierra Stihl MS 261',               2, '2023-06-15', 'Para poda gruesa de olivo',                       450.00,  @DEMO_USER),
('Desbrozadora Husqvarna 535RXT',          3, '2023-09-01', 'Desbroce de hierba entre calles',                  380.00,  @DEMO_USER),
('Mochila pulverizadora Matabi Super 16',  4, '2024-02-10', 'Tratamientos fitosanitarios manuales',              85.00,  @DEMO_USER),
('Tijeras de poda Felco 2',               8, '2024-01-20', 'Poda manual de olivo',                              45.00,  @DEMO_USER),
('Vibrador de olivos Campagnola Alice',    1, '2022-11-01', 'Recolección mecanizada de aceituna',              2800.00,  @DEMO_USER),
('Remolque agrícola Palazoglu 5T',         1, '2020-08-15', 'Transporte de material y aceituna',               3200.00,  @DEMO_USER),
('Atomizador Hardi Arrow 1000',            1, '2023-03-10', 'Tratamientos fitosanitarios con tractor',         4500.00,  @DEMO_USER),
('Abonadora centrífuga Vicon RS-M',        1, '2024-04-05', 'Aplicación de abono sólido en parcelas',           650.00,  @DEMO_USER);

-- =============================================================================
-- 9. FITOSANITARIOS — INVENTARIO
-- =============================================================================
INSERT INTO fitosanitarios_inventario (producto, fecha_compra, cantidad, unidad, proveedor_id, id_user) VALUES
('Cobre Nordox 75 WG',           '2024-03-10', 25.00,  'kg',     @PROV1, @DEMO_USER),
('Glifosato 36%',                '2024-04-01', 20.00,  'litros', @PROV1, @DEMO_USER),
('Dimetoato 40%',                '2024-05-15', 10.00,  'litros', @PROV1, @DEMO_USER),
('Abono foliar NPK 20-20-20',   '2024-03-20', 50.00,  'kg',     @PROV1, @DEMO_USER),
('Oxicloruro de cobre 50%',      '2025-01-10', 30.00,  'kg',     @PROV1, @DEMO_USER),
('Imidacloprid 20 SL',           '2025-03-05', 5.00,   'litros', @PROV1, @DEMO_USER),
('Abono orgánico Fertiolivo',    '2025-02-15', 500.00, 'kg',     @PROV1, @DEMO_USER),
('Azufre mojable 80%',           '2025-04-01', 15.00,  'kg',     @PROV1, @DEMO_USER),
('Kaolin (Surround WP)',         '2025-06-10', 20.00,  'kg',     @PROV1, @DEMO_USER),
('Spinosad (GF-120)',            '2025-07-01', 8.00,   'litros', @PROV1, @DEMO_USER);

SET @FIT1 = LAST_INSERT_ID();

-- =============================================================================
-- 10. FITOSANITARIOS — APLICACIONES (2 años de historial)
-- =============================================================================
INSERT INTO fitosanitarios_aplicaciones (parcela_id, producto, fecha, cantidad, id_user) VALUES
-- 2024 Primavera: Cobre post-poda
(@PAR1, 'Cobre Nordox 75 WG',         '2024-02-20', 3.00, @DEMO_USER),
(@PAR2, 'Cobre Nordox 75 WG',         '2024-02-22', 2.50, @DEMO_USER),
(@PAR4, 'Cobre Nordox 75 WG',         '2024-02-25', 4.00, @DEMO_USER),
(@PAR6, 'Cobre Nordox 75 WG',         '2024-03-01', 3.00, @DEMO_USER),
-- 2024 Primavera: Herbicida
(@PAR1, 'Glifosato 36%',              '2024-03-15', 3.00, @DEMO_USER),
(@PAR2, 'Glifosato 36%',              '2024-03-18', 2.00, @DEMO_USER),
(@PAR5, 'Glifosato 36%',              '2024-03-20', 1.50, @DEMO_USER),
(@PAR8, 'Glifosato 36%',              '2024-03-22', 2.00, @DEMO_USER),
-- 2024 Primavera: Abono foliar
(@PAR1, 'Abono foliar NPK 20-20-20', '2024-04-10', 5.00, @DEMO_USER),
(@PAR4, 'Abono foliar NPK 20-20-20', '2024-04-12', 6.00, @DEMO_USER),
(@PAR5, 'Abono foliar NPK 20-20-20', '2024-04-15', 3.00, @DEMO_USER),
-- 2024 Verano: Mosca del olivo
(@PAR1, 'Dimetoato 40%',              '2024-07-01', 2.00, @DEMO_USER),
(@PAR4, 'Dimetoato 40%',              '2024-07-03', 2.50, @DEMO_USER),
(@PAR7, 'Dimetoato 40%',              '2024-07-05', 1.50, @DEMO_USER),
-- 2024 Otoño: Cobre pre-recolección
(@PAR1, 'Cobre Nordox 75 WG',         '2024-09-15', 3.00, @DEMO_USER),
(@PAR3, 'Cobre Nordox 75 WG',         '2024-09-18', 2.00, @DEMO_USER),
(@PAR4, 'Cobre Nordox 75 WG',         '2024-09-20', 4.00, @DEMO_USER),
-- 2025 Invierno: Cobre post-recolección
(@PAR1, 'Oxicloruro de cobre 50%',    '2025-01-20', 4.00, @DEMO_USER),
(@PAR2, 'Oxicloruro de cobre 50%',    '2025-01-22', 3.00, @DEMO_USER),
(@PAR4, 'Oxicloruro de cobre 50%',    '2025-01-25', 5.00, @DEMO_USER),
(@PAR6, 'Oxicloruro de cobre 50%',    '2025-01-28', 3.50, @DEMO_USER),
-- 2025 Primavera: Abono orgánico
(@PAR1, 'Abono orgánico Fertiolivo',  '2025-02-20', 80.00, @DEMO_USER),
(@PAR4, 'Abono orgánico Fertiolivo',  '2025-02-25', 100.00,@DEMO_USER),
(@PAR5, 'Abono orgánico Fertiolivo',  '2025-03-01', 50.00, @DEMO_USER),
-- 2025 Primavera: Azufre contra repilo
(@PAR3, 'Azufre mojable 80%',         '2025-04-10', 3.00, @DEMO_USER),
(@PAR7, 'Azufre mojable 80%',         '2025-04-12', 3.00, @DEMO_USER),
-- 2025 Verano: Kaolín contra mosca
(@PAR1, 'Kaolin (Surround WP)',       '2025-06-20', 4.00, @DEMO_USER),
(@PAR4, 'Kaolin (Surround WP)',       '2025-06-22', 5.00, @DEMO_USER),
(@PAR6, 'Kaolin (Surround WP)',       '2025-06-25', 4.00, @DEMO_USER),
-- 2025 Verano: Spinosad trampeo
(@PAR1, 'Spinosad (GF-120)',          '2025-07-10', 1.50, @DEMO_USER),
(@PAR4, 'Spinosad (GF-120)',          '2025-07-12', 2.00, @DEMO_USER),
(@PAR7, 'Spinosad (GF-120)',          '2025-07-15', 1.50, @DEMO_USER),
-- 2025 Otoño: Cobre pre-campaña
(@PAR1, 'Oxicloruro de cobre 50%',    '2025-09-20', 3.50, @DEMO_USER),
(@PAR4, 'Oxicloruro de cobre 50%',    '2025-09-22', 4.50, @DEMO_USER),
(@PAR3, 'Oxicloruro de cobre 50%',    '2025-09-25', 2.50, @DEMO_USER),
-- 2026 Invierno: Cobre post-campaña
(@PAR1, 'Oxicloruro de cobre 50%',    '2026-01-15', 3.50, @DEMO_USER),
(@PAR2, 'Oxicloruro de cobre 50%',    '2026-01-18', 3.00, @DEMO_USER),
(@PAR4, 'Oxicloruro de cobre 50%',    '2026-01-20', 5.00, @DEMO_USER);

-- =============================================================================
-- 11. RIEGOS (2 años de registros — temporada abr-oct)
-- =============================================================================
INSERT INTO riegos (parcela_id, hidrante, fecha_ini, fecha_fin, cantidad_ini, cantidad_fin, dias, total_m3, id_user) VALUES
-- 2024 Temporada de riego
(@PAR1, '15', '2024-04-01', '2024-04-08', 10200, 10450, 7,  250,  @DEMO_USER),
(@PAR2, '16', '2024-04-01', '2024-04-08', 5100,  5310,  7,  210,  @DEMO_USER),
(@PAR4, '23', '2024-04-10', '2024-04-18', 8000,  8380,  8,  380,  @DEMO_USER),
(@PAR5, '30', '2024-04-10', '2024-04-16', 3200,  3380,  6,  180,  @DEMO_USER),
(@PAR1, '15', '2024-05-01', '2024-05-10', 10450, 10750, 9,  300,  @DEMO_USER),
(@PAR2, '16', '2024-05-01', '2024-05-09', 5310,  5550,  8,  240,  @DEMO_USER),
(@PAR4, '23', '2024-05-05', '2024-05-14', 8380,  8800,  9,  420,  @DEMO_USER),
(@PAR6, '31', '2024-05-10', '2024-05-18', 4000,  4300,  8,  300,  @DEMO_USER),
(@PAR8, '41', '2024-05-10', '2024-05-17', 2000,  2180,  7,  180,  @DEMO_USER),
(@PAR1, '15', '2024-06-01', '2024-06-12', 10750, 11150, 11, 400,  @DEMO_USER),
(@PAR2, '16', '2024-06-01', '2024-06-11', 5550,  5870,  10, 320,  @DEMO_USER),
(@PAR4, '23', '2024-06-05', '2024-06-16', 8800,  9320,  11, 520,  @DEMO_USER),
(@PAR5, '30', '2024-06-05', '2024-06-14', 3380,  3630,  9,  250,  @DEMO_USER),
(@PAR1, '15', '2024-07-01', '2024-07-14', 11150, 11650, 13, 500,  @DEMO_USER),
(@PAR4, '23', '2024-07-01', '2024-07-15', 9320,  9920,  14, 600,  @DEMO_USER),
(@PAR6, '31', '2024-07-05', '2024-07-16', 4300,  4680,  11, 380,  @DEMO_USER),
(@PAR8, '41', '2024-07-05', '2024-07-15', 2180,  2420,  10, 240,  @DEMO_USER),
(@PAR1, '15', '2024-08-01', '2024-08-15', 11650, 12200, 14, 550,  @DEMO_USER),
(@PAR2, '16', '2024-08-01', '2024-08-14', 5870,  6280,  13, 410,  @DEMO_USER),
(@PAR4, '23', '2024-08-01', '2024-08-16', 9920,  10600, 15, 680,  @DEMO_USER),
(@PAR5, '30', '2024-08-05', '2024-08-16', 3630,  3910,  11, 280,  @DEMO_USER),
(@PAR1, '15', '2024-09-01', '2024-09-12', 12200, 12580, 11, 380,  @DEMO_USER),
(@PAR4, '23', '2024-09-01', '2024-09-14', 10600, 11100, 13, 500,  @DEMO_USER),
(@PAR6, '31', '2024-09-05', '2024-09-15', 4680,  4980,  10, 300,  @DEMO_USER),
-- 2025 Temporada de riego
(@PAR1, '15', '2025-04-05', '2025-04-13', 12580, 12830, 8,  250,  @DEMO_USER),
(@PAR2, '16', '2025-04-05', '2025-04-12', 6280,  6500,  7,  220,  @DEMO_USER),
(@PAR4, '23', '2025-04-10', '2025-04-19', 11100, 11480, 9,  380,  @DEMO_USER),
(@PAR5, '30', '2025-04-10', '2025-04-17', 3910,  4100,  7,  190,  @DEMO_USER),
(@PAR1, '15', '2025-05-01', '2025-05-11', 12830, 13150, 10, 320,  @DEMO_USER),
(@PAR2, '16', '2025-05-01', '2025-05-10', 6500,  6760,  9,  260,  @DEMO_USER),
(@PAR4, '23', '2025-05-05', '2025-05-15', 11480, 11930, 10, 450,  @DEMO_USER),
(@PAR6, '31', '2025-05-10', '2025-05-19', 4980,  5300,  9,  320,  @DEMO_USER),
(@PAR8, '41', '2025-05-10', '2025-05-18', 2420,  2620,  8,  200,  @DEMO_USER),
(@PAR1, '15', '2025-06-01', '2025-06-13', 13150, 13600, 12, 450,  @DEMO_USER),
(@PAR4, '23', '2025-06-01', '2025-06-14', 11930, 12510, 13, 580,  @DEMO_USER),
(@PAR5, '30', '2025-06-05', '2025-06-15', 4100,  4370,  10, 270,  @DEMO_USER),
(@PAR1, '15', '2025-07-01', '2025-07-15', 13600, 14150, 14, 550,  @DEMO_USER),
(@PAR2, '16', '2025-07-01', '2025-07-13', 6760,  7170,  12, 410,  @DEMO_USER),
(@PAR4, '23', '2025-07-01', '2025-07-16', 12510, 13200, 15, 690,  @DEMO_USER),
(@PAR6, '31', '2025-07-05', '2025-07-17', 5300,  5700,  12, 400,  @DEMO_USER),
(@PAR8, '41', '2025-07-05', '2025-07-16', 2620,  2890,  11, 270,  @DEMO_USER),
(@PAR1, '15', '2025-08-01', '2025-08-16', 14150, 14750, 15, 600,  @DEMO_USER),
(@PAR4, '23', '2025-08-01', '2025-08-17', 13200, 13950, 16, 750,  @DEMO_USER),
(@PAR5, '30', '2025-08-05', '2025-08-17', 4370,  4680,  12, 310,  @DEMO_USER),
(@PAR1, '15', '2025-09-01', '2025-09-13', 14750, 15150, 12, 400,  @DEMO_USER),
(@PAR4, '23', '2025-09-01', '2025-09-15', 13950, 14500, 14, 550,  @DEMO_USER),
(@PAR6, '31', '2025-09-05', '2025-09-16', 5700,  6030,  11, 330,  @DEMO_USER);

-- =============================================================================
-- 12. CAMPAÑAS DE ACEITUNA (2 campañas completas + 1 actual)
-- =============================================================================
INSERT INTO campanas (nombre, fecha_inicio, fecha_fin, activa, precio_venta, id_user) VALUES
('23/24', '2023-11-15', '2024-02-28', 0, 5.20, @DEMO_USER),
('24/25', '2024-11-10', '2025-02-20', 0, 4.85, @DEMO_USER),
('25/26', '2025-11-12', NULL,         1, NULL,  @DEMO_USER);

SET @CAMP1 = LAST_INSERT_ID();
SET @CAMP2 = @CAMP1 + 1;
SET @CAMP3 = @CAMP1 + 2;

-- Registros campaña 23/24
INSERT INTO campana_registros (campana_id, parcela_id, fecha, kilos, rendimiento_pct, id_user) VALUES
(@CAMP1, @PAR1, '2023-11-20', 4800, 21.5, @DEMO_USER),
(@CAMP1, @PAR1, '2023-12-05', 3200, 22.0, @DEMO_USER),
(@CAMP1, @PAR2, '2023-11-25', 3100, 20.8, @DEMO_USER),
(@CAMP1, @PAR3, '2023-12-10', 2400, 19.5, @DEMO_USER),
(@CAMP1, @PAR4, '2023-12-01', 6200, 22.3, @DEMO_USER),
(@CAMP1, @PAR4, '2023-12-15', 4100, 21.8, @DEMO_USER),
(@CAMP1, @PAR6, '2024-01-10', 3800, 20.5, @DEMO_USER),
(@CAMP1, @PAR7, '2024-01-15', 2600, 21.0, @DEMO_USER),
(@CAMP1, @PAR8, '2024-01-20', 1800, 20.0, @DEMO_USER);

-- Registros campaña 24/25
INSERT INTO campana_registros (campana_id, parcela_id, fecha, kilos, rendimiento_pct, id_user) VALUES
(@CAMP2, @PAR1, '2024-11-15', 5200, 22.0, @DEMO_USER),
(@CAMP2, @PAR1, '2024-12-02', 3600, 22.5, @DEMO_USER),
(@CAMP2, @PAR2, '2024-11-20', 3400, 21.2, @DEMO_USER),
(@CAMP2, @PAR4, '2024-11-25', 7100, 22.8, @DEMO_USER),
(@CAMP2, @PAR4, '2024-12-10', 4500, 22.0, @DEMO_USER),
(@CAMP2, @PAR5, '2024-12-05', 2800, 23.5, @DEMO_USER),
(@CAMP2, @PAR6, '2025-01-05', 4200, 21.0, @DEMO_USER),
(@CAMP2, @PAR7, '2025-01-12', 2900, 21.5, @DEMO_USER),
(@CAMP2, @PAR8, '2025-01-18', 2100, 20.8, @DEMO_USER),
(@CAMP2, @PAR3, '2025-02-01', 2000, 19.0, @DEMO_USER);

-- Registros campaña 25/26 (en curso, solo algunos datos)
INSERT INTO campana_registros (campana_id, parcela_id, fecha, kilos, rendimiento_pct, id_user) VALUES
(@CAMP3, @PAR1, '2025-11-18', 5500, 22.3, @DEMO_USER),
(@CAMP3, @PAR1, '2025-12-08', 3800, 23.0, @DEMO_USER),
(@CAMP3, @PAR2, '2025-11-22', 3600, 21.5, @DEMO_USER),
(@CAMP3, @PAR4, '2025-11-28', 7400, 23.1, @DEMO_USER),
(@CAMP3, @PAR4, '2025-12-15', 4800, 22.5, @DEMO_USER),
(@CAMP3, @PAR5, '2025-12-10', 3000, 24.0, @DEMO_USER),
(@CAMP3, @PAR6, '2026-01-08', 4500, 21.8, @DEMO_USER),
(@CAMP3, @PAR7, '2026-01-15', 3100, 22.0, @DEMO_USER),
(@CAMP3, @PAR8, '2026-01-22', 2300, 21.0, @DEMO_USER),
(@CAMP3, @PAR3, '2026-02-05', 2200, 19.8, @DEMO_USER);

-- =============================================================================
-- 13. TAREAS (amplio historial 2024-2026)
-- =============================================================================

-- === 2024 ENERO-FEBRERO: Poda ===
INSERT INTO tareas (fecha, titulo, descripcion, horas, estado, id_user) VALUES
('2024-01-15', 'Poda Los Cerros',               'Poda de producción zona norte',      8, 'realizada', @DEMO_USER),
('2024-01-17', 'Poda Los Cerros',               'Poda de producción zona sur',        7, 'realizada', @DEMO_USER),
('2024-01-22', 'Poda La Vega Alta',             'Poda intensiva',                     6, 'realizada', @DEMO_USER),
('2024-01-29', 'Poda Cortijo La Esperanza',     'Poda olivos centenarios',            9, 'realizada', @DEMO_USER),
('2024-02-05', 'Poda El Almendral',             'Poda de renovación',                 8, 'realizada', @DEMO_USER),
('2024-02-12', 'Poda Cerro del Águila',         'Poda de formación',                  7, 'realizada', @DEMO_USER),
('2024-02-15', 'Recoger desnate Los Cerros',    'Acordonar y recoger restos poda',    6, 'realizada', @DEMO_USER),
('2024-02-19', 'Picar ramón Cortijo Esperanza', 'Triturar ramas de poda',             5, 'realizada', @DEMO_USER);
SET @T2024_01 = LAST_INSERT_ID();

-- === 2024 MARZO: Tratamientos primavera ===
INSERT INTO tareas (fecha, titulo, descripcion, horas, estado, id_user) VALUES
('2024-03-05', 'Cobre post-poda Los Cerros',    'Tratamiento preventivo con cobre',   4, 'realizada', @DEMO_USER),
('2024-03-08', 'Cobre post-poda Cortijo',       'Tratamiento preventivo con cobre',   5, 'realizada', @DEMO_USER),
('2024-03-15', 'Herbicida ruedos parcelas',     'Aplicar herbicida en ruedos',        6, 'realizada', @DEMO_USER),
('2024-03-22', 'Desbrozar Las Navas',           'Desbroce mecanizado',                5, 'realizada', @DEMO_USER);
SET @T2024_03 = LAST_INSERT_ID();

-- === 2024 ABRIL-MAYO: Riego y laboreo ===
INSERT INTO tareas (fecha, titulo, descripcion, horas, estado, id_user) VALUES
('2024-04-01', 'Abrir riego temporada',         'Poner en marcha sistema riego',      3, 'realizada', @DEMO_USER),
('2024-04-10', 'Abono foliar Los Cerros',       'Fertilización foliar primavera',     4, 'realizada', @DEMO_USER),
('2024-04-18', 'Grada discos La Vega Alta',     'Laboreo entre calles',               6, 'realizada', @DEMO_USER),
('2024-05-02', 'Desbrozar El Almendral',        'Desbroce primavera',                 5, 'realizada', @DEMO_USER),
('2024-05-10', 'Riego y fertirrigación',        'Abono con riego parcelas grandes',   4, 'realizada', @DEMO_USER),
('2024-05-20', 'Mantenimiento tractor JD',      'Cambio aceite y filtros',            3, 'realizada', @DEMO_USER);
SET @T2024_04 = LAST_INSERT_ID();

-- === 2024 JUNIO-AGOSTO: Verano ===
INSERT INTO tareas (fecha, titulo, descripcion, horas, estado, id_user) VALUES
('2024-06-05', 'Riego Los Cerros junio',        'Riego prolongado por calor',         3, 'realizada', @DEMO_USER),
('2024-06-15', 'Desbrozar cunetas accesos',     'Limpieza caminos acceso',            6, 'realizada', @DEMO_USER),
('2024-07-01', 'Tratamiento mosca olivo',       'Dimetoato contra Bactrocera',        5, 'realizada', @DEMO_USER),
('2024-07-15', 'Soplar suelos Los Cerros',      'Preparar suelo recolección',         4, 'realizada', @DEMO_USER),
('2024-08-05', 'Riego agosto Cortijo',          'Riego de apoyo verano',              3, 'realizada', @DEMO_USER),
('2024-08-20', 'Grada pinches La Vega Alta',    'Laboreo verano',                     5, 'realizada', @DEMO_USER),
('2024-09-10', 'Cobre pre-recolección',         'Tratamiento preventivo otoño',       5, 'realizada', @DEMO_USER),
('2024-09-25', 'Soplar suelos Cortijo',         'Preparar suelo bajo olivos',         6, 'realizada', @DEMO_USER);
SET @T2024_06 = LAST_INSERT_ID();

-- === 2024 OCTUBRE-NOVIEMBRE: Preparar campaña ===
INSERT INTO tareas (fecha, titulo, descripcion, horas, estado, id_user) VALUES
('2024-10-05', 'Preparar vibrador y mantos',    'Revisar maquinaria recolección',     4, 'realizada', @DEMO_USER),
('2024-10-15', 'Cerrar riego temporada',        'Vaciar y recoger gomas',             3, 'realizada', @DEMO_USER),
('2024-10-28', 'Soplar suelos pre-campaña',     'Última limpieza antes de recogida',  7, 'realizada', @DEMO_USER),
('2024-11-10', 'Recoger aceituna Los Cerros',   'Inicio campaña 24/25',               9, 'realizada', @DEMO_USER),
('2024-11-15', 'Recoger aceituna Los Cerros',   'Continuar recolección zona sur',     8, 'realizada', @DEMO_USER),
('2024-11-20', 'Recoger aceituna La Vega Alta', 'Recolección mecanizada',             7, 'realizada', @DEMO_USER),
('2024-11-25', 'Recoger aceituna Cortijo',      'Recolección olivos grandes',         9, 'realizada', @DEMO_USER),
('2024-12-02', 'Recoger aceituna Cortijo',      'Segunda pasada Cortijo',             8, 'realizada', @DEMO_USER),
('2024-12-05', 'Recoger aceituna Las Navas',    'Arbequina mecanizada',               6, 'realizada', @DEMO_USER),
('2024-12-15', 'Llevar aceituna a cooperativa', 'Transporte última recogida',         4, 'realizada', @DEMO_USER);
SET @T2024_10 = LAST_INSERT_ID();

-- === 2025 ENERO-FEBRERO: Fin campaña + Poda ===
INSERT INTO tareas (fecha, titulo, descripcion, horas, estado, id_user) VALUES
('2025-01-05', 'Recoger aceituna El Almendral', 'Recolección tardía',                 8, 'realizada', @DEMO_USER),
('2025-01-12', 'Recoger aceituna Cerro Águila', 'Secano, recolección manual',         7, 'realizada', @DEMO_USER),
('2025-01-18', 'Recoger aceituna La Cañada',    'Última parcela campaña',             6, 'realizada', @DEMO_USER),
('2025-01-25', 'Poda Los Cerros',               'Inicio poda temporada',              8, 'realizada', @DEMO_USER),
('2025-02-01', 'Poda Cortijo La Esperanza',     'Poda producción',                    9, 'realizada', @DEMO_USER),
('2025-02-08', 'Poda La Vega Alta',             'Poda intensiva',                     6, 'realizada', @DEMO_USER),
('2025-02-15', 'Poda Las Navas',                'Poda de formación plantones',        5, 'realizada', @DEMO_USER),
('2025-02-20', 'Cobre post-poda general',       'Tratamiento preventivo',             5, 'realizada', @DEMO_USER),
('2025-02-25', 'Abono orgánico Cortijo',        'Abonar con Fertiolivo',              6, 'realizada', @DEMO_USER);
SET @T2025_01 = LAST_INSERT_ID();

-- === 2025 MARZO-MAYO: Primavera ===
INSERT INTO tareas (fecha, titulo, descripcion, horas, estado, id_user) VALUES
('2025-03-05', 'Recoger desnate general',       'Recoger restos poda todas parcelas', 7, 'realizada', @DEMO_USER),
('2025-03-12', 'Picar ramón Los Cerros',        'Triturar ramas',                     5, 'realizada', @DEMO_USER),
('2025-03-20', 'Herbicida ruedos',              'Tratamiento herbicida primavera',    5, 'realizada', @DEMO_USER),
('2025-04-01', 'Abrir riego temporada 2025',    'Puesta en marcha riego',             3, 'realizada', @DEMO_USER),
('2025-04-10', 'Azufre parcelas secano',        'Contra repilo en Cerrejón y Águila', 4, 'realizada', @DEMO_USER),
('2025-04-20', 'Grada discos Cortijo',          'Laboreo primavera',                  6, 'realizada', @DEMO_USER),
('2025-05-05', 'Desbrozar todas las parcelas',  'Desbroce primavera general',         8, 'realizada', @DEMO_USER),
('2025-05-15', 'Fertirrigación mayo',           'Abono con riego mensual',            4, 'realizada', @DEMO_USER),
('2025-05-25', 'Arreglar plantones Las Navas',  'Revisión plantación superintensiva', 5, 'realizada', @DEMO_USER);
SET @T2025_03 = LAST_INSERT_ID();

-- === 2025 JUNIO-SEPTIEMBRE: Verano ===
INSERT INTO tareas (fecha, titulo, descripcion, horas, estado, id_user) VALUES
('2025-06-10', 'Desbrozar accesos y cunetas',   'Limpieza caminos verano',            6, 'realizada', @DEMO_USER),
('2025-06-20', 'Kaolín contra mosca',           'Aplicación preventiva kaolín',       5, 'realizada', @DEMO_USER),
('2025-07-01', 'Riego julio intensivo',         'Apertura riego prolongado',          3, 'realizada', @DEMO_USER),
('2025-07-10', 'Trampeo mosca Spinosad',        'Trampas GF-120',                    4, 'realizada', @DEMO_USER),
('2025-07-20', 'Mantenimiento tractor Kubota',  'Revisión completa pre-campaña',     4, 'realizada', @DEMO_USER),
('2025-08-01', 'Riego agosto general',          'Riego apoyo verano',                3, 'realizada', @DEMO_USER),
('2025-08-15', 'Soplar suelos parcelas riego',  'Preparar suelos recolección',       7, 'realizada', @DEMO_USER),
('2025-09-05', 'Grada pinches El Almendral',    'Laboreo pre-campaña',               5, 'realizada', @DEMO_USER),
('2025-09-15', 'Cobre otoño pre-campaña',       'Tratamiento preventivo',            5, 'realizada', @DEMO_USER),
('2025-09-28', 'Cerrar riego 2025',             'Fin temporada riego',               3, 'realizada', @DEMO_USER);
SET @T2025_06 = LAST_INSERT_ID();

-- === 2025 OCT - 2026 FEB: Campaña 25/26 ===
INSERT INTO tareas (fecha, titulo, descripcion, horas, estado, id_user) VALUES
('2025-10-10', 'Preparar maquinaria campaña',   'Revisar vibrador, mantos, remolque', 5, 'realizada', @DEMO_USER),
('2025-10-25', 'Soplar suelos pre-campaña',     'Limpieza final suelos',              8, 'realizada', @DEMO_USER),
('2025-11-12', 'Recoger aceituna Los Cerros',   'Inicio campaña 25/26',               9, 'realizada', @DEMO_USER),
('2025-11-18', 'Recoger aceituna Los Cerros',   'Segunda pasada',                     8, 'realizada', @DEMO_USER),
('2025-11-22', 'Recoger aceituna La Vega Alta', 'Campaña 25/26',                      7, 'realizada', @DEMO_USER),
('2025-11-28', 'Recoger aceituna Cortijo',      'Recolección olivos grandes',         9, 'realizada', @DEMO_USER),
('2025-12-08', 'Recoger aceituna Cortijo',      'Segunda pasada Cortijo',             8, 'realizada', @DEMO_USER),
('2025-12-10', 'Recoger aceituna Las Navas',    'Arbequina mecanizada',               6, 'realizada', @DEMO_USER),
('2025-12-15', 'Recoger aceituna Cortijo',      'Última zona Cortijo',                7, 'realizada', @DEMO_USER),
('2026-01-08', 'Recoger aceituna El Almendral', 'Campaña 25/26',                      8, 'realizada', @DEMO_USER),
('2026-01-15', 'Recoger aceituna Cerro Águila', 'Recolección sierra',                 7, 'realizada', @DEMO_USER),
('2026-01-22', 'Recoger aceituna La Cañada',    'Última parcela',                     6, 'realizada', @DEMO_USER),
('2026-02-05', 'Recoger aceituna El Cerrejón',  'Campaña tardía secano',              7, 'realizada', @DEMO_USER);
SET @T2025_10 = LAST_INSERT_ID();

-- === 2026 FEBRERO-MARZO: Poda + actual ===
INSERT INTO tareas (fecha, titulo, descripcion, horas, estado, id_user) VALUES
('2026-02-10', 'Llevar aceituna a cooperativa', 'Último transporte campaña',          3, 'realizada', @DEMO_USER),
('2026-02-15', 'Cobre post-campaña',           'Tratamiento post-recolección',        5, 'realizada', @DEMO_USER),
('2026-02-20', 'Poda Los Cerros',              'Inicio poda 2026',                    8, 'realizada', @DEMO_USER),
('2026-02-25', 'Poda Cortijo La Esperanza',    'Poda producción',                     9, 'realizada', @DEMO_USER),
('2026-03-01', 'Poda La Vega Alta',            'Poda intensiva',                      6, 'realizada', @DEMO_USER),
('2026-03-05', 'Poda El Almendral',            'Poda renovación',                     7, 'realizada', @DEMO_USER),
('2026-03-10', 'Recoger desnate general',      'Acordonar restos de poda',            6, 'realizada', @DEMO_USER),
('2026-03-15', 'Picar ramón Los Cerros',       'Triturar ramas poda',                 5, 'realizada', @DEMO_USER),
('2026-03-18', 'Cobre post-poda',              'Tratamiento preventivo',              4, 'realizada', @DEMO_USER);
SET @T2026_02 = LAST_INSERT_ID();

-- Tareas pendientes (futuras)
INSERT INTO tareas (fecha, titulo, descripcion, horas, estado, id_user) VALUES
('2026-03-25', 'Herbicida ruedos primavera',   'Aplicar herbicida en ruedos',         0, 'pendiente', @DEMO_USER),
('2026-04-01', 'Abrir riego temporada 2026',   'Puesta en marcha sistema riego',      0, 'pendiente', @DEMO_USER),
('2026-04-10', 'Abono foliar primavera',       'Fertilización foliar general',        0, 'pendiente', @DEMO_USER),
(NULL,         'Reparar valla Cortijo',        'Tramo caído zona este',               0, 'pendiente', @DEMO_USER),
(NULL,         'Plantar 20 plantones Navas',   'Reposición marras superintensivo',    0, 'pendiente', @DEMO_USER);
SET @T2026_PEND = LAST_INSERT_ID();

-- =============================================================================
-- 14. TAREA_TRABAJADORES (asignaciones)
-- =============================================================================
-- Poda 2024
INSERT INTO tarea_trabajadores (tarea_id, trabajador_id, horas_asignadas) VALUES
(@T2024_01,     @TRAB2, 8), (@T2024_01,     @TRAB3, 8),
(@T2024_01+1,   @TRAB2, 7), (@T2024_01+1,   @TRAB3, 7),
(@T2024_01+2,   @TRAB2, 6), (@T2024_01+2,   @TRAB5, 6),
(@T2024_01+3,   @TRAB2, 9), (@T2024_01+3,   @TRAB3, 9), (@T2024_01+3, @TRAB5, 9),
(@T2024_01+4,   @TRAB2, 8), (@T2024_01+4,   @TRAB3, 8),
(@T2024_01+5,   @TRAB2, 7),
(@T2024_01+6,   @TRAB3, 6), (@T2024_01+6,   @TRAB5, 6),
(@T2024_01+7,   @TRAB1, 5),
-- Tratamientos marzo 2024
(@T2024_03,     @TRAB4, 4), (@T2024_03,     @TRAB1, 4),
(@T2024_03+1,   @TRAB4, 5), (@T2024_03+1,   @TRAB1, 5),
(@T2024_03+2,   @TRAB4, 6), (@T2024_03+2,   @TRAB1, 6),
(@T2024_03+3,   @TRAB1, 5),
-- Abril-Mayo 2024
(@T2024_04,     @TRAB1, 3),
(@T2024_04+1,   @TRAB4, 4),
(@T2024_04+2,   @TRAB1, 6),
(@T2024_04+3,   @TRAB1, 5),
(@T2024_04+4,   @TRAB1, 4), (@T2024_04+4, @TRAB4, 4),
(@T2024_04+5,   @TRAB1, 3),
-- Verano 2024
(@T2024_06,     @TRAB1, 3),
(@T2024_06+1,   @TRAB3, 6), (@T2024_06+1, @TRAB5, 6),
(@T2024_06+2,   @TRAB4, 5), (@T2024_06+2, @TRAB1, 5),
(@T2024_06+3,   @TRAB1, 4),
(@T2024_06+4,   @TRAB1, 3),
(@T2024_06+5,   @TRAB1, 5),
(@T2024_06+6,   @TRAB4, 5), (@T2024_06+6, @TRAB1, 5),
(@T2024_06+7,   @TRAB1, 6),
-- Campaña recolección 2024
(@T2024_10,     @TRAB1, 4),
(@T2024_10+1,   @TRAB1, 3),
(@T2024_10+2,   @TRAB1, 7), (@T2024_10+2, @TRAB3, 7),
(@T2024_10+3,   @TRAB1, 9), (@T2024_10+3, @TRAB3, 9), (@T2024_10+3, @TRAB5, 9), (@T2024_10+3, @TRAB6, 9),
(@T2024_10+4,   @TRAB1, 8), (@T2024_10+4, @TRAB3, 8), (@T2024_10+4, @TRAB5, 8), (@T2024_10+4, @TRAB6, 8),
(@T2024_10+5,   @TRAB1, 7), (@T2024_10+5, @TRAB3, 7), (@T2024_10+5, @TRAB6, 7),
(@T2024_10+6,   @TRAB1, 9), (@T2024_10+6, @TRAB3, 9), (@T2024_10+6, @TRAB5, 9), (@T2024_10+6, @TRAB6, 9),
(@T2024_10+7,   @TRAB1, 8), (@T2024_10+7, @TRAB3, 8), (@T2024_10+7, @TRAB6, 8),
(@T2024_10+8,   @TRAB1, 6), (@T2024_10+8, @TRAB5, 6),
(@T2024_10+9,   @TRAB1, 4),
-- 2025 Campaña tardía + poda
(@T2025_01,     @TRAB1, 8), (@T2025_01,   @TRAB3, 8), (@T2025_01,   @TRAB6, 8),
(@T2025_01+1,   @TRAB1, 7), (@T2025_01+1, @TRAB3, 7), (@T2025_01+1, @TRAB6, 7),
(@T2025_01+2,   @TRAB1, 6), (@T2025_01+2, @TRAB3, 6),
(@T2025_01+3,   @TRAB2, 8), (@T2025_01+3, @TRAB3, 8),
(@T2025_01+4,   @TRAB2, 9), (@T2025_01+4, @TRAB3, 9), (@T2025_01+4, @TRAB5, 9),
(@T2025_01+5,   @TRAB2, 6), (@T2025_01+5, @TRAB5, 6),
(@T2025_01+6,   @TRAB2, 5), (@T2025_01+6, @TRAB5, 5),
(@T2025_01+7,   @TRAB4, 5), (@T2025_01+7, @TRAB1, 5),
(@T2025_01+8,   @TRAB1, 6), (@T2025_01+8, @TRAB3, 6),
-- 2025 Primavera
(@T2025_03,     @TRAB3, 7), (@T2025_03,   @TRAB5, 7),
(@T2025_03+1,   @TRAB1, 5),
(@T2025_03+2,   @TRAB4, 5), (@T2025_03+2, @TRAB1, 5),
(@T2025_03+3,   @TRAB1, 3),
(@T2025_03+4,   @TRAB4, 4),
(@T2025_03+5,   @TRAB1, 6),
(@T2025_03+6,   @TRAB1, 8), (@T2025_03+6, @TRAB3, 8),
(@T2025_03+7,   @TRAB1, 4), (@T2025_03+7, @TRAB4, 4),
(@T2025_03+8,   @TRAB3, 5), (@T2025_03+8, @TRAB5, 5),
-- 2025 Verano
(@T2025_06,     @TRAB3, 6), (@T2025_06,   @TRAB5, 6),
(@T2025_06+1,   @TRAB4, 5), (@T2025_06+1, @TRAB1, 5),
(@T2025_06+2,   @TRAB1, 3),
(@T2025_06+3,   @TRAB4, 4),
(@T2025_06+4,   @TRAB1, 4),
(@T2025_06+5,   @TRAB1, 3),
(@T2025_06+6,   @TRAB1, 7), (@T2025_06+6, @TRAB3, 7),
(@T2025_06+7,   @TRAB1, 5),
(@T2025_06+8,   @TRAB4, 5), (@T2025_06+8, @TRAB1, 5),
(@T2025_06+9,   @TRAB1, 3),
-- 2025-2026 Campaña
(@T2025_10,     @TRAB1, 5),
(@T2025_10+1,   @TRAB1, 8), (@T2025_10+1, @TRAB3, 8),
(@T2025_10+2,   @TRAB1, 9), (@T2025_10+2, @TRAB3, 9), (@T2025_10+2, @TRAB5, 9), (@T2025_10+2, @TRAB6, 9),
(@T2025_10+3,   @TRAB1, 8), (@T2025_10+3, @TRAB3, 8), (@T2025_10+3, @TRAB5, 8), (@T2025_10+3, @TRAB6, 8),
(@T2025_10+4,   @TRAB1, 7), (@T2025_10+4, @TRAB3, 7), (@T2025_10+4, @TRAB6, 7),
(@T2025_10+5,   @TRAB1, 9), (@T2025_10+5, @TRAB3, 9), (@T2025_10+5, @TRAB5, 9), (@T2025_10+5, @TRAB6, 9),
(@T2025_10+6,   @TRAB1, 8), (@T2025_10+6, @TRAB3, 8), (@T2025_10+6, @TRAB6, 8),
(@T2025_10+7,   @TRAB1, 6), (@T2025_10+7, @TRAB5, 6),
(@T2025_10+8,   @TRAB1, 7), (@T2025_10+8, @TRAB3, 7), (@T2025_10+8, @TRAB6, 7),
(@T2025_10+9,   @TRAB1, 8), (@T2025_10+9, @TRAB3, 8), (@T2025_10+9, @TRAB6, 8),
(@T2025_10+10,  @TRAB1, 7), (@T2025_10+10,@TRAB3, 7), (@T2025_10+10,@TRAB6, 7),
(@T2025_10+11,  @TRAB1, 6), (@T2025_10+11,@TRAB3, 6),
(@T2025_10+12,  @TRAB1, 7), (@T2025_10+12,@TRAB3, 7), (@T2025_10+12,@TRAB6, 7),
-- 2026 Poda
(@T2026_02,     @TRAB1, 3),
(@T2026_02+1,   @TRAB4, 5), (@T2026_02+1, @TRAB1, 5),
(@T2026_02+2,   @TRAB2, 8), (@T2026_02+2, @TRAB3, 8),
(@T2026_02+3,   @TRAB2, 9), (@T2026_02+3, @TRAB3, 9), (@T2026_02+3, @TRAB5, 9),
(@T2026_02+4,   @TRAB2, 6), (@T2026_02+4, @TRAB5, 6),
(@T2026_02+5,   @TRAB2, 7), (@T2026_02+5, @TRAB3, 7),
(@T2026_02+6,   @TRAB3, 6), (@T2026_02+6, @TRAB5, 6),
(@T2026_02+7,   @TRAB1, 5),
(@T2026_02+8,   @TRAB4, 4), (@T2026_02+8, @TRAB1, 4);

-- =============================================================================
-- 15. TAREA_PARCELAS (asignaciones)
-- =============================================================================
INSERT INTO tarea_parcelas (tarea_id, parcela_id) VALUES
-- Poda 2024
(@T2024_01, @PAR1), (@T2024_01+1, @PAR1), (@T2024_01+2, @PAR2),
(@T2024_01+3, @PAR4), (@T2024_01+4, @PAR6), (@T2024_01+5, @PAR7),
(@T2024_01+6, @PAR1), (@T2024_01+7, @PAR4),
-- Tratamientos marzo 2024
(@T2024_03, @PAR1), (@T2024_03+1, @PAR4),
(@T2024_03+2, @PAR1), (@T2024_03+2, @PAR2), (@T2024_03+2, @PAR5),
(@T2024_03+3, @PAR5),
-- Abril-Mayo 2024
(@T2024_04, @PAR1), (@T2024_04, @PAR2), (@T2024_04, @PAR4),
(@T2024_04+1, @PAR1),
(@T2024_04+2, @PAR2),
(@T2024_04+3, @PAR6),
(@T2024_04+4, @PAR1), (@T2024_04+4, @PAR4),
-- Verano 2024
(@T2024_06, @PAR1),
(@T2024_06+1, @PAR1), (@T2024_06+1, @PAR4),
(@T2024_06+2, @PAR1), (@T2024_06+2, @PAR4), (@T2024_06+2, @PAR7),
(@T2024_06+3, @PAR1),
(@T2024_06+4, @PAR4),
(@T2024_06+5, @PAR2),
(@T2024_06+6, @PAR1), (@T2024_06+6, @PAR3), (@T2024_06+6, @PAR4),
(@T2024_06+7, @PAR4),
-- Recolección 2024
(@T2024_10+2, @PAR1), (@T2024_10+3, @PAR1),
(@T2024_10+4, @PAR1), (@T2024_10+5, @PAR2),
(@T2024_10+6, @PAR4), (@T2024_10+7, @PAR4),
(@T2024_10+8, @PAR5),
-- 2025 ene-feb
(@T2025_01, @PAR6), (@T2025_01+1, @PAR7), (@T2025_01+2, @PAR8),
(@T2025_01+3, @PAR1), (@T2025_01+4, @PAR4), (@T2025_01+5, @PAR2),
(@T2025_01+6, @PAR5), (@T2025_01+7, @PAR1), (@T2025_01+7, @PAR4),
(@T2025_01+8, @PAR4),
-- 2025 primavera
(@T2025_03, @PAR1), (@T2025_03, @PAR4),
(@T2025_03+1, @PAR1),
(@T2025_03+2, @PAR1), (@T2025_03+2, @PAR2),
(@T2025_03+3, @PAR1), (@T2025_03+3, @PAR2), (@T2025_03+3, @PAR4),
(@T2025_03+4, @PAR3), (@T2025_03+4, @PAR7),
(@T2025_03+5, @PAR4),
(@T2025_03+6, @PAR1), (@T2025_03+6, @PAR2), (@T2025_03+6, @PAR4), (@T2025_03+6, @PAR6),
(@T2025_03+7, @PAR1), (@T2025_03+7, @PAR4),
(@T2025_03+8, @PAR5),
-- 2025 verano
(@T2025_06, @PAR1), (@T2025_06, @PAR4),
(@T2025_06+1, @PAR1), (@T2025_06+1, @PAR4), (@T2025_06+1, @PAR6),
(@T2025_06+2, @PAR1), (@T2025_06+2, @PAR4),
(@T2025_06+3, @PAR1), (@T2025_06+3, @PAR4), (@T2025_06+3, @PAR7),
(@T2025_06+6, @PAR1), (@T2025_06+6, @PAR4), (@T2025_06+6, @PAR6),
(@T2025_06+7, @PAR6),
(@T2025_06+8, @PAR1), (@T2025_06+8, @PAR4), (@T2025_06+8, @PAR3),
-- Campaña 25/26
(@T2025_10+2, @PAR1), (@T2025_10+3, @PAR1),
(@T2025_10+4, @PAR2), (@T2025_10+5, @PAR4),
(@T2025_10+6, @PAR4), (@T2025_10+7, @PAR5),
(@T2025_10+8, @PAR4), (@T2025_10+9, @PAR6),
(@T2025_10+10, @PAR7), (@T2025_10+11, @PAR8),
(@T2025_10+12, @PAR3),
-- 2026 poda
(@T2026_02+2, @PAR1), (@T2026_02+3, @PAR4),
(@T2026_02+4, @PAR2), (@T2026_02+5, @PAR6),
(@T2026_02+6, @PAR1), (@T2026_02+6, @PAR4),
(@T2026_02+7, @PAR1),
(@T2026_02+8, @PAR1), (@T2026_02+8, @PAR2);

-- =============================================================================
-- 16. TAREA_TRABAJOS (tipo de trabajo asignado a cada tarea)
-- =============================================================================
INSERT INTO tarea_trabajos (tarea_id, trabajo_id, horas_trabajo, precio_hora) VALUES
-- Poda 2024 → Escamujar
(@T2024_01, @TRAB_ESCAMUJAR, 8, 9.23), (@T2024_01+1, @TRAB_ESCAMUJAR, 7, 9.23),
(@T2024_01+2, @TRAB_ESCAMUJAR, 6, 9.23), (@T2024_01+3, @TRAB_ESCAMUJAR, 9, 9.23),
(@T2024_01+4, @TRAB_ESCAMUJAR, 8, 9.23), (@T2024_01+5, @TRAB_ESCAMUJAR, 7, 9.23),
(@T2024_01+6, @TRAB_ACORDONAR_DESNATE, 6, 9.23),
(@T2024_01+7, @TRAB_PICAR_RAMON, 5, 9.23),
-- Tratamientos marzo 2024
(@T2024_03, @TRAB_SULFATO, 4, 0), (@T2024_03+1, @TRAB_SULFATO, 5, 0),
(@T2024_03+2, @TRAB_HERB_TRACTOR, 6, 0),
(@T2024_03+3, @TRAB_DESBROZAR, 5, 0),
-- Abril-Mayo 2024
(@T2024_04, @TRAB_ABRIR_RIEGO, 3, 9.23),
(@T2024_04+1, @TRAB_HOJA_TRACTOR, 4, 0),
(@T2024_04+2, @TRAB_GRADA_DISCOS, 6, 0),
(@T2024_04+3, @TRAB_DESBROZAR, 5, 0),
(@T2024_04+4, @TRAB_ABONO_RIEGO, 4, 0),
(@T2024_04+5, @TRAB_MANT_VEHICULOS, 3, 0),
-- Verano 2024
(@T2024_06, @TRAB_ABRIR_RIEGO, 3, 9.23),
(@T2024_06+1, @TRAB_DESBROZAR, 6, 0),
(@T2024_06+2, @TRAB_SULFATO, 5, 0),
(@T2024_06+3, @TRAB_SOPLAR_TRACTOR, 4, 0),
(@T2024_06+4, @TRAB_ABRIR_RIEGO, 3, 9.23),
(@T2024_06+5, @TRAB_GRADA_PINCHES, 5, 0),
(@T2024_06+6, @TRAB_SULFATO, 5, 0),
(@T2024_06+7, @TRAB_SOPLAR_TRACTOR, 6, 0),
-- Recolección 2024
(@T2024_10, @TRAB_MANT_VEHICULOS, 4, 0),
(@T2024_10+1, @TRAB_CERRAR_RIEGO, 3, 9.23),
(@T2024_10+2, @TRAB_SOPLAR_TRACTOR, 7, 0),
(@T2024_10+3, @TRAB_RECOGER_ACEITUNA, 9, 0), (@T2024_10+4, @TRAB_RECOGER_ACEITUNA, 8, 0),
(@T2024_10+5, @TRAB_RECOGER_ACEITUNA, 7, 0), (@T2024_10+6, @TRAB_RECOGER_ACEITUNA, 9, 0),
(@T2024_10+7, @TRAB_RECOGER_ACEITUNA, 8, 0), (@T2024_10+8, @TRAB_RECOGER_ACEITUNA, 6, 0),
(@T2024_10+9, @TRAB_PAPELEO, 4, 0),
-- 2025 enero-febrero
(@T2025_01, @TRAB_RECOGER_ACEITUNA, 8, 0), (@T2025_01+1, @TRAB_RECOGER_ACEITUNA, 7, 0),
(@T2025_01+2, @TRAB_RECOGER_ACEITUNA, 6, 0),
(@T2025_01+3, @TRAB_ESCAMUJAR, 8, 9.23), (@T2025_01+4, @TRAB_ESCAMUJAR, 9, 9.23),
(@T2025_01+5, @TRAB_ESCAMUJAR, 6, 9.23), (@T2025_01+6, @TRAB_ESCAMUJAR, 5, 9.23),
(@T2025_01+7, @TRAB_SULFATO, 5, 0),
(@T2025_01+8, @TRAB_ABONO_ABONADORA, 6, 0),
-- 2025 primavera
(@T2025_03, @TRAB_ACORDONAR_DESNATE, 7, 9.23),
(@T2025_03+1, @TRAB_PICAR_RAMON, 5, 0),
(@T2025_03+2, @TRAB_HERB_TRACTOR, 5, 0),
(@T2025_03+3, @TRAB_ABRIR_RIEGO, 3, 9.23),
(@T2025_03+4, @TRAB_SULFATO, 4, 0),
(@T2025_03+5, @TRAB_GRADA_DISCOS, 6, 0),
(@T2025_03+6, @TRAB_DESBROZAR, 8, 0),
(@T2025_03+7, @TRAB_ABONO_RIEGO, 4, 0),
(@T2025_03+8, @TRAB_ARREGLAR_PLANTONES, 5, 9.23),
-- 2025 verano
(@T2025_06, @TRAB_DESBROZAR, 6, 0),
(@T2025_06+1, @TRAB_SULFATO, 5, 0),
(@T2025_06+2, @TRAB_ABRIR_RIEGO, 3, 9.23),
(@T2025_06+3, @TRAB_SULFATO, 4, 0),
(@T2025_06+4, @TRAB_MANT_VEHICULOS, 4, 0),
(@T2025_06+5, @TRAB_ABRIR_RIEGO, 3, 9.23),
(@T2025_06+6, @TRAB_SOPLAR_TRACTOR, 7, 0),
(@T2025_06+7, @TRAB_GRADA_PINCHES, 5, 0),
(@T2025_06+8, @TRAB_SULFATO, 5, 0),
(@T2025_06+9, @TRAB_CERRAR_RIEGO, 3, 9.23),
-- Campaña 25/26
(@T2025_10, @TRAB_MANT_VEHICULOS, 5, 0),
(@T2025_10+1, @TRAB_SOPLAR_TRACTOR, 8, 0),
(@T2025_10+2, @TRAB_RECOGER_ACEITUNA, 9, 0), (@T2025_10+3, @TRAB_RECOGER_ACEITUNA, 8, 0),
(@T2025_10+4, @TRAB_RECOGER_ACEITUNA, 7, 0), (@T2025_10+5, @TRAB_RECOGER_ACEITUNA, 9, 0),
(@T2025_10+6, @TRAB_RECOGER_ACEITUNA, 8, 0), (@T2025_10+7, @TRAB_RECOGER_ACEITUNA, 6, 0),
(@T2025_10+8, @TRAB_RECOGER_ACEITUNA, 7, 0), (@T2025_10+9, @TRAB_RECOGER_ACEITUNA, 8, 0),
(@T2025_10+10, @TRAB_RECOGER_ACEITUNA, 7, 0), (@T2025_10+11, @TRAB_RECOGER_ACEITUNA, 6, 0),
(@T2025_10+12, @TRAB_RECOGER_ACEITUNA, 7, 0),
-- 2026 poda
(@T2026_02, @TRAB_PAPELEO, 3, 0),
(@T2026_02+1, @TRAB_SULFATO, 5, 0),
(@T2026_02+2, @TRAB_ESCAMUJAR, 8, 9.23), (@T2026_02+3, @TRAB_ESCAMUJAR, 9, 9.23),
(@T2026_02+4, @TRAB_ESCAMUJAR, 6, 9.23), (@T2026_02+5, @TRAB_ESCAMUJAR, 7, 9.23),
(@T2026_02+6, @TRAB_ACORDONAR_DESNATE, 6, 9.23),
(@T2026_02+7, @TRAB_PICAR_RAMON, 5, 0),
(@T2026_02+8, @TRAB_SULFATO, 4, 0);

-- =============================================================================
-- 17. MOVIMIENTOS ECONÓMICOS (2 años completos)
-- =============================================================================

-- ---- 2024 GASTOS ----
INSERT INTO movimientos (fecha, tipo, concepto, categoria, importe, proveedor_id, vehiculo_id, parcela_id, trabajador_id, estado, cuenta) VALUES
-- Enero 2024
('2024-01-10', 'gasto', 'Gasóleo agrícola enero',                 'compras',       450.00, @PROV5, @VEH1, NULL,   NULL,   'pagado', 'banco'),
('2024-01-20', 'gasto', 'Tijeras y herramientas poda',            'compras',       280.00, @PROV1, NULL,   NULL,   NULL,   'pagado', 'efectivo'),
('2024-01-31', 'gasto', 'Seguro tractor John Deere anual',        'seguros',       420.00, NULL,   @VEH1, NULL,   NULL,   'pagado', 'banco'),
-- Febrero 2024
('2024-02-05', 'gasto', 'Gasóleo febrero',                        'compras',       380.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2024-02-15', 'gasto', 'Reparación desbrozadora',                'reparaciones',  120.00, @PROV3, NULL,   NULL,   NULL,   'pagado', 'efectivo'),
-- Marzo 2024
('2024-03-05', 'gasto', 'Cobre Nordox 25kg',                      'compras',       185.00, @PROV1, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2024-03-10', 'gasto', 'Glifosato 20L',                          'compras',       95.00,  @PROV1, NULL,   NULL,   NULL,   'pagado', 'efectivo'),
('2024-03-15', 'gasto', 'Gasóleo marzo',                          'compras',       320.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2024-03-25', 'gasto', 'Gestoría trimestre T1',                  'gestoria',      180.00, NULL,   NULL,   NULL,   NULL,   'pagado', 'banco'),
-- Abril 2024
('2024-04-02', 'gasto', 'Abono foliar NPK 50kg',                 'compras',       210.00, @PROV1, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2024-04-10', 'gasto', 'Gasóleo abril',                          'compras',       290.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2024-04-20', 'gasto', 'Reparación bomba riego',                 'reparaciones',  340.00, @PROV2, NULL,   @PAR1,  NULL,   'pagado', 'banco'),
-- Mayo 2024
('2024-05-05', 'gasto', 'Gasóleo mayo',                           'compras',       310.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2024-05-15', 'gasto', 'Filtros y aceite tractor JD',            'reparaciones',  175.00, @PROV3, @VEH1, NULL,   NULL,   'pagado', 'banco'),
('2024-05-25', 'gasto', 'Recibo agua comunidad regantes',         'impuestos',     680.00, NULL,   NULL,   NULL,   NULL,   'pagado', 'banco'),
-- Junio 2024
('2024-06-01', 'gasto', 'Gasóleo junio',                          'compras',       350.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2024-06-15', 'gasto', 'Seguro pick-up Toyota anual',            'seguros',       350.00, NULL,   @VEH3, NULL,   NULL,   'pagado', 'banco'),
('2024-06-25', 'gasto', 'Gestoría trimestre T2',                  'gestoria',      180.00, NULL,   NULL,   NULL,   NULL,   'pagado', 'banco'),
-- Julio 2024
('2024-07-01', 'gasto', 'Dimetoato 10L (mosca olivo)',            'compras',       78.00,  @PROV1, NULL,   NULL,   NULL,   'pagado', 'efectivo'),
('2024-07-10', 'gasto', 'Gasóleo julio',                          'compras',       280.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
-- Agosto 2024
('2024-08-05', 'gasto', 'Gasóleo agosto',                         'compras',       260.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2024-08-20', 'gasto', 'Reparar neumático tractor',              'reparaciones',  220.00, @PROV3, @VEH1, NULL,   NULL,   'pagado', 'efectivo'),
-- Septiembre 2024
('2024-09-05', 'gasto', 'Gasóleo septiembre',                     'compras',       300.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2024-09-15', 'gasto', 'Cobre pre-recolección',                  'compras',       145.00, @PROV1, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2024-09-25', 'gasto', 'Gestoría trimestre T3',                  'gestoria',      180.00, NULL,   NULL,   NULL,   NULL,   'pagado', 'banco'),
('2024-09-30', 'gasto', 'IBI rústica anual parcelas',             'impuestos',    1200.00, NULL,   NULL,   NULL,   NULL,   'pagado', 'banco'),
-- Octubre 2024
('2024-10-05', 'gasto', 'Reparación vibrador olivos',             'reparaciones',  280.00, @PROV3, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2024-10-10', 'gasto', 'Mantos recolección nuevos',              'compras',       450.00, @PROV1, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2024-10-15', 'gasto', 'Gasóleo octubre',                        'compras',       420.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
-- Noviembre 2024
('2024-11-01', 'gasto', 'Gasóleo noviembre',                      'compras',       580.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2024-11-15', 'gasto', 'Cuadrilla recolección noviembre',        'compras',      2400.00, NULL,   NULL,   NULL,   @TRAB6, 'pagado', 'banco'),
('2024-11-25', 'gasto', 'ITV tractor John Deere',                 'impuestos',      65.00, NULL,   @VEH1, NULL,   NULL,   'pagado', 'efectivo'),
-- Diciembre 2024
('2024-12-01', 'gasto', 'Gasóleo diciembre',                      'compras',       520.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2024-12-10', 'gasto', 'Cuadrilla recolección diciembre',        'compras',      1800.00, NULL,   NULL,   NULL,   @TRAB6, 'pagado', 'banco'),
('2024-12-20', 'gasto', 'Gestoría trimestre T4 + cierre anual',   'gestoria',      350.00, NULL,   NULL,   NULL,   NULL,   'pagado', 'banco'),
('2024-12-28', 'gasto', 'Seguro tractor Kubota anual',            'seguros',       480.00, NULL,   @VEH2, NULL,   NULL,   'pagado', 'banco'),

-- ---- 2024 INGRESOS ----
('2024-02-28', 'ingreso', 'Liquidación aceite campaña 23/24',          'liquidacion_aceite', 35200.00, @PROV4, NULL, NULL, NULL, 'pagado', 'banco'),
('2024-04-15', 'ingreso', 'Labores a terceros — grada parcela vecina', 'labores_terceros',   450.00,   NULL,   NULL, NULL, NULL, 'pagado', 'efectivo'),
('2024-06-15', 'ingreso', 'Subvención PAC 2023',                      'subvenciones',       8400.00,  NULL,   NULL, NULL, NULL, 'pagado', 'banco'),
('2024-07-20', 'ingreso', 'Labores a terceros — desbrozar',           'labores_terceros',   320.00,   NULL,   NULL, NULL, NULL, 'pagado', 'efectivo'),
('2024-09-10', 'ingreso', 'Venta leña poda',                          'labores_terceros',   600.00,   NULL,   NULL, NULL, NULL, 'pagado', 'efectivo'),

-- ---- 2025 GASTOS ----
('2025-01-08', 'gasto', 'Gasóleo enero',                          'compras',       490.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-01-15', 'gasto', 'Cuadrilla recolección enero',            'compras',      1200.00, NULL,   NULL,   NULL,   @TRAB6, 'pagado', 'banco'),
('2025-01-20', 'gasto', 'Oxicloruro de cobre 30kg',               'compras',       165.00, @PROV1, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-01-30', 'gasto', 'Seguro tractor JD anual 2025',           'seguros',       435.00, NULL,   @VEH1, NULL,   NULL,   'pagado', 'banco'),
('2025-02-05', 'gasto', 'Gasóleo febrero',                        'compras',       360.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-02-10', 'gasto', 'Abono orgánico Fertiolivo 500kg',        'compras',       420.00, @PROV1, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-02-20', 'gasto', 'Plantones olivo reposición x20',         'inversiones',   300.00, @PROV6, NULL,   @PAR5,  NULL,   'pagado', 'banco'),
('2025-03-05', 'gasto', 'Gasóleo marzo',                          'compras',       340.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-03-15', 'gasto', 'Imidacloprid 5L',                        'compras',       92.00,  @PROV1, NULL,   NULL,   NULL,   'pagado', 'efectivo'),
('2025-03-25', 'gasto', 'Gestoría T1 2025',                       'gestoria',      190.00, NULL,   NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-04-05', 'gasto', 'Gasóleo abril',                          'compras',       310.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-04-10', 'gasto', 'Azufre mojable 15kg',                    'compras',       68.00,  @PROV1, NULL,   NULL,   NULL,   'pagado', 'efectivo'),
('2025-04-20', 'gasto', 'Reparación sistema goteo La Vega',       'reparaciones',  290.00, @PROV2, NULL,   @PAR2,  NULL,   'pagado', 'banco'),
('2025-05-05', 'gasto', 'Gasóleo mayo',                           'compras',       330.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-05-20', 'gasto', 'Recibo agua comunidad regantes',         'impuestos',     720.00, NULL,   NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-06-01', 'gasto', 'Gasóleo junio',                          'compras',       370.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-06-10', 'gasto', 'Kaolin Surround WP 20kg',               'compras',       145.00, @PROV1, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-06-20', 'gasto', 'Seguro pick-up Toyota 2025',             'seguros',       365.00, NULL,   @VEH3, NULL,   NULL,   'pagado', 'banco'),
('2025-06-25', 'gasto', 'Gestoría T2 2025',                       'gestoria',      190.00, NULL,   NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-07-01', 'gasto', 'Spinosad GF-120 8L',                    'compras',       112.00, @PROV1, NULL,   NULL,   NULL,   'pagado', 'efectivo'),
('2025-07-10', 'gasto', 'Gasóleo julio',                          'compras',       300.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-07-20', 'gasto', 'Revisión Kubota pre-campaña',            'reparaciones',  240.00, @PROV3, @VEH2, NULL,   NULL,   'pagado', 'banco'),
('2025-08-05', 'gasto', 'Gasóleo agosto',                         'compras',       280.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-09-05', 'gasto', 'Gasóleo septiembre',                     'compras',       320.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-09-15', 'gasto', 'Oxicloruro cobre otoño',                 'compras',       155.00, @PROV1, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-09-25', 'gasto', 'Gestoría T3 2025',                       'gestoria',      190.00, NULL,   NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-09-30', 'gasto', 'IBI rústica 2025',                       'impuestos',    1250.00, NULL,   NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-10-05', 'gasto', 'Reparación vibrador',                    'reparaciones',  180.00, @PROV3, NULL,   NULL,   NULL,   'pagado', 'efectivo'),
('2025-10-15', 'gasto', 'Gasóleo octubre',                        'compras',       440.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-11-01', 'gasto', 'Gasóleo noviembre',                      'compras',       620.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-11-15', 'gasto', 'Cuadrilla recolección noviembre',        'compras',      2600.00, NULL,   NULL,   NULL,   @TRAB6, 'pagado', 'banco'),
('2025-12-01', 'gasto', 'Gasóleo diciembre',                      'compras',       550.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-12-10', 'gasto', 'Cuadrilla recolección diciembre',        'compras',      2000.00, NULL,   NULL,   NULL,   @TRAB6, 'pagado', 'banco'),
('2025-12-20', 'gasto', 'Gestoría T4 + cierre 2025',             'gestoria',      360.00, NULL,   NULL,   NULL,   NULL,   'pagado', 'banco'),
('2025-12-28', 'gasto', 'Seguro Kubota 2026',                    'seguros',       495.00, NULL,   @VEH2, NULL,   NULL,   'pagado', 'banco'),

-- ---- 2026 GASTOS (hasta marzo) ----
('2026-01-08', 'gasto', 'Gasóleo enero 2026',                     'compras',       510.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2026-01-12', 'gasto', 'Cuadrilla recolección enero',            'compras',      1400.00, NULL,   NULL,   NULL,   @TRAB6, 'pagado', 'banco'),
('2026-01-15', 'gasto', 'Oxicloruro cobre post-campaña',          'compras',       170.00, @PROV1, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2026-01-30', 'gasto', 'Seguro tractor JD 2026',                 'seguros',       450.00, NULL,   @VEH1, NULL,   NULL,   'pagado', 'banco'),
('2026-02-05', 'gasto', 'Gasóleo febrero 2026',                   'compras',       380.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2026-02-15', 'gasto', 'Reparación cadena motosierra',           'reparaciones',   85.00, @PROV3, NULL,   NULL,   NULL,   'pagado', 'efectivo'),
('2026-03-05', 'gasto', 'Gasóleo marzo 2026',                     'compras',       340.00, @PROV5, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2026-03-10', 'gasto', 'Cobre para tratamiento post-poda',       'compras',       155.00, @PROV1, NULL,   NULL,   NULL,   'pagado', 'banco'),
('2026-03-15', 'gasto', 'Gestoría T1 2026',                       'gestoria',      195.00, NULL,   NULL,   NULL,   NULL,   'pendiente','banco'),

-- ---- 2025 INGRESOS ----
('2025-03-10', 'ingreso', 'Liquidación aceite campaña 24/25',          'liquidacion_aceite', 38500.00, @PROV4, NULL, NULL, NULL, 'pagado', 'banco'),
('2025-04-20', 'ingreso', 'Labores a terceros — tractor',             'labores_terceros',   520.00,   NULL,   NULL, NULL, NULL, 'pagado', 'efectivo'),
('2025-06-20', 'ingreso', 'Subvención PAC 2024',                      'subvenciones',       8800.00,  NULL,   NULL, NULL, NULL, 'pagado', 'banco'),
('2025-08-10', 'ingreso', 'Labores a terceros — desbroce',            'labores_terceros',   380.00,   NULL,   NULL, NULL, NULL, 'pagado', 'efectivo'),
('2025-09-15', 'ingreso', 'Venta leña poda 2025',                     'labores_terceros',   750.00,   NULL,   NULL, NULL, NULL, 'pagado', 'efectivo'),

-- ---- 2026 INGRESOS (pendientes/recientes) ----
('2026-03-01', 'ingreso', 'Liquidación aceite campaña 25/26 (parcial)','liquidacion_aceite', 22000.00, @PROV4, NULL, NULL, NULL, 'pagado', 'banco'),
('2026-03-20', 'ingreso', 'Liquidación aceite 25/26 (resto)',          'liquidacion_aceite', 18500.00, @PROV4, NULL, NULL, NULL, 'pendiente','banco');

-- =============================================================================
-- 18. PAGOS MENSUALES TRABAJADORES (2024 completo + 2025 + 2026 parcial)
-- =============================================================================
-- Manuel (tractorista, desde nov 2023)
INSERT INTO pagos_mensuales_trabajadores (trabajador_id, month, year, importe_total, pagado, fecha_pago, id_user) VALUES
(@TRAB1, 1,  2024, 720.00,  1, '2024-02-05', @DEMO_USER),
(@TRAB1, 2,  2024, 680.00,  1, '2024-03-05', @DEMO_USER),
(@TRAB1, 3,  2024, 850.00,  1, '2024-04-05', @DEMO_USER),
(@TRAB1, 4,  2024, 620.00,  1, '2024-05-05', @DEMO_USER),
(@TRAB1, 5,  2024, 580.00,  1, '2024-06-05', @DEMO_USER),
(@TRAB1, 6,  2024, 540.00,  1, '2024-07-05', @DEMO_USER),
(@TRAB1, 7,  2024, 600.00,  1, '2024-08-05', @DEMO_USER),
(@TRAB1, 8,  2024, 520.00,  1, '2024-09-05', @DEMO_USER),
(@TRAB1, 9,  2024, 650.00,  1, '2024-10-05', @DEMO_USER),
(@TRAB1, 10, 2024, 780.00,  1, '2024-11-05', @DEMO_USER),
(@TRAB1, 11, 2024, 950.00,  1, '2024-12-05', @DEMO_USER),
(@TRAB1, 12, 2024, 880.00,  1, '2025-01-05', @DEMO_USER),
(@TRAB1, 1,  2025, 820.00,  1, '2025-02-05', @DEMO_USER),
(@TRAB1, 2,  2025, 750.00,  1, '2025-03-05', @DEMO_USER),
(@TRAB1, 3,  2025, 880.00,  1, '2025-04-05', @DEMO_USER),
(@TRAB1, 4,  2025, 640.00,  1, '2025-05-05', @DEMO_USER),
(@TRAB1, 5,  2025, 610.00,  1, '2025-06-05', @DEMO_USER),
(@TRAB1, 6,  2025, 560.00,  1, '2025-07-05', @DEMO_USER),
(@TRAB1, 7,  2025, 620.00,  1, '2025-08-05', @DEMO_USER),
(@TRAB1, 8,  2025, 540.00,  1, '2025-09-05', @DEMO_USER),
(@TRAB1, 9,  2025, 680.00,  1, '2025-10-05', @DEMO_USER),
(@TRAB1, 10, 2025, 810.00,  1, '2025-11-05', @DEMO_USER),
(@TRAB1, 11, 2025, 980.00,  1, '2025-12-05', @DEMO_USER),
(@TRAB1, 12, 2025, 920.00,  1, '2026-01-05', @DEMO_USER),
(@TRAB1, 1,  2026, 860.00,  1, '2026-02-05', @DEMO_USER),
(@TRAB1, 2,  2026, 780.00,  1, '2026-03-05', @DEMO_USER),
(@TRAB1, 3,  2026, 650.00,  0, NULL,          @DEMO_USER),

-- José Luis (podador, desde ene 2024)
(@TRAB2, 1,  2024, 640.00,  1, '2024-02-05', @DEMO_USER),
(@TRAB2, 2,  2024, 720.00,  1, '2024-03-05', @DEMO_USER),
(@TRAB2, 3,  2024, 320.00,  1, '2024-04-05', @DEMO_USER),
(@TRAB2, 4,  2024, 0.00,    1, '2024-05-05', @DEMO_USER),
(@TRAB2, 5,  2024, 0.00,    1, '2024-06-05', @DEMO_USER),
(@TRAB2, 11, 2024, 0.00,    1, '2024-12-05', @DEMO_USER),
(@TRAB2, 12, 2024, 0.00,    1, '2025-01-05', @DEMO_USER),
(@TRAB2, 1,  2025, 680.00,  1, '2025-02-05', @DEMO_USER),
(@TRAB2, 2,  2025, 760.00,  1, '2025-03-05', @DEMO_USER),
(@TRAB2, 3,  2025, 350.00,  1, '2025-04-05', @DEMO_USER),
(@TRAB2, 1,  2026, 0.00,    1, '2026-02-05', @DEMO_USER),
(@TRAB2, 2,  2026, 780.00,  1, '2026-03-05', @DEMO_USER),
(@TRAB2, 3,  2026, 690.00,  0, NULL,          @DEMO_USER),

-- Rafael (peón, desde feb 2024)
(@TRAB3, 2,  2024, 580.00,  1, '2024-03-05', @DEMO_USER),
(@TRAB3, 3,  2024, 450.00,  1, '2024-04-05', @DEMO_USER),
(@TRAB3, 4,  2024, 380.00,  1, '2024-05-05', @DEMO_USER),
(@TRAB3, 5,  2024, 420.00,  1, '2024-06-05', @DEMO_USER),
(@TRAB3, 6,  2024, 480.00,  1, '2024-07-05', @DEMO_USER),
(@TRAB3, 7,  2024, 350.00,  1, '2024-08-05', @DEMO_USER),
(@TRAB3, 8,  2024, 280.00,  1, '2024-09-05', @DEMO_USER),
(@TRAB3, 9,  2024, 320.00,  1, '2024-10-05', @DEMO_USER),
(@TRAB3, 10, 2024, 550.00,  1, '2024-11-05', @DEMO_USER),
(@TRAB3, 11, 2024, 850.00,  1, '2024-12-05', @DEMO_USER),
(@TRAB3, 12, 2024, 780.00,  1, '2025-01-05', @DEMO_USER),
(@TRAB3, 1,  2025, 720.00,  1, '2025-02-05', @DEMO_USER),
(@TRAB3, 2,  2025, 680.00,  1, '2025-03-05', @DEMO_USER),
(@TRAB3, 3,  2025, 520.00,  1, '2025-04-05', @DEMO_USER),
(@TRAB3, 4,  2025, 410.00,  1, '2025-05-05', @DEMO_USER),
(@TRAB3, 5,  2025, 450.00,  1, '2025-06-05', @DEMO_USER),
(@TRAB3, 6,  2025, 500.00,  1, '2025-07-05', @DEMO_USER),
(@TRAB3, 7,  2025, 380.00,  1, '2025-08-05', @DEMO_USER),
(@TRAB3, 8,  2025, 300.00,  1, '2025-09-05', @DEMO_USER),
(@TRAB3, 9,  2025, 350.00,  1, '2025-10-05', @DEMO_USER),
(@TRAB3, 10, 2025, 580.00,  1, '2025-11-05', @DEMO_USER),
(@TRAB3, 11, 2025, 900.00,  1, '2025-12-05', @DEMO_USER),
(@TRAB3, 12, 2025, 820.00,  1, '2026-01-05', @DEMO_USER),
(@TRAB3, 1,  2026, 750.00,  1, '2026-02-05', @DEMO_USER),
(@TRAB3, 2,  2026, 710.00,  1, '2026-03-05', @DEMO_USER),
(@TRAB3, 3,  2026, 580.00,  0, NULL,          @DEMO_USER),

-- Ana María (tratamientos, desde mar 2024)
(@TRAB4, 3,  2024, 480.00,  1, '2024-04-05', @DEMO_USER),
(@TRAB4, 4,  2024, 320.00,  1, '2024-05-05', @DEMO_USER),
(@TRAB4, 5,  2024, 350.00,  1, '2024-06-05', @DEMO_USER),
(@TRAB4, 6,  2024, 280.00,  1, '2024-07-05', @DEMO_USER),
(@TRAB4, 7,  2024, 400.00,  1, '2024-08-05', @DEMO_USER),
(@TRAB4, 8,  2024, 250.00,  1, '2024-09-05', @DEMO_USER),
(@TRAB4, 9,  2024, 380.00,  1, '2024-10-05', @DEMO_USER),
(@TRAB4, 1,  2025, 350.00,  1, '2025-02-05', @DEMO_USER),
(@TRAB4, 2,  2025, 420.00,  1, '2025-03-05', @DEMO_USER),
(@TRAB4, 3,  2025, 480.00,  1, '2025-04-05', @DEMO_USER),
(@TRAB4, 4,  2025, 350.00,  1, '2025-05-05', @DEMO_USER),
(@TRAB4, 5,  2025, 380.00,  1, '2025-06-05', @DEMO_USER),
(@TRAB4, 6,  2025, 300.00,  1, '2025-07-05', @DEMO_USER),
(@TRAB4, 7,  2025, 420.00,  1, '2025-08-05', @DEMO_USER),
(@TRAB4, 9,  2025, 400.00,  1, '2025-10-05', @DEMO_USER),
(@TRAB4, 2,  2026, 350.00,  1, '2026-03-05', @DEMO_USER),
(@TRAB4, 3,  2026, 320.00,  0, NULL,          @DEMO_USER),

-- Pedro (peón, desde jun 2024)
(@TRAB5, 6,  2024, 420.00,  1, '2024-07-05', @DEMO_USER),
(@TRAB5, 7,  2024, 350.00,  1, '2024-08-05', @DEMO_USER),
(@TRAB5, 8,  2024, 280.00,  1, '2024-09-05', @DEMO_USER),
(@TRAB5, 9,  2024, 300.00,  1, '2024-10-05', @DEMO_USER),
(@TRAB5, 10, 2024, 480.00,  1, '2024-11-05', @DEMO_USER),
(@TRAB5, 11, 2024, 720.00,  1, '2024-12-05', @DEMO_USER),
(@TRAB5, 12, 2024, 580.00,  1, '2025-01-05', @DEMO_USER),
(@TRAB5, 1,  2025, 520.00,  1, '2025-02-05', @DEMO_USER),
(@TRAB5, 2,  2025, 480.00,  1, '2025-03-05', @DEMO_USER),
(@TRAB5, 3,  2025, 380.00,  1, '2025-04-05', @DEMO_USER),
(@TRAB5, 4,  2025, 320.00,  1, '2025-05-05', @DEMO_USER),
(@TRAB5, 5,  2025, 350.00,  1, '2025-06-05', @DEMO_USER),
(@TRAB5, 6,  2025, 400.00,  1, '2025-07-05', @DEMO_USER),
(@TRAB5, 7,  2025, 300.00,  1, '2025-08-05', @DEMO_USER),
(@TRAB5, 8,  2025, 260.00,  1, '2025-09-05', @DEMO_USER),
(@TRAB5, 9,  2025, 280.00,  1, '2025-10-05', @DEMO_USER),
(@TRAB5, 10, 2025, 450.00,  1, '2025-11-05', @DEMO_USER),
(@TRAB5, 11, 2025, 680.00,  1, '2025-12-05', @DEMO_USER),
(@TRAB5, 12, 2025, 600.00,  1, '2026-01-05', @DEMO_USER),
(@TRAB5, 1,  2026, 520.00,  1, '2026-02-05', @DEMO_USER),
(@TRAB5, 2,  2026, 480.00,  1, '2026-03-05', @DEMO_USER),
(@TRAB5, 3,  2026, 420.00,  0, NULL,          @DEMO_USER);


-- =============================================================================
-- FIN DEL SEED
-- =============================================================================
SELECT CONCAT('Seed demo ejecutado correctamente. Usuario ID: ', @DEMO_USER) AS resultado;
SELECT CONCAT('Login: demo@martincarmona.com / Demo2024!') AS credenciales;
