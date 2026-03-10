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

        const addRow = this._makeAddRow(
            `sidebar-sel-trabajador-${this.taskId}`,
            () => this._agregarTrabajador(tags)
        );

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

        const addRow = this._makeAddRow(
            `sidebar-sel-parcela-${this.taskId}`,
            () => this._agregarParcela(tags)
        );

        wrap.appendChild(tags);
        wrap.appendChild(addRow);
        return wrap;
    }

    _buildTrabajoSection(tarea) {
        const wrap = this._makeSectionEl('🔨 Tipo de trabajo', 'trabajo');
        const sel  = document.createElement('select');
        sel.className = 'sidebar-inline-select';
        sel.id        = `sidebar-sel-trabajo-${this.taskId}`;

        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = '— Sin asignar';
        sel.appendChild(opt);

        // Las opciones se rellenan en _cargarOpciones()
        const currentTrabajo = tarea.trabajos && tarea.trabajos.length ? tarea.trabajos[0] : null;
        sel.dataset.currentTrabajo = currentTrabajo?.id ?? '';

        sel.addEventListener('change', () => {
            this._cambiarTrabajo(sel);
            this._actualizarCoste();
        });

        // Coste estimado usando el snapshot de precio guardado en la tarea
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

        wrap.appendChild(sel);
        wrap.appendChild(costeEl);
        return wrap;
    }

    _actualizarCoste() {
        const costeEl    = document.getElementById(`sidebar-coste-${this.taskId}`);
        const sel        = document.getElementById(`sidebar-sel-trabajo-${this.taskId}`);
        const horasInput = document.getElementById('sidebar-horas');
        if (!costeEl || !sel || !horasInput) return;

        const selectedOpt = sel.options[sel.selectedIndex];
        const precio = parseFloat(selectedOpt?.dataset?.precio ?? 0);
        const horas  = parseFloat(horasInput.value || 0);

        if (precio > 0 && horas > 0) {
            costeEl.textContent = `💶 ${precio.toFixed(2)} €/h × ${horas} h = ${(precio * horas).toFixed(2)} €`;
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

    _makeAddRow(selectId, onAdd) {
        const row = document.createElement('div');
        row.className = 'sidebar-inline-add';

        const sel = document.createElement('select');
        sel.className = 'sidebar-inline-select';
        sel.id        = selectId;

        const optDef = document.createElement('option');
        optDef.value = '';
        optDef.textContent = '— Seleccionar';
        sel.appendChild(optDef);

        const btn = document.createElement('button');
        btn.type      = 'button';
        btn.className = 'sidebar-btn-add';
        btn.textContent = '＋';
        btn.addEventListener('click', onAdd);

        row.appendChild(sel);
        row.appendChild(btn);
        return row;
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

            this._fillSelect(`sidebar-sel-trabajador-${this.taskId}`, trabajadores);
            this._fillSelect(`sidebar-sel-parcela-${this.taskId}`,    parcelas);

            const selTrabajo = document.getElementById(`sidebar-sel-trabajo-${this.taskId}`);
            if (selTrabajo) {
                this._fillSelect(`sidebar-sel-trabajo-${this.taskId}`, trabajos);
                // Restaurar selección actual
                const current = selTrabajo.dataset.currentTrabajo;
                if (current) selTrabajo.value = current;
                // Actualizar coste una vez cargadas las opciones con precio_hora
                this._actualizarCoste();
            }
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

    async _agregarTrabajador(tagsContainer) {
        const sel        = document.getElementById(`sidebar-sel-trabajador-${this.taskId}`);
        const trabajadorId   = parseInt(sel.value);
        const trabajadorNombre = sel.options[sel.selectedIndex]?.text;
        if (!trabajadorId) return;

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
                sel.value = '';
                this._markSaved('trabajadores');
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

    async _agregarParcela(tagsContainer) {
        const sel      = document.getElementById(`sidebar-sel-parcela-${this.taskId}`);
        const parcelaId    = parseInt(sel.value);
        const parcelaNombre = sel.options[sel.selectedIndex]?.text;
        if (!parcelaId) return;

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
                sel.value = '';
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

    async _cambiarTrabajo(sel) {
        const trabajoId = parseInt(sel.value) || 0;
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
