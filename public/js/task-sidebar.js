/**
 * TaskSidebar — sidebar deslizante unificado para crear y editar tareas.
 * Reutiliza los endpoints y funciones existentes del sistema de tareas.
 */
class TaskSidebar {
    constructor() {
        this.taskId        = null;
        this.debounceTimers = {};
        this.savedFields   = new Set();
        this._opcionesCache = null; // trabajadores, parcelas, trabajos disponibles

        this._bindGlobalKeys();
    }

    // ─── API pública ──────────────────────────────────────────────────────────

    /**
     * Abre el sidebar.
     * @param {number|null} taskId  null → crea tarea vacía primero
     * @param {string|null} fecha   fecha en formato YYYY-MM-DD para asignar a la nueva tarea
     */
    async open(taskId = null, fecha = null) {
        this.savedFields.clear();
        this._opcionesCache = null;
        window.needsReload  = false;

        if (taskId === null) {
            this._showStatus('saving', 'Creando...');
            try {
                const body = fecha ? JSON.stringify({ fecha }) : undefined;
                const res  = await fetch(buildUrl('/tareas/crearVacio'), {
                    method:  'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(fecha ? { 'Content-Type': 'application/json' } : {})
                    },
                    body
                });
                const data = await res.json();
                if (!data.success) {
                    showToast('Error al crear la tarea', 'error');
                    return;
                }
                taskId = data.id;
                window.needsReload = true; // recarga lista al cerrar
            } catch (e) {
                showToast('Error de conexión', 'error');
                return;
            }
        }

