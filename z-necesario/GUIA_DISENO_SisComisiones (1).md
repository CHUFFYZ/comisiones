# Guía de diseño – SisComisiones (FCI · UNACAR)

Pega este documento al inicio de cualquier conversación o prompt para mantener el mismo diseño.

**Stack:** PHP + MariaDB, JS vanilla, **Bootstrap 5.3 (framework base de toda la interfaz)**, Font Awesome 6.5 (`fa-solid`), Google Fonts (Syne + DM Sans).

> **Regla principal:** todo componente nuevo se construye con clases de Bootstrap (grid, utilidades, botones, modales, formularios, tablas, badges, alertas, toasts, offcanvas). El CSS propio (`theme.css`) solo sobrescribe variables de Bootstrap para aplicar la identidad visual y define los pocos componentes que Bootstrap no tiene. **No se escribe CSS nuevo si ya existe una clase o utilidad de Bootstrap que lo resuelve.**

---

## 1. Identidad visual

- Estilo cálido y moderno: fondo crema, acentos en degradado naranja-durazno, textos azul marino muy oscuro.
- Esquinas muy redondeadas, sombras suaves, transiciones cortas (0.2s–0.3s).
- Botones y chips: pill (`rounded-pill`). Tarjetas 16px, modales 20px, inputs 10px.
- Interfaz en español (México). Etiquetas de formulario en MAYÚSCULAS pequeñas.

## 2. Carga de librerías (orden obligatorio)

```html
<!-- Fuentes -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet">

<!-- Iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Bootstrap 5.3 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Tema propio (SIEMPRE después de Bootstrap) -->
<link rel="stylesheet" href="../../css/theme.css">

<!-- Antes de </body> -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
```

Meta viewport obligatorio: `<meta name="viewport" content="width=device-width, initial-scale=1">`.
Los iconos siguen siendo **Font Awesome** (no usar Bootstrap Icons para mantener consistencia).

## 3. Paleta (tokens)

| Token | Valor | Uso |
|---|---|---|
| `--naranja` | `#ff7e5f` | Color de marca / primario |
| `--naranja2` | `#feb47b` | Inicio del degradado de marca |
| `--azul` | `#1a1a2e` | Texto principal, fondos oscuros |
| `--azul-m` | `#16213e` | Header secretaría, panel login |
| `--blanco` | `#f5f0eb` | Texto sobre fondo oscuro |
| `--fondo` | `#f0ebe4` | Fondo de página |
| `--gris` | `#9a9a9a` | Texto secundario, placeholders |
| `--gris-claro` | `#e8e2db` | Hover de botón secundario |
| `--rojo` | `#e84a5f` | No iniciado / error / peligro |
| `--amarillo` / `--amarillo-osc` | `#f9a825` / `#d68910` | En proceso / advertencia |
| `--verde` / `--verde-oscuro` | `#2ecc71` / `#27ae60` | Terminado / éxito |
| `--superficie-1` | `#fafaf9` | Fondo de inputs |
| `--superficie-2` | `#f8f4ef` | Cajas internas, stats, descripciones |
| `--superficie-3` | `#f5f0eb` | Botón secundario, buscador, selects |
| `--superficie-4` | `#fdf9f6` | Sub-formularios |

Colores auxiliares de badges: permiso 603 `#1a1a2e` · 602 `#c0552a` · 601 `#a07000` · Cuerpo Académico `#27ae60` · Academia `#2471a3`. Regla: fondo = color al 8–15 % de opacidad, texto = versión oscura.

## 4. `theme.css` – Bootstrap personalizado (archivo base del proyecto)

