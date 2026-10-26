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
                <div class="px-3 py-2 border-bottom d-flex align-items-center gap-2" id="chatCabecera">
                    <span class="fw-semibold">Selecciona una conversación</span>
                    <button type="button" id="btnCerrarConversacion" class="btn btn-secondary btn-sm ms-auto" aria-label="Cerrar conversación">
                        <i class="fa-solid fa-xmark me-1"></i> Cerrar
                    </button>
                </div>
                <div class="chat-mensajes flex-grow-1 p-3 d-flex flex-column gap-2" id="mensajes"></div>
                <form id="formEnviar" class="p-3 border-top d-none">
                    <div class="input-group">
                        <input type="text" id="texto" class="form-control" maxlength="2000" placeholder="Escribe un mensaje…" autocomplete="off">
                        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane"></i></button>
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
        eliminar: @json(route('chat.eliminar', '__ID__')),
        supMsg: @json(route('chat.supervision.mensajes', '__ID__')),
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

    let modo = 'mios', activa = null, ultimaFirma = '', usuarios = null;

    async function api(url, opciones = {}) {
        const r = await fetch(url, {
            ...opciones,
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
        });
        const d = await r.json().catch(() => ({}));
        if (!r.ok) throw new Error(d.message || 'Ocurrió un error.');
        return d;
    }

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

    function cabecera(c) {
        if (!c) return;
        $('chatCabecera').innerHTML = `
            <button type="button" class="btn p-0 border-0 bg-transparent" ${c.id ? `data-perfil="${c.id}"` : 'disabled'}>${avatar(c.foto, c.nombre, 'chat-avatar--lg')}</button>
            <div class="lh-sm">
                <div class="fw-semibold">${esc(c.nombre)} ${badgeRol(c.rol)} ${badgeFac(c.facultad)}</div>
                ${c.anonimo ? '' : `<small class="${c.enLinea ? 'text-success' : 'text-body-secondary'}">${
                    c.enLinea ? '● En línea' : (c.conexion ? 'Últ. conexión: ' + esc(c.conexion) : 'Sin conexión registrada')}</small>`}
            </div>
            <button type="button" id="btnCerrarConversacion" class="btn btn-secondary btn-sm ms-auto" aria-label="Cerrar conversación">
                <i class="fa-solid fa-xmark me-1"></i> Cerrar
            </button>`;
    }

    async function abrirConversacion(id, titulo) {
        activa = id; ultimaFirma = '';
        $('chatWrap').classList.add('conversacion-abierta');
        $('chatCabecera').innerHTML = `<span class="fw-semibold">${esc(titulo)}</span>
            <button type="button" id="btnCerrarConversacion" class="btn btn-secondary btn-sm ms-auto" aria-label="Cerrar conversación">
                <i class="fa-solid fa-xmark me-1"></i> Cerrar
            </button>`;
        $('mensajes').innerHTML = '';
        await cargarMensajes(true);
        cargarLista();
    }

    $('chatCabecera').addEventListener('click', e => {
        if (e.target.closest('#btnCerrarConversacion')) $('chatWrap').classList.remove('conversacion-abierta');
    });

    async function cargarMensajes(forzar) {
        if (!activa) return;
        try {
            const base = (modo === 'mios' ? RUTAS.mensajes : RUTAS.supMsg).replace('__ID__', activa);
            const d = await api(base);
            const caja = $('mensajes');
            const alFinal = caja.scrollHeight - caja.scrollTop - caja.clientHeight < 80;
            const firma = JSON.stringify(d.mensajes);

            if (firma !== ultimaFirma) {
                ultimaFirma = firma;
                caja.innerHTML = '';
                d.mensajes.forEach(m => {
                    const div = document.createElement('div');
                    div.className = 'burbuja ' + (m.mio ? 'mia align-self-end' : 'otra align-self-start');
                    const estado = m.mio ? (m.leido
                        ? ' <span class="visto"><i class="fa-solid fa-check-double"></i> Visto</span>'
                        : ' <i class="fa-solid fa-check"></i>') : '';
                    div.innerHTML = (m.autor ? `<strong>${esc(m.autor)}</strong><br>` : '') + esc(m.texto) + `<small>${esc(m.fecha)}${estado}</small>`;
                    caja.appendChild(div);
                });
                if (forzar || alFinal) caja.scrollTop = caja.scrollHeight;
            }
            if (modo === 'mios') { cabecera(d.contacto); botonEliminar(); }

            const puede = modo === 'mios' && d.puedeResponder;
            $('formEnviar').classList.toggle('d-none', !puede);
            $('soloLectura').classList.toggle('d-none', puede);
            $('soloLectura').textContent = modo === 'mios' ? 'No puedes responder en esta conversación.' : 'Modo supervisión: solo lectura.';
        } catch (e) {}
    }

    // Eliminar chat (borrado lógico: solo se oculta para mí)
    function botonEliminar() {
        if (!activa || modo !== 'mios') return;
        const b = document.createElement('button');
        b.type = 'button'; b.className = 'btn btn-ghost is-danger btn-sm ms-auto';
        b.dataset.eliminar = '1'; b.title = 'Eliminar chat';
        b.innerHTML = '<i class="fa-solid fa-trash"></i>';
        $('chatCabecera').appendChild(b);
    }
    $('chatCabecera').addEventListener('click', async e => {
        if (!e.target.closest('[data-eliminar]') || !activa) return;
        if (!await confirmar('Esta conversación se quitará de tu lista. La otra persona seguirá viéndola.', 'Eliminar chat', 'Eliminar')) return;
        try {
            await api(RUTAS.eliminar.replace('__ID__', activa), { method: 'DELETE' });
            activa = null; ultimaFirma = '';
            $('mensajes').innerHTML = '';
            $('chatCabecera').innerHTML = `<span class="fw-semibold">Selecciona una conversación</span>
                <button type="button" id="btnCerrarConversacion" class="btn btn-secondary btn-sm ms-auto" aria-label="Cerrar conversación">
                    <i class="fa-solid fa-xmark me-1"></i> Cerrar
                </button>`;
            $('chatWrap').classList.remove('conversacion-abierta');
            $('formEnviar').classList.add('d-none'); $('soloLectura').classList.add('d-none');
            cargarLista(); showToast('Chat eliminado de tu lista.');
        } catch (err) { showToast(err.message, 'error'); }
    });

    $('lista').addEventListener('click', e => {
        if (e.target.closest('[data-perfil]')) return;
        const el = e.target.closest('.chat-item');
        if (el) abrirConversacion(Number(el.dataset.id), el.dataset.titulo);
    });

    $('formEnviar').addEventListener('submit', async e => {
        e.preventDefault();
        const t = $('texto').value.trim();
        if (!t || !activa) return;
        $('texto').value = '';
        try {
            await api(RUTAS.mensajes.replace('__ID__', activa), { method: 'POST', body: JSON.stringify({ Mensaje: t }) });
            await cargarMensajes(true);
            cargarLista();
        } catch (err) { showToast(err.message, 'error'); }
    });

    // Perfil (clic en cualquier foto)
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

    // Nuevo chat: botón que despliega usuarios por facultad y rol
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

    // Pestañas del superusuario
    function cambiarModo(m) {
        modo = m; activa = null;
        $('tabMios')?.classList.toggle('active', m === 'mios');
        $('tabSup')?.classList.toggle('active', m === 'sup');
        $('btnNuevo').classList.toggle('d-none', m === 'sup');
        $('mensajes').innerHTML = ''; $('chatCabecera').innerHTML = `<span class="fw-semibold">Selecciona una conversación</span>
            <button type="button" id="btnCerrarConversacion" class="btn btn-secondary btn-sm ms-auto" aria-label="Cerrar conversación">
                <i class="fa-solid fa-xmark me-1"></i> Cerrar
            </button>`;
        $('chatWrap').classList.remove('conversacion-abierta');
        $('formEnviar').classList.add('d-none'); $('soloLectura').classList.add('d-none');
        cargarLista();
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