        this.taskId = taskId;
        this._show();
        await this._loadAndRender();
    }

    close() {
        this._hide();
        if (window.needsReload) {
            window.needsReload = false;
            if (typeof window.refreshCalendar === 'function') {
                window.refreshCalendar();
            } else {
                window.location.reload();
            }
        }
    }

    // ─── Apertura / cierre DOM ────────────────────────────────────────────────

    _show() {
        document.getElementById('task-sidebar-overlay').classList.add('visible');
        document.getElementById('task-sidebar').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    _hide() {
        document.getElementById('task-sidebar-overlay').classList.remove('visible');
        document.getElementById('task-sidebar').classList.remove('open');
        document.body.style.overflow = '';
        // Limpiar cualquier debounce pendiente
        Object.values(this.debounceTimers).forEach(clearTimeout);
        this.debounceTimers = {};
        // Vaciar contenido para que no se vea la tarea anterior al abrir la siguiente
        const titleEl = document.getElementById('sidebar-title');
        const bodyEl  = document.getElementById('sidebar-body');
        if (titleEl) titleEl.textContent = '';
        if (bodyEl)  bodyEl.innerHTML = '';
    }

    // ─── Carga y renderizado ──────────────────────────────────────────────────

    async _loadAndRender() {
        this._showStatus('saving', 'Cargando...');
        try {
            const res  = await fetch(buildUrl('/tareas/obtener') + '?id=' + this.taskId, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data.success) {
                showToast('Error al cargar la tarea', 'error');
                return;
            }
            this._render(data.tarea);
            this._showStatus('', '');
        } catch (e) {
            showToast('Error de conexión', 'error');
        }
    }

    _render(tarea) {
        // ── Header: título ───────────────────────────────────────────────────
        const titleEl = document.getElementById('sidebar-title');
        titleEl.textContent = tarea.titulo || '';
        titleEl.dataset.id  = tarea.id;

        // ── Bind autoguardado en título ──────────────────────────────────────
        titleEl.oninput = () => this._autoSave('titulo', titleEl.innerText.trim());

        // ── Construir cuerpo ─────────────────────────────────────────────────
        // Limpiar listas combobox portadas al body desde renders anteriores
        document.querySelectorAll('[data-combobox-portal]').forEach(el => el.remove());

        const body = document.getElementById('sidebar-body');
        body.innerHTML = '';

        body.appendChild(this._buildFechaSection(tarea));
        body.appendChild(this._buildHorasSection(tarea));
        body.appendChild(this._buildDescripcionSection(tarea));
        body.appendChild(document.createElement('hr')).className = 'sidebar-divider';

        body.appendChild(this._buildTrabajadoresSection(tarea));
        body.appendChild(this._buildParcelasSection(tarea));
        body.appendChild(this._buildTrabajoSection(tarea));

        body.appendChild(document.createElement('hr')).className = 'sidebar-divider';
        body.appendChild(this._buildImagenesSection(tarea));

        // ── Footer: eliminar ─────────────────────────────────────────────────
        const deleteBtn = document.getElementById('sidebar-delete-btn');
        deleteBtn.onclick = () => this._eliminarTarea();

        // ── Cargar opciones de selects ────────────────────────────────────────
        this._cargarOpciones();
    }

    // ─── Secciones del formulario ─────────────────────────────────────────────

    _buildFechaSection(tarea) {
        const sec = this._createSection('📅 Fecha', 'fecha');
        const input = this._createInput('date', tarea.fecha || '', 'sidebar-fecha');
        input.addEventListener('change', () => {
            this._autoSaveImmediate('fecha', input.value);
        });
        sec.querySelector('.sidebar-section').appendChild(input);
        return sec.querySelector('.sidebar-section');
    }

    _buildHorasSection(tarea) {
        const wrap = this._makeSectionEl('⏱ Horas', 'horas');
        const input = this._createInput('number', tarea.horas || '', 'sidebar-horas');
        input.min  = '0';
        input.step = '0.5';
        input.addEventListener('change', () => {
            this._autoSaveImmediate('horas', input.value);
            this._actualizarCoste();
        });
        wrap.appendChild(input);
        return wrap;
    }

    _buildDescripcionSection(tarea) {
        const wrap = this._makeSectionEl('📝 Descripción', 'descripcion');
        const ta = document.createElement('textarea');
        ta.id          = 'sidebar-desc-textarea';
        ta.className   = 'sidebar-desc-textarea';
        ta.placeholder = 'Descripción detallada (opcional)...';
        ta.rows        = 4;
        ta.value       = tarea.descripcion || '';
        ta.addEventListener('input', () => this._autoSave('descripcion', ta.value));
        wrap.appendChild(ta);
        return wrap;
    }

    _buildTrabajadoresSection(tarea) {
        const wrap  = this._makeSectionEl('👷 Trabajadores', 'trabajadores');
        const tags  = document.createElement('div');
        tags.className = 'sidebar-tags';
        tags.id        = `sidebar-tags-trabajadores-${this.taskId}`;

        (tarea.trabajadores || []).forEach(t => {
            tags.appendChild(this._makeTag(t.nombre, () => this._quitarTrabajador(t.id, tags)));
        });

        const addRow = this._makeAddRow(`cb-sidebar-trab-${this.taskId}`);

        // Botón "Añadir cuadrilla"
        const cuadrillaBtn = document.createElement('button');
        cuadrillaBtn.type      = 'button';
        cuadrillaBtn.className = 'sidebar-cuadrilla-btn';
        cuadrillaBtn.title     = 'Añadir toda la cuadrilla a esta tarea';
        cuadrillaBtn.innerHTML = '👷 Añadir cuadrilla';
        cuadrillaBtn.addEventListener('click', () => this._asignarCuadrilla(tags));

        wrap.appendChild(tags);
        wrap.appendChild(addRow);
        wrap.appendChild(cuadrillaBtn);
        return wrap;
    }

    async _asignarCuadrilla(tagsContainer) {
        try {
            const res  = await fetch(buildUrl('/tareas/asignarCuadrilla'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ tarea_id: this.taskId })
            });
            const data = await res.json();

            if (data.success) {
                showToast(data.message, 'success');
                // Recargar los tags de trabajadores
                await this._reloadTrabajadores(tagsContainer);
                this._markSaved('trabajadores');
                this._actualizarCoste(); // Recalcular con toda la cuadrilla
                window.needsReload = true;
            } else {
                showToast(data.message || 'Error al asignar cuadrilla', 'error');
            }
        } catch (e) {
            showToast('Error de conexión', 'error');
        }
    }

    _buildParcelasSection(tarea) {
        const wrap = this._makeSectionEl('🌿 Parcelas', 'parcelas');
        const tags = document.createElement('div');
        tags.className = 'sidebar-tags';
        tags.id        = `sidebar-tags-parcelas-${this.taskId}`;

        (tarea.parcelas || []).forEach(p => {
            tags.appendChild(this._makeTag(p.nombre, () => this._quitarParcela(p.id, tags)));
        });

        const addRow = this._makeAddRow(`cb-sidebar-parc-${this.taskId}`);

        wrap.appendChild(tags);
        wrap.appendChild(addRow);
        return wrap;
    }

    _buildTrabajoSection(tarea) {
        const wrap = this._makeSectionEl('🔨 Tipo de trabajo', 'trabajo');

        const cbWrap = document.createElement('div');
        cbWrap.className = 'combobox-wrap';
        cbWrap.id = `cb-sidebar-work-${this.taskId}`;

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'combobox-input sidebar-inline-select';
        input.placeholder = '— Sin asignar';
        input.autocomplete = 'off';
        input.spellcheck = false;

        const hiddenVal = document.createElement('input');
        hiddenVal.type = 'hidden';
        hiddenVal.className = 'combobox-val';
        hiddenVal.value = '';

        const list = document.createElement('ul');
        list.className = 'combobox-list';

        cbWrap.appendChild(input);
        cbWrap.appendChild(hiddenVal);
        cbWrap.appendChild(list);

        // Pre-rellenar con el trabajo actual
        const currentTrabajo = tarea.trabajos && tarea.trabajos.length ? tarea.trabajos[0] : null;
        if (currentTrabajo) {
            input.value     = currentTrabajo.nombre;
            hiddenVal.value = currentTrabajo.id;
            cbWrap.dataset.currentTrabajo = currentTrabajo.id;
        }

        // Coste estimado
        const costeEl = document.createElement('div');
        costeEl.id        = `sidebar-coste-${this.taskId}`;
        costeEl.className = 'sidebar-coste-label';

        if (currentTrabajo && currentTrabajo.precio_hora) {
            const horas  = parseFloat(tarea.horas || 0);
            const precio = parseFloat(currentTrabajo.precio_hora);
            if (horas > 0 && precio > 0) {
                costeEl.textContent = `💶 ${precio.toFixed(2)} €/h × ${horas} h = ${(precio * horas).toFixed(2)} €`;
            }
        }

        wrap.appendChild(cbWrap);
        wrap.appendChild(costeEl);
        return wrap;
    }

    _actualizarCoste() {
        const costeEl    = document.getElementById(`sidebar-coste-${this.taskId}`);
        const horasInput = document.getElementById('sidebar-horas');
        if (!costeEl || !horasInput) return;

        const { id: trabajoId } = _getComboboxSel(`cb-sidebar-work-${this.taskId}`);
        const horas = parseFloat(horasInput.value || 0);

        if (!trabajoId || horas <= 0) { costeEl.textContent = ''; return; }

        const trabajos = this._opcionesCache?.trabajos || [];
        const trabajo  = trabajos.find(t => String(t.id) === String(trabajoId));
        const precio   = parseFloat(trabajo?.precio_hora ?? 0);

        // Contar trabajadores asignados (tags visibles en la sección)
        const tagsContainer = document.getElementById(`sidebar-tags-trabajadores-${this.taskId}`);
        const numTrabajadores = tagsContainer ? tagsContainer.querySelectorAll('.sidebar-tag').length : 1;
        const efectivos = Math.max(numTrabajadores, 1); // mínimo 1

        if (precio > 0) {
            const costePorTrabajador = precio * horas;
            const costeTotal = costePorTrabajador * efectivos;
            if (efectivos > 1) {
                costeEl.textContent = `💶 ${precio.toFixed(2)} €/h × ${horas} h × ${efectivos} trab. = ${costeTotal.toFixed(2)} €`;
            } else {
                costeEl.textContent = `💶 ${precio.toFixed(2)} €/h × ${horas} h = ${costeTotal.toFixed(2)} €`;
            }
        } else {
            costeEl.textContent = '';
        }
    }

    _buildImagenesSection(tarea) {
        const wrap   = this._makeSectionEl('🖼 Imágenes', 'imagenes');
        const gallery = document.createElement('div');
        gallery.id    = 'sidebar-images';

        const uploadBtn = document.createElement('label');
        uploadBtn.className = 'btn btn-secondary btn-sm';
        uploadBtn.style.cssText = 'cursor:pointer;display:inline-block;margin-top:8px;';
        uploadBtn.textContent   = '+ Subir imágenes';

        const fileInput = document.createElement('input');
        fileInput.type     = 'file';
        fileInput.multiple = true;
        fileInput.accept   = 'image/*';
        fileInput.style.display = 'none';
        fileInput.addEventListener('change', () => this._subirImagenes(fileInput));
        uploadBtn.appendChild(fileInput);

        wrap.appendChild(gallery);
        wrap.appendChild(uploadBtn);

        // Cargar imágenes existentes via función global si está disponible
        if (typeof loadTaskImages === 'function') {
            setTimeout(() => loadTaskImages(this.taskId, 'sidebar-images', true), 50);
        }

        return wrap;
    }

    // ─── Helpers DOM ──────────────────────────────────────────────────────────

    _makeSectionEl(labelText, fieldKey) {
        const wrap  = document.createElement('div');
        wrap.className = 'sidebar-section';

        const label = document.createElement('div');
        label.className = 'sidebar-section-label';
        label.innerHTML = `<span>${labelText}</span>`;

        const badge = document.createElement('span');
        badge.className = `sidebar-saved-badge`;
        badge.id        = `sidebar-badge-${fieldKey}`;
        badge.textContent = '✓';
        label.appendChild(badge);

        wrap.appendChild(label);
        return wrap;
    }

    // Compat: algunos métodos devuelven wrap, otros el elemento directamente
    _createSection(labelText, fieldKey) {
        const outer = document.createElement('div');
        outer.appendChild(this._makeSectionEl(labelText, fieldKey));
        return outer;
    }

    _createInput(type, value, id) {
        const input = document.createElement('input');
        input.type      = type;
        input.value     = value;
        input.id        = id;
        input.className = 'sidebar-field-input';
        return input;
    }

    _makeTag(nombre, onRemove) {
        const tag = document.createElement('div');
        tag.className   = 'sidebar-tag';
        tag.innerHTML   = `<span>${nombre}</span>`;

        const btn = document.createElement('button');
        btn.type      = 'button';
        btn.className = 'sidebar-tag-remove';
        btn.textContent = '×';
        btn.addEventListener('click', onRemove);
        tag.appendChild(btn);
        return tag;
    }

    _makeAddRow(comboboxId) {
        const wrap = document.createElement('div');
        wrap.className = 'combobox-wrap sidebar-inline-add';
        wrap.id = comboboxId;

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'combobox-input sidebar-inline-select';
        input.placeholder = '— Buscar y seleccionar...';
        input.autocomplete = 'off';
        input.spellcheck = false;

        const hiddenVal = document.createElement('input');
        hiddenVal.type = 'hidden';
        hiddenVal.className = 'combobox-val';
        hiddenVal.value = '';

        const list = document.createElement('ul');
        list.className = 'combobox-list';

        wrap.appendChild(input);
        wrap.appendChild(hiddenVal);
        wrap.appendChild(list);

        return wrap;
    }

    // ─── Carga de opciones (trabajadores, parcelas, trabajos) ─────────────────

    async _cargarOpciones() {
        try {
            if (!this._opcionesCache) {
                const res  = await fetch(buildUrl('/tareas/opcionesModal'), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                this._opcionesCache = await res.json();
            }

            const { trabajadores = [], parcelas = [], trabajos = [] } = this._opcionesCache;

            initCombobox(`cb-sidebar-trab-${this.taskId}`, trabajadores, (opt) => {
                this._agregarTrabajador(opt.id, opt.nombre);
            });
            initCombobox(`cb-sidebar-parc-${this.taskId}`, parcelas, (opt) => {
                this._agregarParcela(opt.id, opt.nombre);
            });
            initCombobox(`cb-sidebar-work-${this.taskId}`, trabajos, (opt) => {
                this._cambiarTrabajo(opt.id, opt.nombre);
                this._actualizarCoste();
            }, (nombre) => {
                this._crearYAsignarTrabajo(nombre);
            });

            // Restaurar nombre del trabajo actual en el input si ya tenía ID pre-cargado
            const cbWork = document.getElementById(`cb-sidebar-work-${this.taskId}`);
            if (cbWork?.dataset.currentTrabajo) {
                const current = cbWork.dataset.currentTrabajo;
                const match   = trabajos.find(t => String(t.id) === String(current));
                const inp     = cbWork.querySelector('.combobox-input');
                if (match && inp && !inp.value) inp.value = match.nombre;
            }

            this._actualizarCoste();
        } catch (e) {
            console.error('Error cargando opciones del sidebar:', e);
        }
    }

    _fillSelect(selectId, items) {
        const sel = document.getElementById(selectId);
        if (!sel) return;
        // Mantener el option vacío inicial
        while (sel.options.length > 1) sel.remove(1);
        items.forEach(item => {
            const opt = document.createElement('option');
            opt.value       = item.id;
            opt.textContent = item.nombre;
            if (item.precio_hora != null) opt.dataset.precio = item.precio_hora;
            sel.appendChild(opt);
        });
    }

    // ─── Auto-guardado de campos de texto ─────────────────────────────────────

    _autoSave(campo, valor) {
        clearTimeout(this.debounceTimers[campo]);
        this._showStatus('saving', 'Guardando...');
        this.debounceTimers[campo] = setTimeout(
            () => this._doSave(campo, valor),
            600
        );
    }

    _autoSaveImmediate(campo, valor) {
        clearTimeout(this.debounceTimers[campo]);
        this._doSave(campo, valor);
    }

    async _doSave(campo, valor) {
        try {
            const res  = await fetch(buildUrl('/tareas/actualizarCampo'), {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ id: this.taskId, campo, valor })
            });
            const data = await res.json();

            if (data.success) {
                this._showStatus('saved', 'Guardado ✓');
                this._markSaved(campo);
                window.needsReload = true;
                // Resetear status tras 3s
                clearTimeout(this._statusTimer);
                this._statusTimer = setTimeout(() => this._showStatus('', ''), 3000);
            } else {
                this._showStatus('error', 'Error al guardar');
                showToast('Error al guardar: ' + (data.message || ''), 'error');
            }
        } catch (e) {
            this._showStatus('error', 'Error de conexión');
            showToast('Error de conexión', 'error');
        }
    }

    _showStatus(cssClass, text) {
        const el = document.getElementById('sidebar-save-status');
        if (!el) return;
        el.className = 'sidebar-save-status' + (cssClass ? ' ' + cssClass : '');
        el.textContent = text;
    }

    _markSaved(campo) {
        const badge = document.getElementById(`sidebar-badge-${campo}`);
        if (!badge) return;
        this.savedFields.add(campo);
        badge.classList.add('visible');
    }

    // ─── Trabajadores ─────────────────────────────────────────────────────────

    async _agregarTrabajador(trabajadorId, trabajadorNombre) {
        const tagsContainer = document.getElementById(`sidebar-tags-trabajadores-${this.taskId}`);
        if (!tagsContainer) return;

        try {
            const res  = await fetch(buildUrl('/tareas/agregarTrabajador'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ tarea_id: this.taskId, trabajador_id: trabajadorId })
            });
            const data = await res.json();

            if (data.success) {
                const tag = this._makeTag(
                    trabajadorNombre,
                    () => this._quitarTrabajador(trabajadorId, tagsContainer)
                );
                tagsContainer.appendChild(tag);
                _resetCombobox(`cb-sidebar-trab-${this.taskId}`);
                this._markSaved('trabajadores');
                this._actualizarCoste(); // Recalcular con nuevo nº de trabajadores
                window.needsReload = true;
            } else {
                showToast(data.message || 'Error al añadir trabajador', 'error');
            }
        } catch (e) {
            showToast('Error de conexión', 'error');
        }
    }

    async _quitarTrabajador(trabajadorId, tagsContainer) {
        try {
            const res  = await fetch(buildUrl('/tareas/quitarTrabajador'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ tarea_id: this.taskId, trabajador_id: trabajadorId })
            });
            const data = await res.json();

            if (data.success) {
                // Encontrar y eliminar el tag correspondiente
                const tags = tagsContainer.querySelectorAll('.sidebar-tag');
                tags.forEach(tag => {
                    // Buscar por botón de quitar que tenga este trabajador
                    tag.querySelector('.sidebar-tag-remove')
                        ?._onclickTrabajadorId === trabajadorId && tag.remove();
                });
                // Fallback: recargar sección
                await this._reloadTrabajadores(tagsContainer);
                this._actualizarCoste(); // Recalcular sin ese trabajador
                window.needsReload = true;
            } else {
                showToast(data.message || 'Error al quitar trabajador', 'error');
            }
        } catch (e) {
            showToast('Error de conexión', 'error');
        }
    }

    async _reloadTrabajadores(tagsContainer) {
        try {
            const res  = await fetch(buildUrl('/tareas/obtener') + '?id=' + this.taskId, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data.success) return;

            tagsContainer.innerHTML = '';
            (data.tarea.trabajadores || []).forEach(t => {
                tagsContainer.appendChild(
                    this._makeTag(t.nombre, () => this._quitarTrabajador(t.id, tagsContainer))
                );
            });
        } catch (e) { /* silencioso */ }
    }

    // ─── Parcelas ─────────────────────────────────────────────────────────────

    async _agregarParcela(parcelaId, parcelaNombre) {
        const tagsContainer = document.getElementById(`sidebar-tags-parcelas-${this.taskId}`);
        if (!tagsContainer) return;

        try {
            const res  = await fetch(buildUrl('/tareas/agregarParcela'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ tarea_id: this.taskId, parcela_id: parcelaId })
            });
            const data = await res.json();

            if (data.success) {
                const tag = this._makeTag(
                    parcelaNombre,
                    () => this._quitarParcela(parcelaId, tagsContainer)
                );
                tagsContainer.appendChild(tag);
                _resetCombobox(`cb-sidebar-parc-${this.taskId}`);
                this._markSaved('parcelas');
                window.needsReload = true;
            } else {
                showToast(data.message || 'Error al añadir parcela', 'error');
            }
        } catch (e) {
            showToast('Error de conexión', 'error');
        }
    }

    async _quitarParcela(parcelaId, tagsContainer) {
        try {
            const res  = await fetch(buildUrl('/tareas/quitarParcela'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ tarea_id: this.taskId, parcela_id: parcelaId })
            });
            const data = await res.json();

            if (data.success) {
                await this._reloadParcelas(tagsContainer);
                window.needsReload = true;
            } else {
                showToast(data.message || 'Error al quitar parcela', 'error');
            }
        } catch (e) {
            showToast('Error de conexión', 'error');
        }
    }

    async _reloadParcelas(tagsContainer) {
        try {
            const res  = await fetch(buildUrl('/tareas/obtener') + '?id=' + this.taskId, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data.success) return;

            tagsContainer.innerHTML = '';
            (data.tarea.parcelas || []).forEach(p => {
                tagsContainer.appendChild(
                    this._makeTag(p.nombre, () => this._quitarParcela(p.id, tagsContainer))
                );
            });
        } catch (e) { /* silencioso */ }
    }

    // ─── Trabajo ──────────────────────────────────────────────────────────────

    async _cambiarTrabajo(trabajoId, trabajoNombre) {
        trabajoId = parseInt(trabajoId) || 0;
        try {
            const res  = await fetch(buildUrl('/tareas/cambiarTrabajo'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ tarea_id: this.taskId, trabajo_id: trabajoId })
            });
            const data = await res.json();

            if (data.success) {
                this._markSaved('trabajo');
                window.needsReload = true;
            } else {
                showToast(data.message || 'Error al cambiar trabajo', 'error');
            }
        } catch (e) {
            showToast('Error de conexión', 'error');
        }
    }

    async _crearYAsignarTrabajo(nombre) {
        try {
            const res  = await fetch(buildUrl('/trabajos/crear'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ nombre })
            });
            const data = await res.json();

            if (!data.success) {
                showToast(data.message || 'Error al crear el trabajo', 'error');
                return;
            }

            const nuevoTrabajo = { id: data.id, nombre, precio_hora: null };

            // Actualizar cache para que el combobox tenga la nueva opción
            if (this._opcionesCache) {
                this._opcionesCache.trabajos = [...(this._opcionesCache.trabajos || []), nuevoTrabajo];
            }

            // Re-inicializar combobox con la lista actualizada y pre-seleccionar el nuevo trabajo
            const cbId = `cb-sidebar-work-${this.taskId}`;
            initCombobox(cbId, this._opcionesCache.trabajos, (opt) => {
                this._cambiarTrabajo(opt.id, opt.nombre);
                this._actualizarCoste();
            }, (n) => {
                this._crearYAsignarTrabajo(n);
            });

            // Pre-seleccionar en el input
            const cbWrap = document.getElementById(cbId);
            if (cbWrap) {
                const inp = cbWrap.querySelector('.combobox-input');
                const val = cbWrap.querySelector('.combobox-val');
                if (inp) inp.value = nombre;
                if (val) val.value = data.id;
            }

            await this._cambiarTrabajo(data.id, nombre);
            this._actualizarCoste();
            showToast(`Trabajo "${nombre}" creado y asignado`, 'success');
        } catch (e) {
            showToast('Error de conexión', 'error');
        }
    }

    // ─── Eliminar tarea ───────────────────────────────────────────────────────

    async _eliminarTarea() {
        if (!confirm('¿Eliminar esta tarea? Esta acción no se puede deshacer.')) return;

        try {
            const res  = await fetch(buildUrl('/tareas/eliminar'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ id: this.taskId })
            });
            const data = await res.json();

            if (data.success) {
                window.needsReload = true;
                this.close();
            } else {
                showToast(data.message || 'Error al eliminar', 'error');
            }
        } catch (e) {
            showToast('Error de conexión', 'error');
        }
    }

    // ─── Imágenes ─────────────────────────────────────────────────────────────

    async _subirImagenes(fileInput) {
        const files = fileInput.files;
        if (!files.length) return;

        const formData = new FormData();
        formData.append('tarea_id', this.taskId);
        Array.from(files).forEach(f => formData.append('imagenes[]', f));

        try {
            const res  = await fetch(buildUrl('/tareas/subirImagen'), {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                fileInput.value = '';
                window.needsReload = true;
                // Recargar galería
                if (typeof loadTaskImages === 'function') {
                    loadTaskImages(this.taskId, 'sidebar-images', true);
                }
            } else {
                showToast(data.message || 'Error al subir imágenes', 'error');
            }
        } catch (e) {
            showToast('Error de conexión', 'error');
        }
    }

    // ─── Atajos de teclado ────────────────────────────────────────────────────

    _bindGlobalKeys() {
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && document.getElementById('task-sidebar')?.classList.contains('open')) {
                this.close();
            }
        });
    }
}