```css
:root {
    /* ── Marca propia ── */
    --naranja:#ff7e5f; --naranja2:#feb47b;
    --azul:#1a1a2e;    --azul-m:#16213e;
    --blanco:#f5f0eb;  --fondo:#f0ebe4;
    --gris:#9a9a9a;    --gris-claro:#e8e2db;
    --rojo:#e84a5f; --amarillo:#f9a825; --amarillo-osc:#d68910;
    --verde:#2ecc71; --verde-oscuro:#27ae60;
    --superficie-1:#fafaf9; --superficie-2:#f8f4ef;
    --superficie-3:#f5f0eb; --superficie-4:#fdf9f6;
    --degradado-marca:  linear-gradient(135deg, var(--naranja2), var(--naranja));
    --degradado-exito:  linear-gradient(135deg, var(--verde-oscuro), var(--verde));
    --sombra-marca: 0 6px 20px rgba(255,126,95,.40);
    --card-sombra:  0 4px 20px rgba(0,0,0,.08);

    /* ── Variables de Bootstrap sobrescritas ── */
    --bs-body-font-family: 'DM Sans', sans-serif;
    --bs-body-color: #333;
    --bs-body-bg: var(--fondo);
    --bs-heading-color: var(--azul);
    --bs-border-color: rgba(0,0,0,.08);
    --bs-border-radius: 10px;
    --bs-border-radius-lg: 16px;
    --bs-border-radius-xl: 20px;

    --bs-primary: #ff7e5f;   --bs-primary-rgb: 255,126,95;
    --bs-success: #27ae60;   --bs-success-rgb: 39,174,96;
    --bs-danger:  #e84a5f;   --bs-danger-rgb: 232,74,95;
    --bs-warning: #f9a825;   --bs-warning-rgb: 249,168,37;
    --bs-dark:    #1a1a2e;   --bs-dark-rgb: 26,26,46;
    --bs-link-color: var(--naranja);
    --bs-link-hover-color: #e8664a;
}

body { -webkit-font-smoothing: antialiased; min-height: 100svh; }
h1,h2,h3,h4,h5,h6,.font-syne { font-family:'Syne',sans-serif; font-weight:700; }
a { text-decoration:none; }

/* ── Botones: Bootstrap + estilo de marca ── */
.btn {
    --bs-btn-border-radius: 50rem;            /* pill */
    --bs-btn-padding-x: 1.5rem;
    --bs-btn-padding-y: .65rem;
    --bs-btn-font-size: .875rem;
    font-family:'Syne',sans-serif; font-weight:600;
    transition:all .2s;
}
.btn-primary {
    --bs-btn-color:#fff; --bs-btn-hover-color:#fff; --bs-btn-active-color:#fff;
    --bs-btn-border-color:transparent; --bs-btn-hover-border-color:transparent;
    --bs-btn-bg:transparent; --bs-btn-hover-bg:transparent;
    background-image: var(--degradado-marca);
}
.btn-primary:hover { filter:brightness(1.07); transform:translateY(-1px); box-shadow:var(--sombra-marca); }

.btn-success {
    --bs-btn-color:#fff; --bs-btn-hover-color:#fff;
    --bs-btn-border-color:transparent; --bs-btn-hover-border-color:transparent;
    --bs-btn-bg:transparent; --bs-btn-hover-bg:transparent;
    background-image: var(--degradado-exito);
}
.btn-success:hover { filter:brightness(1.07); }

.btn-danger { --bs-btn-bg:var(--rojo); --bs-btn-border-color:var(--rojo);
              --bs-btn-hover-bg:#d63e53; --bs-btn-hover-border-color:#d63e53; }

.btn-secondary {                               /* Cancelar / Cerrar */
    --bs-btn-color:#555; --bs-btn-bg:var(--superficie-3); --bs-btn-border-color:transparent;
    --bs-btn-hover-color:#555; --bs-btn-hover-bg:var(--gris-claro); --bs-btn-hover-border-color:transparent;
    font-family:'DM Sans',sans-serif; font-weight:500;
}
.btn-sm { --bs-btn-padding-x:.7rem; --bs-btn-padding-y:.3rem; --bs-btn-font-size:.72rem;
          font-family:'DM Sans',sans-serif; font-weight:500; }

/* Botón de tabla / tarjeta (ghost) */
.btn-ghost { --bs-btn-color:#555; --bs-btn-bg:#fff; --bs-btn-border-color:var(--bs-border-color);
             --bs-btn-hover-color:#555; --bs-btn-hover-bg:var(--superficie-3); --bs-btn-hover-border-color:var(--bs-border-color); }
.btn-ghost.is-danger:hover  { background:rgba(232,74,95,.08);  color:var(--rojo);         border-color:rgba(232,74,95,.2); }
.btn-ghost.is-success:hover { background:rgba(46,204,113,.10); color:var(--verde-oscuro); border-color:rgba(46,204,113,.3); }
.btn-ghost.is-brand { background-image:var(--degradado-marca); color:#fff; border-color:transparent; }
.btn-brand-soft { color:var(--naranja); background:rgba(255,126,95,.1); border:1px solid rgba(255,126,95,.2); }
.btn-brand-soft:hover { background:var(--naranja); color:#fff; }

.btn-logout { --bs-btn-color:rgba(255,255,255,.85); --bs-btn-bg:rgba(255,255,255,.1);
              --bs-btn-border-color:rgba(255,255,255,.15); --bs-btn-hover-bg:rgba(255,255,255,.22);
              --bs-btn-hover-color:#fff; --bs-btn-hover-border-color:rgba(255,255,255,.15);
              backdrop-filter:blur(4px); font-family:'DM Sans',sans-serif; }

/* ── Formularios ── */
.form-label { font-size:.75rem; font-weight:600; text-transform:uppercase;
              letter-spacing:.07em; color:var(--gris); margin-bottom:.4rem; }
.form-control, .form-select { background-color:var(--superficie-1); border:1.5px solid var(--bs-border-color);
              border-radius:10px; font-size:.84rem; padding:.7rem .9rem; color:var(--azul); }
.form-control:focus, .form-select:focus { background-color:#fff; border-color:var(--naranja);
              box-shadow:0 0 0 3px rgba(255,126,95,.1); }
.form-check-input:checked { background-color:var(--naranja); border-color:var(--naranja); }

/* ── Modal ── */
.modal-content { border:0; border-radius:20px; box-shadow:0 30px 80px rgba(0,0,0,.3); overflow:hidden; }
.modal-header, .modal-footer { border-color:var(--bs-border-color); padding:1.25rem 1.75rem; }
.modal-body { padding:1.5rem 1.75rem; }
.modal-title { font-family:'Syne',sans-serif; font-weight:700; font-size:1.25rem; color:var(--azul); }
.modal-header--marca { background:var(--degradado-marca); color:#fff; border:0; }   /* popup del profesor */
.modal-backdrop { --bs-backdrop-bg:#000; --bs-backdrop-opacity:.55; backdrop-filter:blur(4px); }

/* ── Tablas ── */
.table { --bs-table-bg:#fff; --bs-table-hover-bg:#fdf8f4; }
.table thead th { font-size:.7rem; font-weight:600; text-transform:uppercase;
                  letter-spacing:.07em; color:var(--gris); white-space:nowrap; background:#fff; }
.table td { font-size:.82rem; vertical-align:middle; }

/* ── Cards ── */
.card { border:1.5px solid transparent; border-radius:16px; box-shadow:var(--card-sombra);
        transition:transform .22s, box-shadow .22s, border-color .22s; }
.card-hover:hover { transform:translateY(-3px); box-shadow:0 12px 32px rgba(0,0,0,.13);
                    border-color:rgba(255,126,95,.25); cursor:pointer; }

/* ── Badges de estado (sobre .badge rounded-pill) ── */
.badge-estado { display:inline-flex; align-items:center; gap:5px; font-weight:500; font-size:.69rem; }
.badge-estado::before { content:''; width:7px; height:7px; border-radius:50%; background:currentColor; }
.estado-no-iniciado { background:rgba(232,74,95,.1);  color:var(--rojo); }
.estado-proceso     { background:rgba(249,168,37,.1); color:var(--amarillo-osc); }
.estado-terminado   { background:rgba(46,204,113,.1); color:var(--verde-oscuro); }
/* Para el punto del estado en amarillo/verde usar color: var(--amarillo)/var(--verde) en ::before si se desea */

/* ── Alertas y toasts ── */
.alert-danger { background:rgba(232,74,95,.1); border-color:rgba(232,74,95,.25); color:var(--rojo); border-radius:10px; font-size:.82rem; }
.toast { border:0; border-radius:12px; color:#fff; box-shadow:0 8px 30px rgba(0,0,0,.2); font-size:.82rem; font-weight:500; }
.toast.text-bg-success { background:var(--verde-oscuro)!important; }
.toast.text-bg-danger  { background:var(--rojo)!important; }

/* ── Headers ── */
.app-header { height:60px; position:sticky; top:0; z-index:1030; }
.app-header--oscuro  { background:linear-gradient(135deg,var(--azul-m),var(--azul)); box-shadow:0 4px 20px rgba(0,0,0,.25); }
.app-header--naranja { background:var(--degradado-marca); box-shadow:0 4px 20px rgba(255,126,95,.3); }
```

