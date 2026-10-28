@extends('layouts.app')

@section('title', 'Chat · CATI')

@section('content')
<div class="container-fluid p-0 d-flex flex-column flex-grow-1">
    <div class="mb-3">
        <h2 class="font-syne text-dark mb-1">Chat</h2>
        <p class="text-muted small mb-0">Escribe a cualquier usuario, sin importar su facultad o rol.</p>
    </div>

    <div class="row g-3 chat-wrap" id="chatWrap">
        {{-- Lista --}}
        <div class="col-md-4 col-xl-3 h-100 chat-list-column">
            <div class="card h-100">
                @if ($esSU)
                    <ul class="nav nav-tabs px-2 pt-2">
                        <li class="nav-item"><button class="nav-link active" id="tabMios" type="button">Mis chats</button></li>
                        <li class="nav-item"><button class="nav-link" id="tabSup" type="button">Supervisión</button></li>
                    </ul>
                @endif
                <div class="p-3 border-bottom position-relative d-flex justify-content-between align-items-center" id="cajaNuevo">
                    <span class="fw-semibold small">Conversaciones</span>
                    <button type="button" id="btnNuevo" class="btn btn-brand-soft btn-sm" title="Nuevo chat" aria-label="Nuevo chat">
                        <i class="fa-solid fa-user-plus"></i>
                    </button>
                    <div id="panelUsuarios" class="panel-usuarios shadow d-none">
                        <div class="p-2 border-bottom">
                            <input type="search" id="buscaUsuario" class="form-control form-control-sm" placeholder="Buscar usuario…" autocomplete="off">
                        </div>
                        <div id="arbolUsuarios" class="p-2"></div>
                    </div>
                </div>
                <div class="chat-lista flex-grow-1" id="lista"></div>
            </div>
        </div>

        {{-- Conversación --}}
        <div class="col-md-8 col-xl-9 h-100 chat-conversation" id="chatConversation">
            <div class="card h-100">
                <div class="px-3 py-2 border-bottom d-flex align-items-center gap-2 chat-cabecera" id="chatCabecera">
                    <span class="fw-semibold text-body-secondary">Selecciona una conversación</span>
                </div>

                {{-- Buscador de mensajes --}}
                <div id="barraBusqueda" class="border-bottom p-2 d-none">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-transparent"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="search" id="buscaMsg" class="form-control" placeholder="Buscar en esta conversación…" autocomplete="off">
                        <button type="button" class="btn btn-secondary btn-sm" id="cerrarBusqueda" aria-label="Cerrar búsqueda"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div id="resultadosBusqueda" class="list-group list-group-flush lista-scroll mt-2"></div>
                </div>

                <div class="chat-mensajes flex-grow-1 p-3 d-flex flex-column gap-2" id="mensajes"></div>
                <form id="formEnviar" class="p-3 border-top d-none">
                    <div id="barraContexto" class="barra-contexto d-none">
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold" id="ctxTitulo"></div>
                            <div class="text-truncate" id="ctxTexto"></div>
                        </div>
                        <button type="button" class="btn-icono" id="ctxCancelar" aria-label="Cancelar" title="Cancelar"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="input-group">
                        <input type="text" id="texto" class="form-control" maxlength="2000" placeholder="Escribe un mensaje…" autocomplete="off">
                        <button class="btn btn-primary" type="submit" aria-label="Enviar"><i class="fa-solid fa-paper-plane"></i></button>
                    </div>
                </form>
                <div id="soloLectura" class="p-3 border-top small text-body-secondary d-none"></div>
            </div>
        </div>
    </div>
</div>