// Instancia global disponible en todas las páginas
window.taskSidebar = new TaskSidebar();

// =====================================================================
// COMBOBOX — typeahead reutilizable
// =====================================================================

/**
 * Inicializa un combobox de typeahead dentro de un wrapper con la estructura:
 *   <div id="wrapperId">
 *     <input class="combobox-input">
 *     <input type="hidden" class="combobox-val">
 *     <ul class="combobox-list"></ul>
 *   </div>
 * @param {string}   wrapperId - ID del div contenedor
 * @param {Array}    options   - Array de {id, nombre}
 * @param {Function} [onSelect] - Callback opcional al seleccionar un item
 */
function initCombobox(wrapperId, options, onSelect = null, onCreate = null) {
    const wrap = document.getElementById(wrapperId);
    if (!wrap) return;

    const input     = wrap.querySelector('.combobox-input');
    const hiddenVal = wrap.querySelector('.combobox-val');
    const list      = wrap.querySelector('.combobox-list');
    if (!input || !hiddenVal || !list) return;

    // Portal: mover la lista al <body> para escapar del overflow y transform del sidebar
    list.dataset.comboboxPortal = wrapperId;
    document.body.appendChild(list);
    list.style.display   = 'none';
    list.style.position  = 'fixed';
    list.style.zIndex    = '99999';

    function positionList() {
        const rect = input.getBoundingClientRect();
        list.style.top   = rect.bottom + 'px';
        list.style.left  = rect.left + 'px';
        list.style.width = rect.width + 'px';
    }

    function renderList(filter) {
        const q = (filter || '').toLowerCase().trim();
        list.innerHTML = '';
        const filtered = q
            ? options.filter(o => o.nombre.toLowerCase().includes(q))
            : options;

        filtered.forEach(opt => {
            const li = document.createElement('li');
            li.className = 'combobox-item';
            li.dataset.id = opt.id;
            li.dataset.nombre = opt.nombre;

            if (q) {
                const idx = opt.nombre.toLowerCase().indexOf(q);
                li.innerHTML = idx >= 0
                    ? opt.nombre.substring(0, idx)
                      + '<mark>' + opt.nombre.substring(idx, idx + q.length) + '</mark>'
                      + opt.nombre.substring(idx + q.length)
                    : opt.nombre;
            } else {
                li.textContent = opt.nombre;
            }

            li.addEventListener('mousedown', (e) => {
                e.preventDefault();
                selectItem(opt);
            });
            list.appendChild(li);
        });

        // Opción "Crear" si hay texto y no hay coincidencia exacta
        if (onCreate && q) {
            const exactMatch = options.some(o => o.nombre.toLowerCase() === q);
            if (!exactMatch) {
                const li = document.createElement('li');
                li.className = 'combobox-create-item';
                li.innerHTML = `+ Crear "<strong>${filter.trim()}</strong>"`;
                li.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    closeList();
                    onCreate(filter.trim());
                });
                list.appendChild(li);
            }
        }

        if (!list.children.length) {
            const li = document.createElement('li');
            li.className = 'combobox-no-results';
            li.textContent = 'Sin resultados';
            list.appendChild(li);
        }
    }

    function selectItem(opt) {
        input.value     = opt.nombre;
        hiddenVal.value = opt.id;
        closeList();
        if (onSelect) onSelect(opt);
    }

    function openList() {
        renderList(input.value);
        positionList();
        list.style.display = '';
    }

    function closeList() {
        list.style.display = 'none';
    }

    input.addEventListener('focus', openList);
    input.addEventListener('input', () => {
        hiddenVal.value = '';
        renderList(input.value);
        positionList();
        list.style.display = '';
    });
    input.addEventListener('blur', () => setTimeout(closeList, 150));

    input.addEventListener('keydown', (e) => {
        const items = list.querySelectorAll('.combobox-item');
        const active = list.querySelector('.combobox-item.cb-active');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (!active) {
                items[0]?.classList.add('cb-active');
            } else {
                const next = active.nextElementSibling;
                if (next?.classList.contains('combobox-item')) {
                    active.classList.remove('cb-active');
                    next.classList.add('cb-active');
                }
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (active) {
                const prev = active.previousElementSibling;
                active.classList.remove('cb-active');
                if (prev?.classList.contains('combobox-item')) prev.classList.add('cb-active');
            }
        } else if (e.key === 'Enter') {
            const act = list.querySelector('.combobox-item.cb-active');
            if (act) {
                e.preventDefault();
                selectItem({ id: act.dataset.id, nombre: act.dataset.nombre });
            }
        } else if (e.key === 'Escape') {
            closeList();
        }
    });
}

/** Devuelve {id, text} del combobox indicado */
function _getComboboxSel(wrapperId) {
    const wrap = document.getElementById(wrapperId);
    if (!wrap) return { id: '', text: '' };
    return {
        id:   parseInt(wrap.querySelector('.combobox-val')?.value || '0') || 0,
        text: wrap.querySelector('.combobox-input')?.value ?? ''
    };
}

/** Limpia el input y el valor oculto del combobox */
function _resetCombobox(wrapperId) {
    const wrap = document.getElementById(wrapperId);
    if (!wrap) return;
    const inp = wrap.querySelector('.combobox-input');
    const val = wrap.querySelector('.combobox-val');
    if (inp) inp.value = '';
    if (val) val.value = '';
}