## 5. Tipografía

| Uso | Fuente | Pesos |
|---|---|---|
| Títulos, botones, números grandes, nombres de tarjeta, logos | **Syne** | 600 / 700 / 800 |
| Cuerpo, inputs, tablas, etiquetas | **DM Sans** | 300 / 400 / 500 |
| Folios / códigos | `font-monospace` (utilidad Bootstrap) | – |

Escala: 10–11px etiquetas y badges · 12–13px tablas y UI · 14px botones/texto · 16–20px títulos de modal/header · 22–36px stats y hero.
Usar utilidades: `fs-6`, `small`, `fw-semibold`, `text-uppercase`, `text-secondary`/`text-body-secondary`, `font-monospace`.

## 6. Layout con Bootstrap

- **Grid:** `container-fluid`, `row`, `col-*`, `g-3`. Breakpoints de Bootstrap (se reemplazan los personalizados): `sm 576` · `md 768` · `lg 992` · `xl 1200` · `xxl 1400`.
- **Header:** `<header class="app-header app-header--oscuro d-flex align-items-center justify-content-between px-3">` (secretaría = `--oscuro`, profesor = `--naranja`). Contiene logo cuadrado 36px, título Syne + subtítulo, círculos "FCI"/"U", nombre de usuario y `btn btn-logout btn-sm`.
- **Secretaría:** sidebar de 260px fijo en `lg+` (`d-none d-lg-flex flex-column`) y como **Offcanvas** en pantallas menores (`offcanvas-lg offcanvas-start`, botón hamburguesa en el header). Área principal = toolbar (`d-flex flex-wrap gap-2 align-items-center`) + `table-responsive`.
- **Profesor:** barra de filtros blanca + `row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3` con `card card-hover` por comisión.
- **Login:** fondo `--azul`, blobs borrosos y cuadrícula (CSS propio), tarjeta en dos paneles con `row g-0` (panel decorativo `col-md-6`, formulario `col-md-6`; en móvil el decorativo se reduce a una franja).
- Sin estilos inline: usar utilidades (`d-flex`, `gap-2`, `mb-3`, `text-center`, `py-5`, `w-100`, `rounded-pill`, `shadow-sm`, `bg-white`, `border-bottom`, `sticky-top`).

