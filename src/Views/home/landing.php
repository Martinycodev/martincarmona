<?php
/**
 * Landing principal de Martín Carmona
 * -----------------------------------
 * Vista pensada para el portfolio personal. Tono según narrativa.md:
 * cercano, directo, minimalista. NO se venden servicios y NO hay
 * CTAs comerciales — solo una muestra de trabajo.
 *
 * Los proyectos provienen directamente de la tabla de proyectos
 * clave en narrativa.md (las prioridades ⭐ y Alta).
 */

// Proyectos seleccionados de la narrativa (ordenados por relevancia).
// Mantenidos aquí en lugar de en BD porque la web aún es estática y
// así es trivial editar copy sin tocar la base de datos.
$projects = [
    [
        'cat'      => 'Diseño',
        'title'    => 'Con la poli en los tacones',
        'detail'   => 'Cartel para obra de teatro musical · TVE La 1',
        'year'     => '2019',
        'star'     => true,
        'featured' => true,
        'tag'      => 'Cartel',
    ],
    [
        'cat'      => 'Desarrollo',
        'title'    => 'MiOlivar.es',
        'detail'   => 'Sistema de gestión agrícola completo',
        'year'     => '2025',
        'star'     => true,
        'featured' => true,
        'tag'      => 'Producto',
    ],
    [
        'cat'      => 'Desarrollo',
        'title'    => 'MiBarDeJaén',
        'detail'   => 'Web de cartas QR para bares',
        'year'     => '2022',
        'star'     => false,
        'featured' => false,
        'tag'      => 'Web',
    ],
    [
        'cat'      => 'Desarrollo',
        'title'    => 'ActivArjonilla',
        'detail'   => 'Web para dar a conocer negocios locales',
        'year'     => '2023',
        'star'     => false,
        'featured' => false,
        'tag'      => 'Web',
    ],
    [
        'cat'      => 'Diseño',
        'title'    => 'Cartelería de teatro',
        'detail'   => 'Carteles y dossieres para compañías',
        'year'     => '2018 — hoy',
        'star'     => false,
        'featured' => false,
        'tag'      => 'Cartelería',
    ],
    [
        'cat'      => 'Fotografía',
        'title'    => 'Boda de María y Eloy',
        'detail'   => 'Reportaje completo de boda',
        'year'     => '2021',
        'star'     => false,
        'featured' => false,
        'tag'      => 'Reportaje',
    ],
    [
        'cat'      => 'Fotografía',
        'title'    => 'Sesión con Ico',
        'detail'   => 'Contenido para Instagram · influencer',
        'year'     => '2020',
        'star'     => false,
        'featured' => false,
        'tag'      => 'Sesión',
    ],
    [
        'cat'      => 'Vídeo',
        'title'    => 'Aquiles Box',
        'detail'   => 'Producción audiovisual para marca',
        'year'     => '2019',
        'star'     => false,
        'featured' => false,
        'tag'      => 'Vídeo',
    ],
    [
        'cat'      => 'Diseño',
        'title'    => 'Portada y maquetación de libro',
        'detail'   => 'Diseño editorial completo',
        'year'     => '2017',
        'star'     => false,
        'featured' => false,
        'tag'      => 'Editorial',
    ],
];

// Disciplinas (basadas en narrativa: las cuatro patas del recorrido)
$disciplines = [
    [
        'num'  => '01',
        'name' => 'Diseño',
        'desc' => 'Empecé aquí. Cartelería, identidad y maquetación. Uno de mis carteles acabó en el Telediario de La 1.',
    ],
    [
        'num'  => '02',
        'name' => 'Fotografía',
        'desc' => 'Bodas, comuniones, books de actores, sesiones con creadoras de contenido. Aprendí a trabajar bajo presión.',
    ],
    [
        'num'  => '03',
        'name' => 'Vídeo',
        'desc' => 'Me enganchó por el ritmo y la narrativa. Producción, dirección y montaje para marcas y compañías de teatro.',
    ],
    [
        'num'  => '04',
        'name' => 'Desarrollo',
        'desc' => 'La etapa actual. Webs, herramientas y un sistema completo para gestionar mi propia explotación de olivar.',
    ],
];
?>

<!-- ════════════════════════════════════════════════════
     HERO
