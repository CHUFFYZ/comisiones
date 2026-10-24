<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="es-MX" class="{{ auth()->check() ? 'con-menu' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CATI · FCI UNACAR')</title>

    <!-- Estado inicial del menú (antes de pintar para evitar parpadeo): abierto en escritorio salvo que se haya cerrado -->
    <script>
        (function () {
            try {
                if (matchMedia('(min-width:992px)').matches && localStorage.getItem('cati.menu') !== 'cerrado') {
                    document.documentElement.classList.add('menu-open');
                }
            } catch (e) {}
        })();
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet">

    <!-- Font Awesome 6.5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Tema Propio -->
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">

    @stack('styles')
</head>
<body>

    @auth
        @include('components.sidebar')
        <div class="menu-fondo" id="menuFondo"></div>
    @endauth

    <!-- Header + contenido + footer, a un costado del menú -->
    <div class="app-shell d-flex flex-column min-vh-100">
        @include('components.header')

        <main class="flex-grow-1 p-3 p-md-4 d-flex flex-column">
            @yield('content')
        </main>

        @include('components.footer')
    </div>

    <!-- Toast Notifications Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3 z-3">
        <div id="appToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Global Helper JS -->
    <script>
        function showToast(message, type = 'success') {
            const toastEl = document.getElementById('appToast');
            const toastMsg = document.getElementById('toastMessage');

            toastEl.className = 'toast align-items-center border-0 text-white ' + (type === 'success' ? 'text-bg-success' : 'text-bg-danger');
            toastMsg.textContent = message;

            bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3500 }).show();
        }

        // Menú lateral: botón hamburguesa (escritorio y móvil)
        (function () {
            const root = document.documentElement;
            const mq = matchMedia('(min-width:992px)');

            document.querySelectorAll('[data-menu-toggle]').forEach(b => b.addEventListener('click', () => {
                const abierto = root.classList.toggle('menu-open');
                if (mq.matches) { try { localStorage.setItem('cati.menu', abierto ? 'abierto' : 'cerrado'); } catch (e) {} }
            }));

            const fondo = document.getElementById('menuFondo');
            if (fondo) fondo.addEventListener('click', () => root.classList.remove('menu-open'));

            mq.addEventListener('change', e => {
                let cerrado = false;
                try { cerrado = localStorage.getItem('cati.menu') === 'cerrado'; } catch (x) {}
                root.classList.toggle('menu-open', e.matches && !cerrado);
            });
        })();

        // Filas clicables (data-href), sin interferir con botones, enlaces o formularios
        document.addEventListener('click', e => {
            const fila = e.target.closest('tr[data-href]');
            if (fila && !e.target.closest('a, button, form, input, select, label')) location.href = fila.dataset.href;
        });
    </script>
    <script>
        @if (session('ok')) showToast(@json(session('ok')), 'success'); @endif
        @if ($errors->has('general')) showToast(@json($errors->first('general')), 'error'); @endif
    </script>

    @stack('scripts')
</body>
</html>