## 7. Sistema de botones (unificado)

| Acción | Clases Bootstrap |
|---|---|
| Guardar, Crear, Ingresar, Ver oficio, Nueva comisión | `btn btn-primary` (`btn-lg w-100` en login y sidebar) |
| Cancelar, Cerrar | `btn btn-secondary` |
| Constancias, Activar usuario | `btn btn-success` |
| Confirmar eliminar / suspender | `btn btn-danger` |
| Ver, Editar, Anular, Eliminar en tabla | `btn btn-ghost btn-sm` (+ `is-danger` en eliminar/anular, `is-brand` en PDF, `is-success` en activar) |
| Descargar en tarjeta | `btn btn-brand-soft btn-sm` |
| Salir (header) | `btn btn-logout btn-sm` |

**Regla de oro:** primario = naranja, éxito = verde, peligro = rojo sólido, todo lo demás = secundario o ghost. Prohibido definir degradados o colores inline en HTML; las acciones se colocan con `d-flex gap-2`.

## 8. Componentes (qué usar de Bootstrap y qué es propio)

| Componente | Implementación |
|---|---|
| **Modales** | `modal fade` + `modal-dialog modal-dialog-centered modal-dialog-scrollable` (`modal-sm` / `modal-lg` / `modal-xl`). Abrir/cerrar con `data-bs-toggle="modal"` o `bootstrap.Modal.getOrCreateInstance(el).show()/hide()`. **Reemplaza** la lógica manual de `.open`, bloqueo de scroll, Escape y clic en overlay. |
| **Toast** | `toast-container position-fixed bottom-0 end-0 p-3` + `.toast` con `text-bg-success` / `text-bg-danger`; mostrar con `bootstrap.Toast.getOrCreateInstance(el, {delay:3500}).show()`. Mantener la función `showToast(msg, type)`. |
| **Formularios** | `row g-3` + `col-md-6` (reemplaza `.form-row`), `form-label`, `form-control`, `form-select`, `input-group` (buscador, contraseña con ojo), `form-check`. Obligatorio: `<span class="text-danger">*</span>`. |
| **Tablas** | `table-responsive` + `table table-hover align-middle mb-0`. Ocultar columnas en móvil con `d-none d-md-table-cell`. |
| **Badges de estado** | `badge rounded-pill badge-estado estado-*` (ver tema). Activo/Suspendido reutilizan `estado-terminado` / `estado-no-iniciado`. |
| **Badges de rol/permiso/grupo** | `badge rounded-pill` con `bg-*-subtle text-*-emphasis` o clases `permiso-badge p601/602/603`. |
| **Alertas** | `alert alert-danger d-flex align-items-center gap-2` (con `fa-triangle-exclamation`). |
| **Búsqueda y filtros** | `input-group rounded-pill` para el buscador y `form-select rounded-pill w-auto` para filtros, fondo `--superficie-3`. |
| **Carga** | `spinner-border text-secondary` (reemplaza `fa-spinner fa-spin` en bloques de carga; en botones puede seguir el icono FA). |
| **Estado vacío** | `text-center py-5 text-body-secondary` + icono `fa-inbox` grande a 40 % de opacidad. |
| **Navegación lateral** | `nav flex-column nav-pills` con `nav-link` personalizado (activo: fondo `rgba(255,126,95,.1)` y texto naranja) y `badge` a la derecha (`ms-auto`). |
| **Dropdown / Tooltip** | Los de Bootstrap (`title` en botones de icono → `data-bs-toggle="tooltip"`). |

### Componentes propios (Bootstrap no los trae, se conservan)
- Fondo animado del login (`.blob`, `.bg-grid`).
- `.tipo-chip` (selector de tipo de comisión con icono y badge de permiso).
- `.estado-chip` (3 chips de estado en popup).
- Multi-selector de profesores (`.multi-prof-*`, `.prof-tag`, `.prof-item`).
- Opciones tipo tarjeta seleccionable (`.grupo-opcion`, `.permiso-opcion`).
- Avatares (`.usu-avatar`: profesor = degradado marca, secretaría = `linear-gradient(135deg,#4a6fa5,var(--azul-m))`) y pills de profesor (`.prof-pill`, principal en naranja con estrella).
- Barra lateral de 4px de color por estado en la tarjeta.
- Documentos imprimibles (oficio / constancia): **no usan Bootstrap**, mantienen su CSS independiente de hoja carta.

