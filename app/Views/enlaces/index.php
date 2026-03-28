<?php $title = 'Enlaces de interés'; ?>

<style>
.enlaces-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
    margin-top: 8px;
}

.enlaces-card {
    background: #2a2a2a;
    border: 1px solid #444;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    display: flex;
    flex-direction: column;
}

.enlaces-card-header {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: #fff;
    padding: 11px 16px;
    font-weight: 600;
    font-size: .9rem;
    letter-spacing: .02em;
    display: flex;
    align-items: center;
    gap: 8px;
}

.enlaces-list {
    list-style: none;
    margin: 0;
    padding: 4px 0;
    flex: 1;
}

.enlaces-list li {
    border-bottom: 1px solid #333;
}

.enlaces-list li:last-child {
    border-bottom: none;
}

.enlaces-list a {
    display: flex;
    flex-direction: column;
    padding: 9px 16px;
    color: #ccc;
    text-decoration: none;
    transition: background .15s;
    gap: 1px;
}

.enlaces-list a:hover {
    background: #333;
    color: #fff;
}

.enlace-nombre {
    font-weight: 600;
    font-size: .875rem;
    color: #90caf9;
}

.enlaces-list a:hover .enlace-nombre {
    color: #bbdefb;
}

.enlace-desc {
    font-size: .78rem;
    color: #888;
    line-height: 1.3;
}

.enlace-url {
    font-size: .72rem;
    color: #555;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    margin-top: 1px;
}

/* Badge para enlaces destacados */
.enlace-badge {
    display: inline-block;
    background: #1a6b3a;
    color: #a5d6a7;
    font-size: .68rem;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 8px;
    margin-left: 6px;
    vertical-align: middle;
    letter-spacing: .04em;
}
</style>

