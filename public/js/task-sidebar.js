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

        // ── Footer: duplicar y eliminar ─────────────────────────────────────
        const dupBtn = document.getElementById('sidebar-duplicate-btn');
        dupBtn.onclick = () => this._duplicarTarea();

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
        const wrap  = this._makeSectionEl(emojiSvg('worker') + ' Trabajadores', 'trabajadores');
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
        cuadrillaBtn.innerHTML = emojiSvg('worker') + ' Añadir cuadrilla';
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

        // Tag del trabajo seleccionado (solo 1, se sustituye al cambiar)
        const tagContainer = document.createElement('div');
        tagContainer.className = 'sidebar-tags';
        tagContainer.id = `sidebar-tags-trabajo-${this.taskId}`;

        const currentTrabajo = tarea.trabajos && tarea.trabajos.length ? tarea.trabajos[0] : null;
        if (currentTrabajo) {
            tagContainer.appendChild(this._makeTag(currentTrabajo.nombre, () => {
                this._quitarTrabajo(tagContainer);
            }));
        }

        const cbWrap = document.createElement('div');
        cbWrap.className = 'combobox-wrap';
        cbWrap.id = `cb-sidebar-work-${this.taskId}`;

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'combobox-input sidebar-inline-select';
        input.placeholder = '— Buscar y seleccionar';
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
        if (currentTrabajo) {
            input.value     = currentTrabajo.nombre;
            hiddenVal.value = currentTrabajo.id;
            cbWrap.dataset.currentTrabajo = currentTrabajo.id;
        }

        // Checkbox "Precio variable" + input de importe
        const precioVarWrap = document.createElement('div');
        precioVarWrap.className = 'sidebar-precio-variable-wrap';
        precioVarWrap.id = `sidebar-precio-var-wrap-${this.taskId}`;

        const checkLabel = document.createElement('label');
        checkLabel.className = 'sidebar-check-label';

        const checkBox = document.createElement('input');
        checkBox.type = 'checkbox';
        checkBox.id = `sidebar-precio-variable-${this.taskId}`;

        const checkText = document.createTextNode(' Precio variable');
        checkLabel.appendChild(checkBox);
        checkLabel.appendChild(checkText);

        const precioFijoInput = document.createElement('input');
        precioFijoInput.type = 'number';
        precioFijoInput.id = `sidebar-precio-fijo-${this.taskId}`;
        precioFijoInput.className = 'sidebar-inline-input';
        precioFijoInput.step = '0.01';
        precioFijoInput.min = '0';
        precioFijoInput.placeholder = 'Importe (€)';
        precioFijoInput.style.display = 'none';
        precioFijoInput.style.width = '120px';

        precioVarWrap.appendChild(checkLabel);
        precioVarWrap.appendChild(precioFijoInput);

        // Inicializar con precio_fijo si la tarea lo tiene
        const tienePrecioFijo = currentTrabajo && currentTrabajo.precio_fijo != null && parseFloat(currentTrabajo.precio_fijo) > 0;
        if (tienePrecioFijo) {
            checkBox.checked = true;
            precioFijoInput.style.display = '';
            precioFijoInput.value = parseFloat(currentTrabajo.precio_fijo);
        }

        // Toggle checkbox: mostrar/ocultar input y guardar
        checkBox.addEventListener('change', () => {
            if (checkBox.checked) {
                precioFijoInput.style.display = '';
                precioFijoInput.focus();
            } else {
                precioFijoInput.style.display = 'none';
                precioFijoInput.value = '';
                // Al desmarcar, quitar precio_fijo (volver a precio/hora)
                this._guardarPrecioFijo(null);
            }
            this._actualizarCoste();
        });

        // Guardar precio fijo al cambiar el input
        precioFijoInput.addEventListener('change', () => {
            this._guardarPrecioFijo(precioFijoInput.value);
            this._actualizarCoste();
        });

        // Coste estimado
        const costeEl = document.createElement('div');
        costeEl.id        = `sidebar-coste-${this.taskId}`;
        costeEl.className = 'sidebar-coste-label';

        // Mostrar coste inicial
        if (currentTrabajo && currentTrabajo.precio_hora) {
            const horas  = parseFloat(tarea.horas || 0);
            const precio = parseFloat(currentTrabajo.precio_hora);

            if (tienePrecioFijo) {
                costeEl.innerHTML = `${emojiSvg('euro')} Precio variable: ${parseFloat(currentTrabajo.precio_fijo).toFixed(2)} €`;
            } else if (horas > 0 && precio > 0) {
                costeEl.innerHTML = `${emojiSvg('euro')} ${precio.toFixed(2)} €/h × ${horas} h = ${(precio * horas).toFixed(2)} €`;
            }
        }

        wrap.appendChild(tagContainer);
        wrap.appendChild(cbWrap);
        wrap.appendChild(precioVarWrap);
        wrap.appendChild(costeEl);
        return wrap;
    }

    /**
     * Quitar el trabajo asignado (vaciar tag + llamar backend con trabajo_id=0)
     */
    async _quitarTrabajo(tagContainer) {
        tagContainer.innerHTML = '';
        _resetCombobox(`cb-sidebar-work-${this.taskId}`);
        await this._cambiarTrabajo(0, '');
        this._actualizarCoste();
    }

    _actualizarCoste() {
        const costeEl    = document.getElementById(`sidebar-coste-${this.taskId}`);
        const horasInput = document.getElementById('sidebar-horas');
        if (!costeEl || !horasInput) return;

        const { id: trabajoId } = _getComboboxSel(`cb-sidebar-work-${this.taskId}`);
        const horas = parseFloat(horasInput.value || 0);

        if (!trabajoId) { costeEl.textContent = ''; return; }

        // Comprobar si hay precio variable activo
        const checkBox = document.getElementById(`sidebar-precio-variable-${this.taskId}`);
        const precioFijoInput = document.getElementById(`sidebar-precio-fijo-${this.taskId}`);
        const precioFijo = checkBox?.checked ? parseFloat(precioFijoInput?.value || 0) : 0;

        // Contar trabajadores asignados
        const tagsContainer = document.getElementById(`sidebar-tags-trabajadores-${this.taskId}`);
        const numTrabajadores = tagsContainer ? tagsContainer.querySelectorAll('.sidebar-tag').length : 1;
        const efectivos = Math.max(numTrabajadores, 1);

        if (checkBox?.checked && precioFijo > 0) {
            // Precio variable: importe fijo × trabajadores
            const costeTotal = precioFijo * efectivos;
            if (efectivos > 1) {
                costeEl.innerHTML = `${emojiSvg('euro')} Variable: ${precioFijo.toFixed(2)} € × ${efectivos} trab. = ${costeTotal.toFixed(2)} €`;
            } else {
                costeEl.innerHTML = `${emojiSvg('euro')} Precio variable: ${precioFijo.toFixed(2)} €`;
            }
        } else if (!checkBox?.checked) {
            // Precio por hora (comportamiento original)
            const trabajos = this._opcionesCache?.trabajos || [];
            const trabajo  = trabajos.find(t => String(t.id) === String(trabajoId));
            const precio   = parseFloat(trabajo?.precio_hora ?? 0);

            if (precio <= 0 || horas <= 0) { costeEl.textContent = ''; return; }
            const costePorTrabajador = precio * horas;
            const costeTotal = costePorTrabajador * efectivos;
            if (efectivos > 1) {
                costeEl.innerHTML = `${emojiSvg('euro')} ${precio.toFixed(2)} €/h × ${horas} h × ${efectivos} trab. = ${costeTotal.toFixed(2)} €`;
            } else {
                costeEl.innerHTML = `${emojiSvg('euro')} ${precio.toFixed(2)} €/h × ${horas} h = ${costeTotal.toFixed(2)} €`;
            }
        } else {
            costeEl.textContent = '';
        }
    }

    /**
     * Guardar precio variable (fijo) en el backend.
     * Si valor es null, se desactiva el precio variable.
     */
    async _guardarPrecioFijo(valor) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            await fetch(buildUrl('tareas/guardarPrecioFijo'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    tarea_id: this.taskId,
                    precio_fijo: valor !== null && valor !== '' ? parseFloat(valor) : null
                })
            });
        } catch (e) {
            console.error('Error guardando precio variable:', e);
        }
    }

    _buildImagenesSection(tarea) {
        const wrap   = this._makeSectionEl('🖼 Imágenes', 'imagenes');
        const gallery = document.createElement('div');
        gallery.id    = 'sidebar-images';
        gallery.style.cssText = 'display:flex; flex-wrap:wrap; gap:8px; margin-bottom:8px;';

        // Renderizar imágenes existentes
        if (tarea.imagenes && tarea.imagenes.length > 0) {
            tarea.imagenes.forEach(img => {
                gallery.appendChild(this._crearImagenThumb(img));
            });
        }

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

        return wrap;
    }

    /**
     * Crear thumbnail de imagen con enlace y botón eliminar
     */
    _crearImagenThumb(img, bustCache = false) {
        const div = document.createElement('div');
        div.className = 'sidebar-img-thumb';
        div.id = 'img-thumb-' + img.id;

        // Construir URL de la imagen con cache-bust opcional
        let imgUrl = buildUrl('/' + img.file_path.replace(/^\//, ''));
        if (bustCache) {
            imgUrl += '?t=' + Date.now();
        }

        const self = this;
        const imageId = img.id;

        const a = document.createElement('a');
        a.href = imgUrl;
        a.onclick = (e) => {
            e.preventDefault();
            // Abrir lightbox con botón eliminar conectado a esta imagen
            const lb = document.getElementById('img-lightbox');
            const lbImg = document.getElementById('img-lightbox-img');
            const lbDel = document.getElementById('img-lightbox-delete');
            if (lb && lbImg) {
                lbImg.src = imgUrl;
                lb.classList.add('open');
                // Conectar botón eliminar del lightbox a esta imagen
                if (lbDel) {
                    lbDel.onclick = (ev) => {
                        ev.stopPropagation();
                        self._eliminarImagen(imageId);
                        window.closeLightbox();
                    };
                }
            } else {
                window.open(imgUrl, '_blank');
            }
        };

        const imgEl = document.createElement('img');
        imgEl.src = imgUrl;
        imgEl.alt = img.original_filename || 'Imagen';
        // Placeholder si falla la carga — mostrar URL en consola para depuración
        imgEl.onerror = () => {
            console.warn('[Imagen] No se pudo cargar:', imgUrl);
            imgEl.style.display = 'none';
            div.classList.add('sidebar-img-thumb-error');
            div.insertAdjacentHTML('afterbegin', '<span title="Imagen no disponible">🖼</span>');
        };
        a.appendChild(imgEl);

        // Botón eliminar en thumbnail — solo visible en desktop
        const delBtn = document.createElement('button');
        delBtn.className = 'sidebar-img-thumb-del';
        delBtn.innerHTML = '×';
        delBtn.title = 'Eliminar imagen';
        delBtn.onclick = (e) => {
            e.preventDefault();
            e.stopPropagation();
            this._eliminarImagen(imageId);
        };

        div.appendChild(a);
        div.appendChild(delBtn);
        return div;
    }

    /**
     * Eliminar imagen de tarea
     */
    async _eliminarImagen(imageId) {
        if (!confirm('¿Eliminar esta imagen?')) return;
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            const res = await fetch(buildUrl('/tareas/eliminarImagen'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ id: imageId })
            });
            const data = await res.json();
            if (data.success) {
                const thumb = document.getElementById('img-thumb-' + imageId);
                if (thumb) thumb.remove();
                showToast('Imagen eliminada', 'success');
            } else {
                showToast(data.message || 'Error al eliminar', 'error');
            }
        } catch (e) {
            showToast('Error de conexión', 'error');
        }
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
                // Actualizar tag verde del trabajo
                const tagContainer = document.getElementById(`sidebar-tags-trabajo-${this.taskId}`);
                if (tagContainer) {
                    tagContainer.innerHTML = '';
                    if (trabajoId > 0 && trabajoNombre) {
                        tagContainer.appendChild(this._makeTag(trabajoNombre, () => {
                            this._quitarTrabajo(tagContainer);
                        }));
                    }
                }
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

            const nuevoTrabajo = { id: data.id, nombre, precio_hora: 0 };

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

    // ─── Duplicar tarea ───────────────────────────────────────────────────────

    async _duplicarTarea() {
        const btn = document.getElementById('sidebar-duplicate-btn');
        setButtonLoading(btn, true);

        try {
            const res = await fetch(buildUrl('/tareas/duplicar'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ id: this.taskId })
            });
            const data = await res.json();

            if (data.success) {
                const fechaMsg = data.fecha
                    ? `Duplicada para el ${new Date(data.fecha + 'T12:00:00').toLocaleDateString('es-ES', { day: 'numeric', month: 'short' })}`
                    : 'Duplicada como pendiente';
                showToast(fechaMsg, 'success');

                // Recargar calendario y abrir la nueva tarea en el sidebar
                window.needsReload = true;
                this.close();
                // Abrir la tarea duplicada tras un breve delay para que el calendario recargue
                setTimeout(() => {
                    if (window.taskSidebar) {
                        window.taskSidebar.open(data.id);
                    }
                }, 600);
            } else {
                showToast(data.message || 'Error al duplicar', 'error');
            }
        } catch (e) {
            showToast('Error de conexión', 'error');
        } finally {
            setButtonLoading(btn, false);
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

        // Validar tamaño en cliente: máx 10MB por imagen (antes de comprimir), máx 10 imágenes
        const maxSize = 10 * 1024 * 1024;
        const maxFiles = 10;
        const validFiles = [];

        for (let i = 0; i < Math.min(files.length, maxFiles); i++) {
            if (files[i].size > maxSize) {
                showToast(`${files[i].name} supera 10MB y se omitirá`, 'error');
            } else {
                validFiles.push(files[i]);
            }
        }

        if (!validFiles.length) {
            fileInput.value = '';
            return;
        }

        showToast('Comprimiendo ' + validFiles.length + ' imagen(es)...', 'info');

        // Comprimir imágenes antes de subir (reduce fotos de móvil de 8-12MB a ~1-2MB)
        const compressedFiles = await compressImages(validFiles);

        const formData = new FormData();
        formData.append('tarea_id', this.taskId);
        compressedFiles.forEach(f => formData.append('imagenes[]', f));

        showToast('Subiendo ' + compressedFiles.length + ' imagen(es)...', 'info');

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            const res  = await fetch(buildUrl('/tareas/subirImagen'), {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });

            // Leer texto primero para diagnosticar errores PHP
            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (parseErr) {
                console.error('Respuesta no JSON:', text);
                showToast('Error del servidor (respuesta no válida)', 'error');
                return;
            }

            if (data.success) {
                fileInput.value = '';
                window.needsReload = true;
                showToast(data.message || 'Imágenes subidas', 'success');
                // Añadir thumbnails al gallery con cache-bust para nuevas imágenes
                const gallery = document.getElementById('sidebar-images');
                if (gallery && data.images) {
                    data.images.forEach(img => {
                        gallery.appendChild(this._crearImagenThumb(img, true));
                    });
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

// Función global para cerrar el lightbox de imágenes
window.closeLightbox = function() {
    const lb = document.getElementById('img-lightbox');
    if (lb) {
        lb.classList.remove('open');
        const lbImg = document.getElementById('img-lightbox-img');
        if (lbImg) lbImg.src = '';
    }
};

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
        hiddenVal.value = opt.id;
        closeList();
        if (onSelect) onSelect(opt);
        // Limpiar el input tras la selección para que vuelva el placeholder
        input.value = '';
        hiddenVal.value = '';
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
