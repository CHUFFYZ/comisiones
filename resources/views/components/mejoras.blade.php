{{-- Pantalla de carga + doble confirmación (se incluye al final del layout) --}}
<div class="cargando-fondo" id="cargando" role="alertdialog" aria-live="assertive" aria-label="Procesando">
    <div class="cargando-caja">
        <div class="spinner-border text-primary mb-3" role="status"></div>
        <div class="fw-semibold">Procesando…</div>
        <div class="small text-body-secondary">No cierres ni recargues esta ventana. El proceso continúa en el servidor aunque la cierres y recibirás una notificación al terminar.</div>
    </div>
</div>
<script>
    (function () {
        const fondo = document.getElementById('cargando');

        // Doble confirmación: <form data-doble-confirm="Mensaje 1|||Mensaje 2" data-confirm-boton="Eliminar">
        document.addEventListener('submit', async e => {
            const f = e.target.closest('form[data-doble-confirm]');
            if (!f || f.dataset.ok) return;
            e.preventDefault();
            const [m1, m2] = f.dataset.dobleConfirm.split('|||');
            const boton = f.dataset.confirmBoton || 'Confirmar';
            if (!await confirmar(m1, 'Confirmar acción (1 de 2)', 'Continuar')) return;
            if (!await confirmar(m2 || m1, 'Última confirmación (2 de 2)', boton)) return;
            f.dataset.ok = '1';
            f.requestSubmit ? f.requestSubmit() : f.submit();
        });

        // Pantalla de carga en todo envío POST (solo actúa si el envío sigue adelante)
        document.addEventListener('submit', e => {
            const f = e.target;
            if (e.defaultPrevented || !f.matches || !f.matches('form') || f.hasAttribute('data-sin-carga')) return;
            if ((f.method || 'get').toLowerCase() !== 'post' || (f.target && f.target !== '_self')) return;
            fondo.classList.add('activo');
            setTimeout(() => f.querySelectorAll('button[type=submit], button:not([type])').forEach(b => b.disabled = true), 0);
        });
        window.addEventListener('pageshow', e => {
            if (e.persisted) { fondo.classList.remove('activo'); document.querySelectorAll('form button:disabled').forEach(b => b.disabled = false); }
        });
    })();
</script>