## 9. Mapa de migración (clase actual → Bootstrap)

| Actual | Bootstrap / nuevo |
|---|---|
| `.btn-primary` (propio) | `btn btn-primary` |
| `.btn-secundario` | `btn btn-secondary` |
| `.btn-accion` / `.danger` / `.primary` / `.success` | `btn btn-ghost btn-sm` + `is-danger` / `is-brand` / `is-success` |
| `.btn-descargar` | `btn btn-brand-soft btn-sm` |
| `.btn-nueva-comision`, `.btn-submit` | `btn btn-primary btn-lg w-100` |
| `.modal-overlay` / `.modal-box` / `.popup-overlay` / `.popup-box` | `modal fade` / `modal-content` |
| `.modal-header/body/footer` | iguales de Bootstrap |
| `.form-row` / `.form-row.full` | `row g-3` + `col-md-6` / `col-12` |
| `.form-campo label` | `form-label` |
| `.tabla-comisiones` | `table table-hover align-middle` dentro de `table-responsive` |
| `.cards-grid` + `.card` propio | `row row-cols-* g-3` + `card card-hover` |
| `.badge-estado` | `badge rounded-pill badge-estado` |
| `.alerta-error` | `alert alert-danger` |
| `.toast` propio | Bootstrap Toast |
| `.main-layout` / `.sidebar` / `.content-area` | `d-flex` + offcanvas-lg + `flex-grow-1` |
| `.header`, `.header--oscuro/--naranja` | `app-header app-header--*` |
| `.search-wrap`, `.filtro-select` | `input-group rounded-pill`, `form-select rounded-pill` |
| `.contador` | `ms-auto small text-body-secondary` |
| `display:none` por JS (`style.display`) | `d-none` / `classList.toggle('d-none')` |
| Media queries manuales | utilidades responsivas (`d-md-block`, `col-lg-*`, etc.) |

## 10. Convenciones de interacción y código

- Hover de botones: brillo + `translateY(-1px)`; tarjetas: `translateY(-3px)`.
- Transiciones 0.2s (UI) y 0.3s (modales/toasts); animación `popIn` de modales ya la cubre `modal fade`.
- Respuestas del servidor: `{ ok, data, msg }`. Mantener `showToast(msg,'success'|'error')` y escapar siempre con `escHTML()` al usar `innerHTML`.
- Fechas en UI `dd/mm/aa`; en documentos "5 de abril de 2025".
- Permisos: 601 (básico) · 602 (parcial) · 603 (total). Folios `FCI-COM-AAAA-MES-###` y `FCI-CONST-AAAA-MES-###`.
- Iconos recurrentes (FA): `fa-file-contract` (comisiones/logo), `fa-certificate` (constancias), `fa-users` / `fa-user` / `fa-star` (profesores/principal), `fa-chalkboard-user`, `fa-user-tie`, `fa-pen`, `fa-trash`, `fa-ban`, `fa-eye`, `fa-file-pdf`, `fa-magnifying-glass`, `fa-xmark`, `fa-floppy-disk`, `fa-plus`, `fa-right-from-bracket`.
- Estructura de archivos sugerida: `css/theme.css` (único CSS global, reemplaza `base.css` + `components.css`), más `css/login.css`, `css/secretaria.css`, `css/profesor.css` solo para componentes propios de la sección 8.

## 11. Reglas para quien continúe el diseño

1. Primero buscar la solución en Bootstrap (componente o utilidad); solo si no existe, crear CSS propio usando los tokens.
2. Nunca escribir colores hexadecimales sueltos: usar `var(--…)` o las variables `--bs-*`.
3. Nada de `style="…"` en el HTML salvo valores dinámicos imprescindibles.
4. Respetar la regla de botones de la sección 7 y la paleta de estados (rojo / amarillo / verde).
5. Mantener Syne para títulos y botones, DM Sans para el resto.
6. Todo debe verse bien en móvil (grid de Bootstrap, offcanvas, `table-responsive`).

## 12. Inconsistencias detectadas del código actual (resolver al migrar)

1. Verde del botón de constancia repetido inline (`#27ae60 → #2ecc71`): usar `btn-success`.
2. Más de diez variantes de botón: consolidadas en la sección 7.
3. Cremas hardcodeados (`#f5f0eb`, `#f8f4ef`, `#fafaf9`, `#fdf9f6`): ahora son tokens `--superficie-*`.
4. `.alerta-error` duplicada en `components.css` y `login.css`: usar `alert alert-danger` y ajustar solo el texto en login (`#ff9aa6`).
5. `base.css` tiene codificación rota en comentarios: guardar en UTF-8 (o eliminarlo al pasar a `theme.css`).
6. `index.css` está vacío: eliminar.
7. `.permiso-badge` y `.tipo-chip-badge` duplican colores: compartir una sola clase.
8. Breakpoints personalizados (480/680/768/1100): reemplazar por los de Bootstrap.