<div class="container">
    <div class="page-header">
        <h1><?= emoji('link', '1.2rem') ?> Enlaces de interés</h1>
        <div class="header-actions">
            <a href="<?= $this->url('/dashboard') ?>" class="btn btn-secondary">← Volver</a>
        </div>
    </div>

    <div class="enlaces-grid">

        <!-- SIGPAC y Catastro -->
        <div class="enlaces-card">
            <div class="enlaces-card-header">🗺️ SIGPAC y Catastro</div>
            <ul class="enlaces-list">
                <li>
                    <a href="https://sigpac.mapa.es/fega/visor/" target="_blank" rel="noopener">
                        <span class="enlace-nombre">SIGPAC — Visor nacional</span>
                        <span class="enlace-desc">Localización e identificación de parcelas agrícolas en España</span>
                        <span class="enlace-url">sigpac.mapa.es/fega/visor</span>
                    </a>
                </li>
                <li>
                    <a href="https://www.juntadeandalucia.es/agriculturaypesca/sigpac/index.xhtml" target="_blank" rel="noopener">
                        <span class="enlace-nombre">SIGPAC Andalucía — Informes <span class="enlace-badge">Junta</span></span>
                        <span class="enlace-desc">Generación de informes de parcelas en Andalucía</span>
                        <span class="enlace-url">juntadeandalucia.es — SIGPAC</span>
                    </a>
                </li>
                <li>
                    <a href="https://www.sedecatastro.gob.es/" target="_blank" rel="noopener">
                        <span class="enlace-nombre">Sede Electrónica del Catastro</span>
                        <span class="enlace-desc">Consulta de referencias catastrales, titularidad y valor</span>
                        <span class="enlace-url">sedecatastro.gob.es</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Explotación y Documentación -->
        <div class="enlaces-card">
            <div class="enlaces-card-header"><?= emoji('clipboard') ?> Explotación y Documentación</div>
            <ul class="enlaces-list">
                <li>
                    <a href="https://www.canva.com/design/DAGiWj92eLc/BJKWAyfSiL45JpJ_EVrgCQ/view?utm_content=DAGiWj92eLc&utm_campaign=designshare&utm_medium=link&utm_source=viewer" target="_blank" rel="noopener">
                        <span class="enlace-nombre">Registro de Explotación Agraria <span class="enlace-badge">Canva</span></span>
                        <span class="enlace-desc">Plantilla del registro oficial de la explotación</span>
                        <span class="enlace-url">canva.com — diseño privado</span>
                    </a>
                </li>
                <li>
                    <a href="https://drive.google.com/file/d/1KM-hhTrzayasU8pY8ixvCOyJI1xFFCS9/view?usp=sharing" target="_blank" rel="noopener">
                        <span class="enlace-nombre">Calendario de referencia <span class="enlace-badge">Drive</span></span>
                        <span class="enlace-desc">Calendario de labores y fechas clave del olivar</span>
                        <span class="enlace-url">drive.google.com — archivo compartido</span>
                    </a>
                </li>
                <li>
                    <a href="https://www.juntadeandalucia.es/organismos/agriculturapescaaguaydesarrollorural/servicios/procedimientos/detalle/25659.html" target="_blank" rel="noopener">
                        <span class="enlace-nombre">Subvenciones agrarias — Junta <span class="enlace-badge">Junta</span></span>
                        <span class="enlace-desc">Procedimiento de solicitud de ayudas agrarias en Andalucía</span>
                        <span class="enlace-url">juntadeandalucia.es — procedimiento 25659</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Meteorología -->
        <div class="enlaces-card">
            <div class="enlaces-card-header">🌤️ Meteorología y Clima</div>
            <ul class="enlaces-list">
                <li>
                    <a href="https://app.weathercloud.net/d5826756735#current" target="_blank" rel="noopener">
                        <span class="enlace-nombre">Weathercloud — Estación local <span class="enlace-badge">Local</span></span>
                        <span class="enlace-desc">Datos en tiempo real de la estación meteorológica propia</span>
                        <span class="enlace-url">app.weathercloud.net</span>
                    </a>
                </li>
                <li>
                    <a href="https://www.juntadeandalucia.es/agriculturaypesca/ifapa/riaweb/web/estacion/23/16" target="_blank" rel="noopener">
                        <span class="enlace-nombre">IFAPA — Estación temperatura/precipitaciones <span class="enlace-badge">Junta</span></span>
                        <span class="enlace-desc">Histórico de temperatura y precipitaciones de la estación local IFAPA</span>
                        <span class="enlace-url">juntadeandalucia.es — IFAPA estación 23/16</span>
                    </a>
                </li>
                <li>
                    <a href="https://www.mapaclima.es/34012323007?variable=heat_waves&year=2021-2050" target="_blank" rel="noopener">
                        <span class="enlace-nombre">Mapaclima — Proyección climática</span>
                        <span class="enlace-desc">Análisis de olas de calor y cambio climático 2021–2050</span>
                        <span class="enlace-url">mapaclima.es</span>
                    </a>
                </li>
                <li>
                    <a href="https://www.aemet.es/es/eltiempo/prediccion/municipios" target="_blank" rel="noopener">
                        <span class="enlace-nombre">AEMET — Predicción por municipio</span>
                        <span class="enlace-desc">Previsión del tiempo a 7 días por localidad</span>
                        <span class="enlace-url">aemet.es</span>
                    </a>
                </li>
                <li>
                    <a href="https://www.windy.com/" target="_blank" rel="noopener">
                        <span class="enlace-nombre">Windy</span>
                        <span class="enlace-desc">Mapa interactivo de viento, lluvia y temperatura</span>
                        <span class="enlace-url">windy.com</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Plagas del Olivar -->
        <div class="enlaces-card">
            <div class="enlaces-card-header">🌿 Plagas y Fitosanitarios del Olivar</div>
            <ul class="enlaces-list">
                <li>
                    <a href="https://www.juntadeandalucia.es/agriculturapescaaguaydesarrollorural/raif/category/olivar/" target="_blank" rel="noopener">
                        <span class="enlace-nombre">RAIF — Gestión integrada olivar <span class="enlace-badge">Junta</span></span>
                        <span class="enlace-desc">Red de Alerta e Información Fitosanitaria del olivar andaluz</span>
                        <span class="enlace-url">juntadeandalucia.es — RAIF olivar</span>
                    </a>
                </li>
                <li>
                    <a href="https://www.juntadeandalucia.es/agriculturaypesca/ifapa/servifapa/buscador?sort_by=field_fecha_publicacion&f%5B0%5D=ambito%3AOlivar&fulltext=" target="_blank" rel="noopener">
                        <span class="enlace-nombre">Servifapa — Buscador olivar <span class="enlace-badge">IFAPA</span></span>
                        <span class="enlace-desc">Publicaciones técnicas y boletines de sanidad vegetal del olivar</span>
                        <span class="enlace-url">juntadeandalucia.es — Servifapa olivar</span>
                    </a>
                </li>
                <li>
                    <a href="https://www.mapa.gob.es/es/agricultura/temas/sanidad-vegetal/productos-fitosanitarios/registro-productos/" target="_blank" rel="noopener">
                        <span class="enlace-nombre">Registro fitosanitarios — MAPA</span>
                        <span class="enlace-desc">Productos fitosanitarios autorizados en España</span>
                        <span class="enlace-url">mapa.gob.es — registro fitosanitarios</span>
                    </a>
                </li>
                <li>
                    <a href="https://ws142.juntadeandalucia.es/agriculturaypesca/sirma/" target="_blank" rel="noopener">
                        <span class="enlace-nombre">SIRMA <span class="enlace-badge">Junta</span></span>
                        <span class="enlace-desc">Sistema de información y registro de materias activas fitosanitarias</span>
                        <span class="enlace-url">juntadeandalucia.es — SIRMA</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Riego -->
        <div class="enlaces-card">
            <div class="enlaces-card-header">💧 Programación de Riego</div>
            <ul class="enlaces-list">
                <li>
                    <a href="https://www.juntadeandalucia.es/agriculturaypesca/ifapa/servifapa/recomendador-olivar/?check_logged_in=1" target="_blank" rel="noopener">
                        <span class="enlace-nombre">Servifapa — Recomendador riego olivar <span class="enlace-badge">IFAPA</span></span>
                        <span class="enlace-desc">Programación del riego y la fertilización del olivar según datos climáticos</span>
                        <span class="enlace-url">juntadeandalucia.es — Servifapa recomendador</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Subvenciones y Hacienda -->
        <div class="enlaces-card">
            <div class="enlaces-card-header"><?= emoji('euro') ?> PAC, Subvenciones y Hacienda</div>
            <ul class="enlaces-list">
                <li>
                    <a href="https://www.juntadeandalucia.es/haciendayadministracionpublica/apl/tesoreria/inicio" target="_blank" rel="noopener">
                        <span class="enlace-nombre">Tesorería Junta de Andalucía <span class="enlace-badge">Junta</span></span>
                        <span class="enlace-desc">Gestión de pagos, subvenciones y cuenta corriente con la Junta</span>
                        <span class="enlace-url">juntadeandalucia.es — Tesorería</span>
                    </a>
                </li>
                <li>
                    <a href="https://www.mapa.gob.es/es/pac/default.aspx" target="_blank" rel="noopener">
                        <span class="enlace-nombre">PAC — MAPA</span>
                        <span class="enlace-desc">Política Agraria Común: información, ayudas y normativa</span>
                        <span class="enlace-url">mapa.gob.es/es/pac</span>
                    </a>
                </li>
                <li>
                    <a href="https://www.fega.gob.es/" target="_blank" rel="noopener">
                        <span class="enlace-nombre">FEGA</span>
                        <span class="enlace-desc">Fondo Español de Garantía Agraria — pagos directos y OCM</span>
                        <span class="enlace-url">fega.gob.es</span>
                    </a>
                </li>
                <li>
                    <a href="https://www.agenciatributaria.es/" target="_blank" rel="noopener">
                        <span class="enlace-nombre">Agencia Tributaria</span>
                        <span class="enlace-desc">Declaraciones, módulos y obligaciones fiscales agrarias</span>
                        <span class="enlace-url">agenciatributaria.es</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Vehículos -->
        <div class="enlaces-card">
            <div class="enlaces-card-header"><?= emoji('car') ?> Vehículos y Maquinaria</div>
            <ul class="enlaces-list">
                <li>
                    <a href="https://www.veiasa.es/itv/estaciones-itv/estaciones-moviles/-/categories/44165" target="_blank" rel="noopener">
                        <span class="enlace-nombre">ITV Móvil — VEIASA</span>
                        <span class="enlace-desc">Localización de estaciones ITV móviles en Andalucía</span>
                        <span class="enlace-url">veiasa.es — estaciones móviles</span>
                    </a>
                </li>
                <li>
                    <a href="https://www.juntadeandalucia.es/organismos/atrian/areas/valoracion-bienes/medios-transporte.html" target="_blank" rel="noopener">
                        <span class="enlace-nombre">Valoración transmisión de automóviles <span class="enlace-badge">Junta</span></span>
                        <span class="enlace-desc">Tablas oficiales de valoración para transmisiones patrimoniales</span>
                        <span class="enlace-url">juntadeandalucia.es — ATRIAN valoración</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Laboral y REASS -->
        <div class="enlaces-card">
            <div class="enlaces-card-header"><?= emoji('worker') ?> Laboral y REASS</div>
            <ul class="enlaces-list">
                <li>
                    <a href="https://sede.seg-social.gob.es/" target="_blank" rel="noopener">
                        <span class="enlace-nombre">Sede Seguridad Social</span>
                        <span class="enlace-desc">REASS, cotizaciones, altas y bajas de trabajadores</span>
                        <span class="enlace-url">sede.seg-social.gob.es</span>
                    </a>
                </li>
                <li>
                    <a href="https://www.sepe.es/" target="_blank" rel="noopener">
                        <span class="enlace-nombre">SEPE</span>
                        <span class="enlace-desc">Servicio Público de Empleo Estatal — contratos y desempleo</span>
                        <span class="enlace-url">sepe.es</span>
                    </a>
                </li>
                <li>
                    <a href="https://contrata.sepe.es/" target="_blank" rel="noopener">
                        <span class="enlace-nombre">Contrat@</span>
                        <span class="enlace-desc">Comunicación de contratos de trabajo al SEPE</span>
                        <span class="enlace-url">contrata.sepe.es</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Tecnología Agrícola -->
        <div class="enlaces-card">
            <div class="enlaces-card-header">💡 Ideas y Tecnología Agrícola</div>
            <ul class="enlaces-list">
                <li>
                    <a href="https://www.oasisanalitica.com/" target="_blank" rel="noopener">
                        <span class="enlace-nombre">Oasis Analítica</span>
                        <span class="enlace-desc">Soluciones de análisis de datos y sensórica para agricultura</span>
                        <span class="enlace-url">oasisanalitica.com</span>
                    </a>
                </li>
                <li>
                    <a href="https://es.carbonrobotics.com/carbon-atk" target="_blank" rel="noopener">
                        <span class="enlace-nombre">Carbon Robotics — ATK</span>
                        <span class="enlace-desc">Robots láser para eliminación de malas hierbas sin herbicidas</span>
                        <span class="enlace-url">es.carbonrobotics.com</span>
                    </a>
                </li>
                <li>
                    <a href="https://www.kaampo.com/home/software-agricola" target="_blank" rel="noopener">
                        <span class="enlace-nombre">Kaampo — Software agrícola</span>
                        <span class="enlace-desc">Plataforma de gestión agrícola digital: cuaderno, tareas y más</span>
                        <span class="enlace-url">kaampo.com</span>
                    </a>
                </li>
            </ul>
        </div>

    </div>
</div>
