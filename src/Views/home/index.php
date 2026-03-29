<!-- ═══════════════════════════════════════
     SECCIÓN 1: HERO
═══════════════════════════════════════ -->
<section id="hero" class="relative min-h-screen flex flex-col justify-center px-6 lg:px-12 overflow-hidden">

    <!-- Ruido de fondo sutil -->
    <div class="absolute inset-0 bg-[url('/img/noise.svg')] opacity-[0.03] pointer-events-none"></div>

    <!-- Línea decorativa vertical -->
    <div class="absolute left-6 lg:left-12 top-0 bottom-0 w-px bg-gradient-to-b from-transparent via-accent/20 to-transparent"></div>

    <div class="max-w-7xl mx-auto w-full pt-24 pb-16">

        <!-- Etiqueta superior -->
        <p class="hero-label text-light-600 text-sm font-medium tracking-[0.25em] uppercase mb-8 opacity-0">
            Desarrollador · Diseñador · Creador
        </p>

        <!-- Nombre principal -->
        <h1 class="hero-title font-display font-bold leading-none tracking-tight text-fluid-hero text-light-50 mb-8 opacity-0">
            Martín<br>
            <span class="text-accent">Carmona</span>
        </h1>

        <!-- Frase definitoria -->
        <p class="hero-subtitle max-w-xl text-fluid-lg text-light-400 leading-relaxed mb-12 opacity-0">
            Construyo experiencias digitales donde el código,<br class="hidden sm:block">
            el diseño y la imagen trabajan como uno.
        </p>

        <!-- CTAs -->
        <div class="hero-ctas flex flex-wrap items-center gap-5 opacity-0">
            <a href="/#portfolio"
               class="inline-flex items-center gap-2 px-8 py-4 bg-accent text-dark-900 font-bold font-display tracking-wide rounded hover:bg-accent-light transition-all duration-300 group">
                Ver portfolio
                <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
            <a href="/contacto"
               class="inline-flex items-center gap-2 px-8 py-4 border border-light-200/20 text-light-200 font-medium rounded hover:border-accent/50 hover:text-accent transition-all duration-300">
                Hablamos
            </a>
        </div>

        <!-- Scroll indicator -->
        <div class="hero-scroll absolute bottom-10 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 opacity-0">
            <span class="text-light-600 text-xs tracking-widest uppercase">Scroll</span>
            <div class="w-px h-12 bg-gradient-to-b from-light-600 to-transparent animate-pulse"></div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════
     SECCIÓN 2: SOBRE MÍ
═══════════════════════════════════════ -->
<section id="sobre-mi" class="py-32 lg:py-40 px-6 lg:px-12">
    <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 lg:gap-24 items-center">

        <!-- Texto -->
        <div class="reveal-left">
            <p class="text-accent text-sm font-medium tracking-[0.2em] uppercase mb-6">Sobre mí</p>
            <h2 class="font-display font-bold text-fluid-2xl text-light-50 leading-tight mb-8">
                Creo desde Jaén,<br>pienso en digital.
            </h2>
            <div class="space-y-5 text-light-400 text-fluid-base leading-relaxed">
                <p>
                    Soy Martín, 32 años. Diseño y desarrollo webs desde hace más de una década,
                    pero mi visión no se queda en el código: la fotografía, el vídeo y el diseño
                    gráfico forman parte de la misma forma de entender la comunicación.
                </p>
                <p>
                    Me muevo bien entre el <span class="text-light-200">frontend moderno</span>,
                    la <span class="text-light-200">identidad visual</span> y la
                    <span class="text-light-200">narrativa audiovisual</span>. Cuando un proyecto
                    me interesa, me involucro de principio a fin.
                </p>
                <p>
                    Disponible para proyectos freelance y oportunidades laborales.
                </p>
            </div>

            <div class="mt-10 flex flex-wrap gap-3">
                <?php foreach (['PHP', 'JavaScript', 'Tailwind CSS', 'MySQL', 'Figma', 'Premiere Pro', 'Lightroom'] as $skill): ?>
                    <span class="px-4 py-2 bg-dark-700 border border-dark-600 text-light-400 text-sm rounded font-medium">
                        <?= $skill ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Foto -->
        <div class="reveal-right relative">
            <div class="relative aspect-[4/5] rounded-lg overflow-hidden bg-dark-700">
                <!-- Placeholder hasta tener foto real -->
                <div class="absolute inset-0 flex items-center justify-center text-light-600 text-sm tracking-widest uppercase">
                    [ foto próximamente ]
                </div>
                <!-- <img src="/uploads/martin-carmona.jpg" alt="Martín Carmona" class="w-full h-full object-cover"> -->
            </div>
            <!-- Marco decorativo -->
            <div class="absolute -bottom-4 -right-4 w-full h-full border border-accent/20 rounded-lg -z-10"></div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════
     SECCIÓN 3: SERVICIOS