## 13. `estructura de carpetas` - carpetas
```css
```

## 14. `base de datos hata el momento` - asdadsaddsadadasb
```css

-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.3 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para cati
CREATE DATABASE IF NOT EXISTS `cati` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `cati`;

-- Volcando estructura para tabla cati.asignacion_permiso
CREATE TABLE IF NOT EXISTS `asignacion_permiso` (
  `Id` int unsigned NOT NULL AUTO_INCREMENT,
  `Clave` varchar(50) DEFAULT NULL,
  `Nombre` varchar(50) DEFAULT NULL,
  `Descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `IdUsuario` int NOT NULL,
  `IdTipoPermiso` int NOT NULL,
  `IdNivelAcceso` int NOT NULL,
  `IdTipoPermisoTemp` int DEFAULT NULL,
  `IdNivelAccesoTemp` int DEFAULT NULL,
  `IniPermisoTemp` datetime DEFAULT NULL,
  `FinPermisoTemp` datetime DEFAULT NULL,
  `Activo` tinyint DEFAULT NULL,
  `CreatedAt` datetime DEFAULT NULL,
  `UpdatedAt` datetime DEFAULT NULL,
  `DeletedAt` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) DEFAULT NULL,
  `UpdatedBy` varchar(50) DEFAULT NULL,
  `DeletedBy` varchar(50) DEFAULT NULL,
  `Versionn` int DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `FK_asignacion_permiso_usuario` (`IdUsuario`),
  KEY `FK_asignacion_permiso_nivel_acceso` (`IdNivelAcceso`),
  KEY `FK_asignacion_permiso_tipo_permiso` (`IdTipoPermiso`),
  KEY `FK_asignacion_permiso_tipo_permiso_2` (`IdTipoPermisoTemp`),
  KEY `FK_asignacion_permiso_nivel_acceso_2` (`IdNivelAccesoTemp`),
  CONSTRAINT `FK_asignacion_permiso_nivel_acceso` FOREIGN KEY (`IdNivelAcceso`) REFERENCES `nivel_acceso` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `FK_asignacion_permiso_nivel_acceso_2` FOREIGN KEY (`IdNivelAccesoTemp`) REFERENCES `nivel_acceso` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `FK_asignacion_permiso_tipo_permiso` FOREIGN KEY (`IdTipoPermiso`) REFERENCES `tipo_permiso` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `FK_asignacion_permiso_tipo_permiso_2` FOREIGN KEY (`IdTipoPermisoTemp`) REFERENCES `tipo_permiso` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `FK_asignacion_permiso_usuario` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.asignacion_permiso: ~5 rows (aproximadamente)
INSERT INTO `asignacion_permiso` (`Id`, `Clave`, `Nombre`, `Descripcion`, `IdUsuario`, `IdTipoPermiso`, `IdNivelAcceso`, `IdTipoPermisoTemp`, `IdNivelAccesoTemp`, `IniPermisoTemp`, `FinPermisoTemp`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`) VALUES
	(1, 'APPN', NULL, 'Permiso 1 prueba area nelly: Asigancion de permiso Parcial Nelly', 1, 4, 1, NULL, NULL, NULL, NULL, 1, '2026-10-04 01:32:26', '2026-10-04 01:32:26', NULL, 'admin', 'admin', NULL, 1),
	(2, 'APPE', NULL, 'Permiso 2 prueba area estrellita: Asigancion de permiso Parcial Estrellita', 2, 4, 2, NULL, NULL, NULL, NULL, 1, '2026-10-04 10:54:02', NULL, NULL, 'admin', NULL, NULL, 1),
	(3, 'APPS', NULL, 'Permiso 3 prueba Saide: Asigancion depermiso Parcial Saide', 3, 4, 3, NULL, NULL, NULL, NULL, 1, '2026-10-04 10:55:50', NULL, NULL, 'admin', NULL, NULL, 1),
	(4, 'APTSU', NULL, 'Permiso Total Super Usuario: asigancion de permiso Total Super Usuario', 5, 4, 4, NULL, NULL, NULL, NULL, 1, '2026-10-04 15:43:41', NULL, NULL, 'SUsuario', NULL, NULL, 1),
	(5, 'APPA', NULL, 'Permiso Total Admin: asigancion de permiso Parcial Administdor', 6, 4, 5, NULL, NULL, NULL, NULL, 1, '2026-10-04 15:52:43', NULL, NULL, 'SUsuario', NULL, NULL, 1);

-- Volcando estructura para tabla cati.facultad
CREATE TABLE IF NOT EXISTS `facultad` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Clave` varchar(50) DEFAULT NULL,
  `Nombre` varchar(50) DEFAULT NULL,
  `Descripcion` varchar(500) DEFAULT NULL,
  `Activo` tinyint DEFAULT NULL,
  `CreatedAt` datetime DEFAULT NULL,
  `UpdatedAt` datetime DEFAULT NULL,
  `DeletedAt` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) DEFAULT NULL,
  `UpdatedBy` varchar(50) DEFAULT NULL,
  `DeletedBy` varchar(50) DEFAULT NULL,
  `Versionn` int DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.facultad: ~3 rows (aproximadamente)