═══════════════════════════════════════════════════════ -->
<section class="hero">
    <div class="container">
        <p class="eyebrow hero__label">Diseñador · Fotógrafo · Videógrafo · Desarrollador</p>

        <h1 class="display hero__title">
            Una década<br>
            convirtiendo ideas<br>
            en <em>cosas reales</em>.
        </h1>

        <p class="hero__subtitle">
            Soy Martín. Diseño, fotografío, grabo y desarrollo desde Jaén.
            Lo que haga falta para que una idea funcione.
        </p>

        <div class="hero__meta">
            <span>Desde 2014</span>
            <span>Jaén, España</span>
            <span>Multidisciplinar</span>
        </div>
    </div>

    <div class="hero__scroll" aria-hidden="true">Scroll</div>
</section>


<!-- ════════════════════════════════════════════════════
     SOBRE MÍ
═══════════════════════════════════════════════════════ -->
<section class="section section--soft" id="sobre-mi">
    <div class="container">
        <div class="about">

            <!-- Retrato del personaje (recortado vía CSS desde el sheet) -->
            <div class="about__portrait" data-reveal>
                <img src="/personaje.png"
                     alt="Ilustración tipo Disney de Martín Carmona — músico urbano con sudadera negra y cascos verdes">
                <div class="about__portrait-mark">
                    <span>Martín, 32</span>
                    <span>Jaén · 2026</span>
                </div>
            </div>

            <!-- Texto biográfico -->
            <div class="about__text" data-reveal>
                <p class="eyebrow" style="margin-bottom: 1.5rem;">Sobre mí</p>

                <p>
                    Empecé estudiando Gráfica Publicitaria. De ahí salté a la
                    fotografía, después al vídeo, y cuando me cansé de la imagen
                    me metí de lleno en el desarrollo web.
                </p>

                <p>
                    He <strong>diseñado carteles</strong> que han salido en La 1,
                    he <strong>fotografiado bodas</strong>, he <strong>grabado vídeos</strong>
                    para marcas y compañías de teatro, y he <strong>construido webs</strong>
                    de las que hoy uso a diario — incluyendo un sistema completo para
                    gestionar mi propia explotación de olivar.
                </p>

                <p>
                    Hoy trabajo en el campo, pero sigo construyendo cosas. Los últimos
                    dos años la IA me ha cambiado las reglas del juego: lo que antes se
                    frustraba por un problema técnico que no sabía resolver, ahora
                    sale adelante.
                </p>

                <p style="font-style: italic; color: var(--brown);">
                    Cada disciplina me ha dejado algo. Todas se cruzan en lo que hago hoy.
                </p>

                <div class="about__signature">
                    <span class="chip">Diseño</span>
                    <span class="chip">Fotografía</span>
                    <span class="chip">Vídeo</span>
                    <span class="chip">Desarrollo web</span>
                    <span class="chip">IA aplicada</span>
                    <span class="chip">Agricultura</span>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════════════════════
     TRAYECTORIA — Las disciplinas que han marcado el camino
═══════════════════════════════════════════════════════ -->
<section class="section" id="trayectoria">
    <div class="container">
        <div class="section__head" data-reveal>
            <p class="eyebrow">Trayectoria</p>
            <h2 class="display section__title">
                Cuatro disciplinas,<br>una misma forma de mirar.
            </h2>
        </div>

        <div class="disciplines" data-reveal>
            <?php foreach ($disciplines as $d): ?>
                <div class="discipline">
                    <span class="discipline__num"><?= $d['num'] ?></span>
                    <h3 class="discipline__name"><?= $d['name'] ?></h3>
                    <p class="discipline__desc"><?= $d['desc'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════════════════════
     TRABAJO — Muestra (sin CTA)
═══════════════════════════════════════════════════════ -->
<section class="section section--soft" id="trabajo">
    <div class="container">
        <div class="section__head" data-reveal>
            <p class="eyebrow">Trabajo selecto</p>
            <h2 class="display section__title">
                Una muestra<br>de proyectos reales.
            </h2>
        </div>

        <div class="work">
            <?php foreach ($projects as $i => $p): ?>
                <article class="work-card <?= $p['featured'] ? 'work-card--featured' : '' ?>"
                         data-reveal
                         style="transition-delay: <?= min($i * 60, 480) ?>ms;">

                    <div class="work-card__media <?= $p['star'] ? 'work-card__media--star' : '' ?>">
                        <span class="work-card__media-tag"><?= htmlspecialchars($p['tag']) ?></span>
                    </div>

                    <div class="work-card__body">
                        <p class="work-card__cat"><?= htmlspecialchars($p['cat']) ?></p>
                        <h3 class="work-card__title"><?= htmlspecialchars($p['title']) ?></h3>
                        <p class="work-card__meta">
                            <?= htmlspecialchars($p['detail']) ?> · <?= htmlspecialchars($p['year']) ?>
                        </p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
