@extends('layouts.app')

@section('title', 'Chat · CATI')

@section('content')
<div class="container-fluid p-0 d-flex flex-column flex-grow-1">
    <div class="mb-3">
        <h2 class="font-syne text-dark mb-1">Chat</h2>
        <p class="text-muted small mb-0">Escribe a cualquier usuario, sin importar su facultad o rol.</p>
    </div>

    <div class="row g-3 chat-wrap">
        {{-- Lista --}}
        <div class="col-md-4 col-xl-3 h-100">
            <div class="card h-100">
                @if ($esSU)
                    <ul class="nav nav-tabs px-2 pt-2">
                        <li class="nav-item"><button class="nav-link active" id="tabMios" type="button">Mis chats</button></li>
                        <li class="nav-item"><button class="nav-link" id="tabSup" type="button">Supervisión</button></li>
                    </ul>
                @endif
                <div class="p-3 border-bottom position-relative" id="cajaNuevo">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="fa-solid fa-user-plus"></i></span>
                        <input type="search" id="buscaUsuario" class="form-control" placeholder="Nuevo chat: buscar usuario…" autocomplete="off">
                    </div>
                    <div id="resultados" class="list-group position-absolute start-0 end-0 mx-3 shadow z-3 d-none"></div>
                </div>
                <div class="chat-lista flex-grow-1" id="lista"></div>
            </div>
        </div>

        {{-- Conversación --}}
        <div class="col-md-8 col-xl-9 h-100">
            <div class="card h-100">
                <div class="px-3 py-2 border-bottom fw-semibold" id="chatTitulo">Selecciona una conversación</div>
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

@push('scripts')
<script>
    const RUTAS = {
        lista: @json(route('chat.conversaciones')),
        sup: @json(route('chat.supervision')),
        usuarios: @json(route('chat.usuarios')),
        abrir: @json(route('chat.abrir')),
        mensajes: @json(route('chat.mensajes', '__ID__')),
        supMsg: @json(route('chat.supervision.mensajes', '__ID__')),
    };
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const $ = id => document.getElementById(id);
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    let modo = 'mios', activa = null, ultimoId = 0;

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
                <div class="chat-item px-3 py-2 border-bottom ${i.id === activa ? 'activo' : ''}" data-id="${i.id}" data-titulo="${esc(i.titulo)}">
                    <div class="d-flex justify-content-between gap-2">
                        <span class="fw-semibold small text-truncate">${esc(i.titulo)}</span>
                        ${i.noLeidos ? `<span class="badge rounded-pill bg-danger">${i.noLeidos}</span>` : `<span class="text-xs text-body-tertiary">${esc(i.fecha || '')}</span>`}
                    </div>
                    <div class="text-xs text-body-secondary text-truncate">${esc(i.ultimo)}</div>
                </div>`).join('')
                : '<div class="p-4 text-center small text-body-secondary">Sin conversaciones.</div>';
        } catch (e) {}
    }

    async function abrirConversacion(id, titulo) {
        activa = id; ultimoId = 0;
        $('chatTitulo').textContent = titulo;
        $('mensajes').innerHTML = '';
        await cargarMensajes(true);
        cargarLista();
    }

    async function cargarMensajes(completo) {
        if (!activa) return;
        try {
            const base = (modo === 'mios' ? RUTAS.mensajes : RUTAS.supMsg).replace('__ID__', activa);
            const d = await api(modo === 'mios' && !completo && ultimoId ? `${base}?despues=${ultimoId}` : base);
            const caja = $('mensajes');
            const alFinal = caja.scrollHeight - caja.scrollTop - caja.clientHeight < 80;

            if (completo || modo !== 'mios') { caja.innerHTML = ''; ultimoId = 0; }
            d.mensajes.forEach(m => {
                ultimoId = Math.max(ultimoId, m.id);
                const div = document.createElement('div');
                div.className = 'burbuja ' + (m.mio ? 'mia align-self-end' : 'otra align-self-start');
                div.innerHTML = (m.autor ? `<strong>${esc(m.autor)}</strong><br>` : '') + esc(m.texto) + `<small>${esc(m.fecha)}</small>`;
                caja.appendChild(div);
            });
            if (completo || alFinal) caja.scrollTop = caja.scrollHeight;

            const puede = modo === 'mios' && d.puedeResponder;
            $('formEnviar').classList.toggle('d-none', !puede);
            $('soloLectura').classList.toggle('d-none', puede);
            $('soloLectura').textContent = modo === 'mios' ? 'No puedes responder en esta conversación.' : 'Modo supervisión: solo lectura.';
        } catch (e) {}
    }

    $('lista').addEventListener('click', e => {
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
            await cargarMensajes(false);
            cargarLista();
        } catch (err) { showToast(err.message, 'error'); }
    });

    // Nuevo chat
    let tBusca;
    $('buscaUsuario').addEventListener('input', e => {
        clearTimeout(tBusca);
        tBusca = setTimeout(async () => {
            const q = e.target.value.trim();
            const caja = $('resultados');
            if (!q) { caja.classList.add('d-none'); return; }
            try {
                const us = await api(RUTAS.usuarios + '?q=' + encodeURIComponent(q));
                caja.innerHTML = us.length ? us.map(u => `
                    <button type="button" class="list-group-item list-group-item-action small" data-id="${u.id}" data-nombre="${esc(u.nombre)}">
                        ${esc(u.nombre)} <span class="text-body-secondary">· ${esc(u.rol || '')}</span>
                    </button>`).join('') : '<div class="list-group-item small text-body-secondary">Sin resultados</div>';
                caja.classList.remove('d-none');
            } catch (err) {}
        }, 250);
    });
    $('resultados').addEventListener('click', async e => {
        const b = e.target.closest('button[data-id]');
        if (!b) return;
        try {
            const d = await api(RUTAS.abrir, { method: 'POST', body: JSON.stringify({ IdUsuario: Number(b.dataset.id) }) });
            $('resultados').classList.add('d-none');
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
        $('cajaNuevo').classList.toggle('d-none', m === 'sup');
        $('mensajes').innerHTML = ''; $('chatTitulo').textContent = 'Selecciona una conversación';
        $('formEnviar').classList.add('d-none'); $('soloLectura').classList.add('d-none');
        cargarLista();
    }
    $('tabMios')?.addEventListener('click', () => cambiarModo('mios'));
    $('tabSup')?.addEventListener('click', () => cambiarModo('sup'));

    cargarLista();
    setInterval(() => { cargarLista(); if (activa) cargarMensajes(false); }, 5000);
</script>
@endpush
@endsection