INSERT INTO `facultad` (`Id`, `Clave`, `Nombre`, `Descripcion`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`) VALUES
	(1, 'FCI', 'Facultad de Ciencias de la Informacion', 'Facultad de Ciencias de la Informacion', 1, '2026-10-04 15:07:44', NULL, NULL, 'admin', NULL, NULL, 1),
	(2, 'FCEA', 'Facultad de Ciencias Economico Administrativas', 'Facultad de Ciencias Economico Administrativas', 1, '2026-10-04 15:08:28', NULL, NULL, 'admin', NULL, NULL, 1),
	(3, 'FGPSU', 'Facultad global', 'Facultad global con permiso de super usuario y admin', 1, NULL, NULL, NULL, 'admin', NULL, NULL, 1);

-- Volcando estructura para tabla cati.modulo
CREATE TABLE IF NOT EXISTS `modulo` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Clave` varchar(50) DEFAULT NULL,
  `Nombre` varchar(50) DEFAULT NULL,
  `Descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `Activo` tinyint DEFAULT NULL,
  `CreatedAt` datetime DEFAULT NULL,
  `UpdatedAt` datetime DEFAULT NULL,
  `DeletedAt` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) DEFAULT NULL,
  `UpdatedBy` varchar(50) DEFAULT NULL,
  `DeletedBy` varchar(50) DEFAULT NULL,
  `Versionn` int DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.modulo: ~4 rows (aproximadamente)
INSERT INTO `modulo` (`Id`, `Clave`, `Nombre`, `Descripcion`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`) VALUES
	(1, 'MSC', 'SisComisiones', 'Modulo de Comisiones Para Profesores y Secretarias', 1, '2026-10-04 00:04:28', '2026-10-04 00:04:28', NULL, 'admin', 'admin', NULL, 1),
	(2, 'MP', 'CATI', 'Modulo principal para invitados y usuarios', 1, '2026-10-04 15:11:24', NULL, NULL, 'admin', NULL, NULL, 1),
	(3, 'MGSU', 'Global', 'Modulo Global Acceso a todos super usuario', 1, '2026-10-04 15:31:48', NULL, NULL, 'SUsuario', NULL, NULL, 1),
	(4, 'MA', 'Administracion', 'Modulo para el administrador', 1, '2026-10-04 15:35:10', NULL, NULL, 'SUsuario', NULL, NULL, 1);

-- Volcando estructura para tabla cati.nivel_acceso
CREATE TABLE IF NOT EXISTS `nivel_acceso` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Clave` varchar(50) DEFAULT NULL,
  `Nombre` varchar(50) DEFAULT NULL,
  `Descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `IdModulo` int NOT NULL,
  `Activo` tinyint DEFAULT NULL,
  `CreatedAt` datetime DEFAULT NULL,
  `UpdatedAt` datetime DEFAULT NULL,
  `DeletedAt` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) DEFAULT NULL,
  `UpdatedBy` varchar(50) DEFAULT NULL,
  `DeletedBy` varchar(50) DEFAULT NULL,
  `Versionn` int DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `FK_nivel_acceso_modulo` (`IdModulo`),
  CONSTRAINT `FK_nivel_acceso_modulo` FOREIGN KEY (`IdModulo`) REFERENCES `modulo` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.nivel_acceso: ~5 rows (aproximadamente)