{{-- Perfil --}}
<div class="modal fade" id="modalPerfil" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center" id="perfilBody"></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const RUTAS = {
        lista: @json(route('chat.conversaciones')),
        sup: @json(route('chat.supervision')),
        usuarios: @json(route('chat.usuarios')),
        abrir: @json(route('chat.abrir')),
        perfil: @json(route('chat.perfil', '__ID__')),
        mensajes: @json(route('chat.mensajes', '__ID__')),
        editar: @json(route('chat.editar', ['id' => '__ID__', 'mensaje' => '__MSG__'])),
        eliminar: @json(route('chat.eliminar', '__ID__')),
        supMsg: @json(route('chat.supervision.mensajes', '__ID__')),
        buscar: @json(route('chat.buscar', '__ID__')),
    };
    const MI_FAC = @json($miFacultad);
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const $ = id => document.getElementById(id);
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const avatar = (foto, nombre, extra = '') => foto
        ? `<img src="${esc(foto)}" alt="" class="chat-avatar ${extra}">`
        : `<span class="chat-avatar chat-avatar--ini ${extra}">${esc((nombre || '?').charAt(0).toUpperCase())}</span>`;
    const badgeRol = r => r ? `<span class="badge rounded-pill bg-primary-subtle text-primary-emphasis text-xs">${esc(r)}</span>` : '';
    const badgeFac = f => f ? `<span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis text-xs">${esc(f)}</span>` : '';

    let modo = 'mios', activa = null, ultimaFirma = '', ultimaCab = '', usuarios = null;
    let mensajesActuales = [], contactoActual = null, respondiendo = null, editando = null, tBusqueda = null;

    async function api(url, opciones = {}) {
        const r = await fetch(url, {
            ...opciones,
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
        });
        const d = await r.json().catch(() => ({}));
        if (!r.ok) throw new Error(d.message || 'Ocurrió un error.');
        return d;
    }

    /* ───────── lista ───────── */
    async function cargarLista() {
        try {
            const items = await api(modo === 'mios' ? RUTAS.lista : RUTAS.sup);
            $('lista').innerHTML = items.length ? items.map(i => `
                <div class="chat-item px-3 py-2 border-bottom d-flex gap-2 ${i.id === activa ? 'activo' : ''}" data-id="${i.id}" data-titulo="${esc(i.titulo)}">
                    ${i.userId ? `<span role="button" data-perfil="${i.userId}">${avatar(i.foto, i.titulo)}</span>` : avatar(null, i.titulo)}
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between gap-2">
                            <span class="fw-semibold small text-truncate">${esc(i.titulo)}</span>
                            ${i.noLeidos ? `<span class="badge rounded-pill bg-danger">${i.noLeidos}</span>` : `<span class="text-xs text-body-tertiary text-nowrap">${esc(i.fecha || '')}</span>`}
                        </div>
                        ${(i.rol || i.facultad) ? `<div class="d-flex gap-1 flex-wrap">${badgeRol(i.rol)} ${badgeFac(i.facultad)}</div>` : ''}
                        <div class="text-xs text-body-secondary text-truncate">${esc(i.ultimo)}</div>
                    </div>
                </div>`).join('')
                : '<div class="p-4 text-center small text-body-secondary">Sin conversaciones.</div>';
        } catch (e) {}
    }

    /* ───────── cabecera: volver (móvil) · info · menú ⋮ · cerrar (escritorio) ───────── */
    const BTN_VOLVER = `<button type="button" class="btn-icono d-md-none" data-cerrar aria-label="Volver a la lista" title="Volver"><i class="fa-solid fa-arrow-left"></i></button>`;
    const BTN_CERRAR = `<button type="button" class="btn-icono d-none d-md-inline-flex" data-cerrar aria-label="Cerrar conversación" title="Cerrar conversación"><i class="fa-solid fa-xmark"></i></button>`;
    const MENU = `<div class="dropdown">
        <button type="button" class="btn-icono" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Más opciones" title="Más opciones"><i class="fa-solid fa-ellipsis-vertical"></i></button>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
            <li><button type="button" class="dropdown-item small" data-buscar><i class="fa-solid fa-magnifying-glass w-20px"></i> Buscar mensajes</button></li>
            <li><button type="button" class="dropdown-item small text-danger" data-eliminar><i class="fa-solid fa-trash w-20px"></i> Eliminar chat</button></li>
        </ul></div>`;

    function pintarCabecera(inner, conMenu) {
        $('chatCabecera').innerHTML = BTN_VOLVER + inner +
            `<div class="ms-auto d-flex align-items-center gap-1">${conMenu ? MENU : ''}${BTN_CERRAR}</div>`;
    }
    function cabeceraContacto(c) {
        if (!c) return;
        const firma = JSON.stringify(c);
        if (firma === ultimaCab) return;
        ultimaCab = firma;
        pintarCabecera(`
            <button type="button" class="btn p-0 border-0 bg-transparent" ${c.id ? `data-perfil="${c.id}"` : 'disabled'}>${avatar(c.foto, c.nombre, 'chat-avatar--lg')}</button>
            <div class="lh-sm min-w-0">
                <div class="fw-semibold text-truncate">${esc(c.nombre)} ${badgeRol(c.rol)} ${badgeFac(c.facultad)}</div>
                ${c.anonimo ? '' : `<small class="${c.enLinea ? 'text-success' : 'text-body-secondary'}">${
                    c.enLinea ? '● En línea' : (c.conexion ? 'Últ. conexión: ' + esc(c.conexion) : 'Sin conexión registrada')}</small>`}
            </div>`, true);
    }

    function cerrarConversacion() {
        activa = null; ultimaFirma = ''; ultimaCab = ''; mensajesActuales = []; contactoActual = null;
        cancelarContexto(); cerrarBusqueda();
        $('mensajes').innerHTML = '';
        $('chatCabecera').innerHTML = '<span class="fw-semibold text-body-secondary">Selecciona una conversación</span>';
        $('chatWrap').classList.remove('conversacion-abierta');
        $('formEnviar').classList.add('d-none'); $('soloLectura').classList.add('d-none');
        cargarLista();
    }

    async function abrirConversacion(id, titulo) {
        activa = id; ultimaFirma = ''; ultimaCab = '';
        cancelarContexto(); cerrarBusqueda();
        $('chatWrap').classList.add('conversacion-abierta');
        pintarCabecera(`<span class="fw-semibold">${esc(titulo)}</span>`, modo === 'mios');
        $('mensajes').innerHTML = '';
        await cargarMensajes(true);
        cargarLista();
    }

    $('chatCabecera').addEventListener('click', async e => {
        if (e.target.closest('[data-cerrar]')) return cerrarConversacion();
        if (e.target.closest('[data-buscar]') && activa) return abrirBusqueda();
        if (!e.target.closest('[data-eliminar]') || !activa) return;
        if (!await confirmar('Esta conversación se quitará de tu lista. La otra persona seguirá viéndola.', 'Eliminar chat', 'Eliminar')) return;
        try {
            await api(RUTAS.eliminar.replace('__ID__', activa), { method: 'DELETE' });
            cerrarConversacion(); showToast('Chat eliminado de tu lista.');
        } catch (err) { showToast(err.message, 'error'); }
    });

    /* ───────── búsqueda de mensajes ───────── */
    function abrirBusqueda() {
        $('barraBusqueda').classList.remove('d-none');
        $('buscaMsg').focus();
    }
    function cerrarBusqueda() {
        clearTimeout(tBusqueda);
        $('barraBusqueda').classList.add('d-none');
        $('buscaMsg').value = ''; $('resultadosBusqueda').innerHTML = '';
    }
    const reEsc = s => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

    async function buscarMensajes() {
        const q = $('buscaMsg').value.trim(), caja = $('resultadosBusqueda');
        if (!activa || q.length < 2) { caja.innerHTML = q.length ? '<div class="small text-body-secondary px-2">Escribe al menos 2 letras.</div>' : ''; return; }
        try {
            const items = await api(RUTAS.buscar.replace('__ID__', activa) + '?q=' + encodeURIComponent(q));
            const re = new RegExp(reEsc(esc(q)), 'gi');
            caja.innerHTML = items.length ? items.map(m => `
                <button type="button" class="list-group-item list-group-item-action small py-2" data-msg="${m.id}">
                    <div class="text-xs text-body-secondary">${m.mio ? 'Tú' : 'Contacto'} · ${esc(m.fecha)}</div>
                    <div class="text-break">${esc(m.texto).replace(re, x => `<mark>${x}</mark>`)}</div>
                </button>`).join('')
                : '<div class="small text-body-secondary px-2">Sin resultados.</div>';
        } catch (err) { showToast(err.message, 'error'); }
    }
    $('buscaMsg').addEventListener('input', () => { clearTimeout(tBusqueda); tBusqueda = setTimeout(buscarMensajes, 350); });
    $('cerrarBusqueda').addEventListener('click', cerrarBusqueda);
    $('resultadosBusqueda').addEventListener('click', e => {
        const b = e.target.closest('[data-msg]');
        if (!b) return;
        const el = $('mensajes').querySelector(`[data-id="${b.dataset.msg}"]`);
        if (!el) { showToast('Es un mensaje antiguo que no está cargado; léelo en los resultados.', 'error'); return; }
        el.scrollIntoView({ block: 'center', behavior: 'smooth' });
        el.classList.add('resaltado'); setTimeout(() => el.classList.remove('resaltado'), 2000);
    });

    /* ───────── mensajes ───────── */
    async function cargarMensajes(forzar) {
        if (!activa) return;
        try {
            const d = await api((modo === 'mios' ? RUTAS.mensajes : RUTAS.supMsg).replace('__ID__', activa));
            const caja = $('mensajes');
            const alFinal = caja.scrollHeight - caja.scrollTop - caja.clientHeight < 80;
            const firma = JSON.stringify(d.mensajes);
            mensajesActuales = d.mensajes;
            contactoActual = d.contacto;

            if (firma !== ultimaFirma) {
                ultimaFirma = firma;
                caja.innerHTML = '';
                d.mensajes.forEach(m => {
                    const fila = document.createElement('div');
                    fila.className = 'msg-fila ' + (m.mio ? 'fila-mia' : 'fila-otra');
                    fila.dataset.id = m.id;
                    const estado = m.mio ? (m.leido
                        ? ' <span class="visto"><i class="fa-solid fa-check-double"></i> Visto</span>'
                        : ' <i class="fa-solid fa-check"></i>') : '';
                    const cita = m.respuesta
                        ? `<div class="cita" data-ir="${m.respuesta.id}"><strong>${esc(m.respuesta.autor)}</strong><span>${esc(m.respuesta.texto)}</span></div>` : '';
                    const burbuja = `<div class="burbuja ${m.mio ? 'mia' : 'otra'}">` + (m.autor ? `<strong>${esc(m.autor)}</strong><br>` : '') + cita + esc(m.texto)
                        + `<small>${esc(m.fecha)}${m.editado ? ' · editado' : ''}${estado}</small></div>`;
                    const acciones = modo === 'mios' ? `<div class="msg-acciones">`
                        + (d.puedeResponder ? `<button type="button" data-responder="${m.id}" title="Responder" aria-label="Responder"><i class="fa-solid fa-reply"></i></button>` : '')
                        + (m.puedeEditar ? `<button type="button" data-editar="${m.id}" title="Editar (hasta 15 min después de enviarlo)" aria-label="Editar"><i class="fa-solid fa-pen"></i></button>` : '')
                        + `</div>` : '';
                    fila.innerHTML = burbuja + acciones;
                    caja.appendChild(fila);
                });
                if (forzar || alFinal) caja.scrollTop = caja.scrollHeight;
            }
            if (modo === 'mios') cabeceraContacto(d.contacto);

            const puede = modo === 'mios' && d.puedeResponder;
            $('formEnviar').classList.toggle('d-none', !puede);
            $('soloLectura').classList.toggle('d-none', puede);
            $('soloLectura').textContent = modo === 'mios' ? 'No puedes responder en esta conversación.' : 'Modo supervisión: solo lectura.';
        } catch (e) {}
    }

    /* ───────── responder / editar ───────── */
    function mostrarContexto(titulo, texto) {
        $('ctxTitulo').textContent = titulo; $('ctxTexto').textContent = texto;
        $('barraContexto').classList.remove('d-none'); $('texto').focus();
    }
    function cancelarContexto() {
        if (editando) $('texto').value = '';
        respondiendo = null; editando = null;
        $('barraContexto').classList.add('d-none');
    }
    function iniciarRespuesta(id) {
        const m = mensajesActuales.find(x => x.id === id); if (!m) return;
        cancelarContexto();
        respondiendo = { id };
        mostrarContexto('Respondiendo a ' + (m.mio ? 'tu mensaje' : (contactoActual?.nombre || 'mensaje')), m.texto);
    }
    function iniciarEdicion(id) {
        const m = mensajesActuales.find(x => x.id === id); if (!m || !m.puedeEditar) return;
        cancelarContexto();
        editando = { id };
        $('texto').value = m.texto;
        mostrarContexto('Editando mensaje', m.texto);
    }
    $('ctxCancelar').addEventListener('click', cancelarContexto);

    $('mensajes').addEventListener('click', e => {
        const r = e.target.closest('[data-responder]'), ed = e.target.closest('[data-editar]'), ir = e.target.closest('[data-ir]');
        if (r) iniciarRespuesta(Number(r.dataset.responder));
        else if (ed) iniciarEdicion(Number(ed.dataset.editar));
        else if (ir) {
            const el = $('mensajes').querySelector(`[data-id="${ir.dataset.ir}"]`);
            if (el) { el.scrollIntoView({ block: 'center', behavior: 'smooth' }); el.classList.add('resaltado'); setTimeout(() => el.classList.remove('resaltado'), 1500); }
        }
    });

    $('formEnviar').addEventListener('submit', async e => {
        e.preventDefault();
        const t = $('texto').value.trim();
        if (!t || !activa) return;
        try {
            if (editando) {
                await api(RUTAS.editar.replace('__ID__', activa).replace('__MSG__', editando.id), { method: 'PATCH', body: JSON.stringify({ Mensaje: t }) });
            } else {
                await api(RUTAS.mensajes.replace('__ID__', activa), { method: 'POST', body: JSON.stringify({ Mensaje: t, IdRespuesta: respondiendo?.id ?? null }) });
            }
            $('texto').value = ''; respondiendo = null; editando = null; $('barraContexto').classList.add('d-none');
            await cargarMensajes(true); cargarLista();
        } catch (err) {
            showToast(err.message, 'error');
            if (editando) cancelarContexto();
        }
    });

    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape' || !activa || document.querySelector('.modal.show')) return;
        if (!$('barraBusqueda').classList.contains('d-none')) cerrarBusqueda();
        else if (respondiendo || editando) cancelarContexto(); else cerrarConversacion();
    });

    $('lista').addEventListener('click', e => {
        if (e.target.closest('[data-perfil]')) return;
        const el = e.target.closest('.chat-item');
        if (el) abrirConversacion(Number(el.dataset.id), el.dataset.titulo);
    });

    /* ───────── perfil ───────── */
    document.addEventListener('click', async e => {
        const b = e.target.closest('[data-perfil]');
        if (!b) return;
        try {
            const p = await api(RUTAS.perfil.replace('__ID__', b.dataset.perfil));
            $('perfilBody').innerHTML = `
                <div class="mb-3"><span role="button" data-ampliar="${esc(p.foto || '')}" class="d-inline-block">${avatar(p.foto, p.nombreCompleto, 'chat-avatar--xl')}</span></div>
                <h5 class="font-syne mb-1">${esc(p.nombreCompleto)}</h5>
                <div class="mb-3">${badgeRol(p.rol)} ${badgeFac(p.facultadClave)}</div>
                <dl class="row small text-start mb-0">
                    <dt class="col-5 text-body-secondary">Facultad</dt><dd class="col-7">${esc(p.facultad)}</dd>
                    <dt class="col-5 text-body-secondary">Matrícula</dt><dd class="col-7 font-monospace">${esc(p.matricula)}</dd>
                    <dt class="col-5 text-body-secondary">Correo</dt><dd class="col-7 text-break">${esc(p.correo)}</dd>
                    <dt class="col-5 text-body-secondary">Rol</dt><dd class="col-7">${esc(p.rol)}</dd>
                    <dt class="col-5 text-body-secondary">Género</dt><dd class="col-7">${esc(p.genero)}</dd>
                </dl>`;
            bootstrap.Modal.getOrCreateInstance($('modalPerfil')).show();
        } catch (err) { showToast(err.message, 'error'); }
    });

    /* ───────── nuevo chat ───────── */
    function pintarArbol() {
        const q = $('buscaUsuario').value.trim().toLowerCase();
        const lista = (usuarios || []).filter(u => !q || `${u.nombre} ${u.rol} ${u.facultad ?? ''}`.toLowerCase().includes(q));
        const facs = new Map();
        lista.forEach(u => {
            if (!facs.has(u.facultadId)) facs.set(u.facultadId, { clave: u.facultad, propia: u.facultadId === MI_FAC, roles: new Map() });
            const f = facs.get(u.facultadId);
            if (!f.roles.has(u.rol)) f.roles.set(u.rol, { orden: u.rolOrden, items: [] });
            f.roles.get(u.rol).items.push(u);
        });
        const orden = [...facs.values()].sort((a, b) => (b.propia - a.propia) || String(a.clave).localeCompare(String(b.clave)));

        $('arbolUsuarios').innerHTML = orden.length ? orden.map(f => `
            <details class="arbol-fac" ${f.propia || q ? 'open' : ''}>
                <summary><i class="fa-solid fa-building-columns me-1"></i>${esc(f.clave || 'Sin facultad')}
                    ${f.propia ? '<span class="badge rounded-pill bg-primary-subtle text-primary-emphasis text-xs ms-1">Mi facultad</span>' : ''}</summary>
                ${[...f.roles.entries()].sort((a, b) => a[1].orden - b[1].orden).map(([rol, g]) => `
                    <details class="arbol-rol" ${f.propia || q ? 'open' : ''}>
                        <summary>${esc(rol)} <span class="text-body-tertiary">(${g.items.length})</span></summary>
                        ${g.items.map(u => `
                            <button type="button" class="arbol-user" data-id="${u.id}" data-nombre="${esc(u.nombre)}">
                                ${avatar(u.foto, u.nombre, 'chat-avatar--sm')} <span class="text-truncate">${esc(u.nombre)}</span>
                            </button>`).join('')}
                    </details>`).join('')}
            </details>`).join('') : '<div class="small text-body-secondary p-2">Sin resultados</div>';
    }

    $('btnNuevo').addEventListener('click', async () => {
        const p = $('panelUsuarios');
        p.classList.toggle('d-none');
        if (!p.classList.contains('d-none')) {
            try { if (!usuarios) usuarios = await api(RUTAS.usuarios); pintarArbol(); $('buscaUsuario').focus(); }
            catch (err) { showToast(err.message, 'error'); }
        }
    });
    $('buscaUsuario').addEventListener('input', pintarArbol);
    document.addEventListener('click', e => { if (!e.target.closest('#cajaNuevo')) $('panelUsuarios').classList.add('d-none'); });

    $('arbolUsuarios').addEventListener('click', async e => {
        const b = e.target.closest('button[data-id]');
        if (!b) return;
        try {
            const d = await api(RUTAS.abrir, { method: 'POST', body: JSON.stringify({ IdUsuario: Number(b.dataset.id) }) });
            $('panelUsuarios').classList.add('d-none');
            $('buscaUsuario').value = '';
            modo = 'mios';
            await cargarLista();
            abrirConversacion(d.id, b.dataset.nombre);
        } catch (err) { showToast(err.message, 'error'); }
    });

    /* ───────── pestañas del superusuario ───────── */
    function cambiarModo(m) {
        modo = m;
        $('tabMios')?.classList.toggle('active', m === 'mios');
        $('tabSup')?.classList.toggle('active', m === 'sup');
        $('btnNuevo').classList.toggle('d-none', m === 'sup');
        cerrarConversacion();
    }
    $('tabMios')?.addEventListener('click', () => cambiarModo('mios'));
    $('tabSup')?.addEventListener('click', () => cambiarModo('sup'));

    const usuarioInicial = new URLSearchParams(window.location.search).get('usuario');
    if (usuarioInicial) {
        const idUsuario = Number(usuarioInicial);
        if (Number.isInteger(idUsuario) && idUsuario > 0) {
            api(RUTAS.abrir, { method: 'POST', body: JSON.stringify({ IdUsuario: idUsuario }) })
                .then(async d => {
                    history.replaceState(null, '', window.location.pathname);
                    await cargarLista();
                    await abrirConversacion(d.id, '');
                })
                .catch(err => {
                    history.replaceState(null, '', window.location.pathname);
                    showToast(err.message, 'error');
                    cargarLista();
                });
        } else {
            history.replaceState(null, '', window.location.pathname);
            cargarLista();
        }
    } else {
        cargarLista();
    }
    setInterval(() => { cargarLista(); if (activa) cargarMensajes(false); }, 5000);
</script>
@endpush
@endsection