═══════════════════════════════════════ -->
<section id="servicios" class="py-32 lg:py-40 px-6 lg:px-12 bg-dark-800">
    <div class="max-w-7xl mx-auto">

        <div class="reveal-fade text-center mb-20">
            <p class="text-accent text-sm font-medium tracking-[0.2em] uppercase mb-4">Lo que hago</p>
            <h2 class="font-display font-bold text-fluid-2xl text-light-50 leading-tight">
                Servicios
            </h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-px bg-dark-700">
            <?php
            $services = [
                [
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>',
                    'title' => 'Desarrollo Web',
                    'desc'  => 'Webs a medida, rápidas y bien construidas. PHP, JavaScript moderno, arquitecturas limpias.',
                    'items' => ['Webs corporativas', 'Tiendas online', 'Aplicaciones web', 'APIs REST'],
                ],
                [
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>',
                    'title' => 'Diseño Gráfico',
                    'desc'  => 'Identidad visual, cartelería, maquetación editorial y contenido para redes sociales.',
                    'items' => ['Logotipos', 'Cartelería', 'Maquetación', 'RRSS'],
                ],
                [
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>',
                    'title' => 'Fotografía',
                    'desc'  => 'Reportajes de eventos, fotografía de producto, retrato y paisaje con ojo para el detalle.',
                    'items' => ['Eventos', 'Producto', 'Retrato', 'Documental'],
                ],
                [
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>',
                    'title' => 'Vídeo',
                    'desc'  => 'Producción y postproducción: spots publicitarios, contenido corporativo y piezas para redes.',
                    'items' => ['Spots', 'Corporativo', 'Reels/Social', 'Edición'],
                ],
            ];
            foreach ($services as $i => $service):
            ?>
            <div class="service-card reveal-fade bg-dark-800 p-8 lg:p-10 flex flex-col gap-6 group hover:bg-dark-700 transition-colors duration-400 cursor-default"
                 style="--delay: <?= $i * 100 ?>ms">

                <div class="w-12 h-12 flex items-center justify-center border border-dark-600 rounded group-hover:border-accent/50 transition-colors duration-300">
                    <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <?= $service['icon'] ?>
                    </svg>
                </div>

                <div>
                    <h3 class="font-display font-bold text-xl text-light-50 mb-3"><?= $service['title'] ?></h3>
                    <p class="text-light-400 text-sm leading-relaxed"><?= $service['desc'] ?></p>
                </div>

                <ul class="mt-auto space-y-2">
                    <?php foreach ($service['items'] as $item): ?>
                        <li class="flex items-center gap-2 text-light-600 text-sm">
                            <span class="w-1 h-1 bg-accent rounded-full flex-shrink-0"></span>
                            <?= $item ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════
     SECCIÓN 4: PORTFOLIO (preview)
═══════════════════════════════════════ -->
<section id="portfolio" class="py-32 lg:py-40 px-6 lg:px-12">
    <div class="max-w-7xl mx-auto">

        <div class="reveal-fade flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-16">
            <div>
                <p class="text-accent text-sm font-medium tracking-[0.2em] uppercase mb-4">Trabajo selecto</p>
                <h2 class="font-display font-bold text-fluid-2xl text-light-50 leading-tight">Portfolio</h2>
            </div>
            <a href="/portfolio" class="text-light-400 hover:text-accent text-sm font-medium tracking-wide transition-colors duration-300 flex items-center gap-2 group whitespace-nowrap">
                Ver todo el trabajo
                <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>

        <!-- Grid placeholder (se llenará dinámicamente con BD) -->
        <div class="grid grid-cols-portfolio gap-px bg-dark-700" id="portfolio-grid">
            <?php
            // Proyectos de ejemplo hasta conectar la BD
            $placeholders = [
                ['cat' => 'web',      'title' => 'Proyecto Web',       'year' => '2024'],
                ['cat' => 'diseño',   'title' => 'Identidad Visual',   'year' => '2024'],
                ['cat' => 'foto',     'title' => 'Reportaje',          'year' => '2023'],
                ['cat' => 'video',    'title' => 'Spot Publicitario',  'year' => '2024'],
                ['cat' => 'web',      'title' => 'E-commerce',         'year' => '2023'],
                ['cat' => 'diseño',   'title' => 'Cartelería',         'year' => '2024'],
            ];
            foreach ($placeholders as $i => $p):
            ?>
            <article class="portfolio-item reveal-fade bg-dark-800 group cursor-pointer overflow-hidden"
                     style="--delay: <?= $i * 80 ?>ms">
                <div class="relative aspect-[4/3] bg-dark-700 overflow-hidden">
                    <!-- Thumbnail placeholder -->
                    <div class="absolute inset-0 flex items-center justify-center text-dark-600 text-xs tracking-widest uppercase font-medium">
                        <?= $p['cat'] ?>
                    </div>
                    <!-- Overlay hover -->
                    <div class="absolute inset-0 bg-dark-900/60 opacity-0 group-hover:opacity-100 transition-opacity duration-400 flex items-center justify-center">
                        <span class="text-light-50 text-sm font-medium tracking-widest uppercase border border-light-50/20 px-5 py-3">
                            Ver proyecto
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-accent text-xs font-medium tracking-[0.15em] uppercase mb-2"><?= ucfirst($p['cat']) ?></p>
                    <h3 class="font-display font-bold text-light-50 text-lg leading-tight"><?= $p['title'] ?></h3>
                    <p class="text-light-600 text-sm mt-1"><?= $p['year'] ?></p>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════
     SECCIÓN 5: CTA CONTACTO
═══════════════════════════════════════ -->
<section class="py-32 lg:py-40 px-6 lg:px-12 bg-dark-800">
    <div class="max-w-4xl mx-auto text-center reveal-fade">
        <h2 class="font-display font-bold text-fluid-2xl text-light-50 leading-tight mb-6">
            ¿Tienes un proyecto<br>en mente?
        </h2>
        <p class="text-light-400 text-fluid-base leading-relaxed mb-10 max-w-xl mx-auto">
            Cuéntame qué necesitas. Respondo en menos de 24 horas.
        </p>
        <a href="/contacto"
           class="inline-flex items-center gap-3 px-10 py-5 bg-accent text-dark-900 font-display font-bold text-lg rounded hover:bg-accent-light transition-all duration-300 group">
            Ponerse en contacto
            <svg class="w-5 h-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
        </a>
    </div>
</section>