INSERT INTO `nivel_acceso` (`Id`, `Clave`, `Nombre`, `Descripcion`, `IdModulo`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`) VALUES
	(1, 'MCNA1', 'MCNA1', 'Nivel de acceso 1 del modulo de comisiones', 1, 1, '2026-10-04 01:14:58', '2026-10-04 01:15:00', NULL, 'admin', 'admin', NULL, 1),
	(2, 'MCNA2', 'MCNA2', 'Nivel de acceso 2 del modulo de comisiones', 1, 1, '2026-10-04 01:17:18', '2026-10-04 01:17:20', NULL, 'admin', 'admin', NULL, 1),
	(3, 'MCNA3', 'MCNA3', 'Nivel de acceso 3 del modulo de comisiones', 1, 1, '2026-10-04 01:17:59', '2026-10-04 01:18:00', NULL, 'admin', 'admin', NULL, 1),
	(4, 'MGNATSU', 'MGNATSU', 'Nivel de acceso Super Usuario, Modulo Global Nivel de acceso Totao sUPER user', 3, 1, '2026-10-04 15:37:42', NULL, NULL, 'SUsuario', NULL, NULL, 1),
	(5, 'MANAT', 'MANAT', 'Nvel de acceso total del modulo de Administracion', 4, 1, '2026-10-04 15:40:00', NULL, NULL, 'SUsuario', NULL, NULL, 1);

-- Volcando estructura para tabla cati.tipo_permiso
CREATE TABLE IF NOT EXISTS `tipo_permiso` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Clave` varchar(50) DEFAULT NULL,
  `Nombre` varchar(50) DEFAULT NULL,
  `Descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `Activo` tinyint DEFAULT NULL,
  `CreatedAt` datetime DEFAULT NULL,
  `UpdatedAt` datetime DEFAULT NULL,
  `DeletedAt` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) DEFAULT NULL,
  `UpdatedBy` varchar(50) DEFAULT NULL,
  `DeletedBy` varchar(50) DEFAULT NULL,
  `Versionn` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.tipo_permiso: ~4 rows (aproximadamente)
INSERT INTO `tipo_permiso` (`Id`, `Clave`, `Nombre`, `Descripcion`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`) VALUES
	(1, NULL, 'R', 'Leer', 1, '2026-10-04 00:39:17', '2026-10-04 00:39:17', NULL, 'admin', 'admin', NULL, '1'),
	(2, NULL, 'CR', 'Crear y Leer', 1, '2026-10-04 00:43:23', '2026-10-04 00:43:23', NULL, 'admin', 'admin', NULL, '1'),
	(3, NULL, 'CRU', 'Crear, leer y actualizar', 1, '2026-10-04 00:45:05', '2026-10-04 00:45:05', NULL, 'admin', 'admin', NULL, '1'),
	(4, NULL, 'CRUD', 'Crear, leer, actualizar y eliminar', 1, '2026-10-04 00:45:29', '2026-10-04 00:45:29', NULL, 'admin', 'admin', NULL, '1');

-- Volcando estructura para tabla cati.usuario
CREATE TABLE IF NOT EXISTS `usuario` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(50) DEFAULT NULL,
  `Apellido` varchar(50) DEFAULT NULL,
  `Matricula` varchar(20) DEFAULT NULL,
  `Correo` varchar(50) DEFAULT NULL,
  `Contrasena` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `Rol` varchar(50) DEFAULT NULL,
  `GradoAcademico` varchar(50) DEFAULT NULL,
  `Genero` tinyint DEFAULT NULL,
  `IdFacultad` int DEFAULT NULL,
  `Activo` tinyint DEFAULT NULL,
  `CreatedAt` datetime DEFAULT NULL,
  `UpdatedAt` datetime DEFAULT NULL,
  `DeletedAt` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) DEFAULT NULL,
  `UpdatedBy` varchar(50) DEFAULT NULL,
  `DeletedBy` varchar(50) DEFAULT NULL,
  `Versionn` int DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `FK_usuario_facultad` (`IdFacultad`),
  CONSTRAINT `FK_usuario_facultad` FOREIGN KEY (`IdFacultad`) REFERENCES `facultad` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci CHECKSUM=1;

-- Volcando datos para la tabla cati.usuario: ~6 rows (aproximadamente)
INSERT INTO `usuario` (`Id`, `Nombre`, `Apellido`, `Matricula`, `Correo`, `Contrasena`, `Rol`, `GradoAcademico`, `Genero`, `IdFacultad`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`) VALUES
	(1, 'Juana', 'Guerra', 'JG1997', 'juani@gmail.com', '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 'Secretaria', 'Mtra', 1, 1, 1, '2026-10-03 23:46:57', '2026-10-03 23:46:57', NULL, 'admin', 'admin', NULL, 1),
	(2, 'Rosa', 'Guerra', 'RG1978', 'Rosy@gmail.com', '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 'Secretaria', 'Mtra', 1, 1, 1, '2026-10-03 23:46:57', '2026-10-04 01:11:12', NULL, 'admin', 'admin', NULL, 1),
	(3, 'Rosario', 'Guerra', 'RG1995', 'Rosarito@gmail.com', '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 'Secretaria', 'Dra', 1, 1, 1, '2026-10-03 23:46:57', '2026-10-03 23:46:57', NULL, 'admin', 'admin', NULL, 1),
	(4, 'Jose', 'Rejon', 'JR1970', 'jose@gmail.com', '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 'Profesor', 'Dr', 0, 1, 1, '2026-10-04 01:20:05', '2026-10-04 01:20:05', NULL, 'admin', 'admin', NULL, 1),
	(5, 'SUsuario', NULL, 'SU2003', 'cg7030295@gmail.com', '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 'SuperUsuario', 'Goat', 0, 3, 1, '2026-10-04 15:28:54', NULL, NULL, 'SUsuario', NULL, NULL, 1),
	(6, 'Administrador', '1', 'A2003', 'cg7030296@gmail.com', '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 'Administrador', 'Admin', 0, 1, 1, '2026-10-04 15:46:28', NULL, NULL, 'SUsuario', NULL, NULL, 1);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;


```