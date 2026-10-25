# Cambios en archivos EXISTENTES (buscar → reemplazar)

Orden: 1) importar `comisiones.sql`  2) ejecutar `database/cati_cambios_2.sql`  3) copiar los archivos del zip  4) aplicar lo de abajo.
Crear carpeta con permisos de escritura: `public/image/usuarios/`.

---
## bootstrap/app.php
Importar y registrar:
```php
use App\Http\Middleware\ActualizarConexion;
use App\Http\Middleware\GestionUsuarios;
...
$middleware->alias([
    'modulo.acceso'    => VerificarAccesoModulo::class,
    'gestion.usuarios' => GestionUsuarios::class,
]);
$middleware->appendToGroup('web', ActualizarConexion::class);
```

## routes/web.php
Imports:
```php
use App\Http\Controllers\Admin\PermisoController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\SimulacionController;
```
Dentro de `Route::middleware('auth')->group(...)`:
```php
Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil');
Route::post('/perfil/foto', [PerfilController::class, 'foto'])->name('perfil.foto');
Route::delete('/perfil/foto', [PerfilController::class, 'quitarFoto'])->name('perfil.foto.quitar');

Route::post('/simulacion', [SimulacionController::class, 'iniciar'])->name('simulacion.iniciar');
Route::delete('/simulacion', [SimulacionController::class, 'terminar'])->name('simulacion.terminar');

Route::middleware('gestion.usuarios')->prefix('administracion')->name('admin.')->where(['id' => '[0-9]+'])->group(function () {
    Route::get('usuarios',                [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::get('usuarios/crear',          [UsuarioController::class, 'create'])->name('usuarios.create');
    Route::post('usuarios',               [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::get('usuarios/bajas',          [UsuarioController::class, 'bajas'])->name('usuarios.bajas');
    Route::get('usuarios/{id}/editar',    [UsuarioController::class, 'edit'])->name('usuarios.edit');
    Route::put('usuarios/{id}',           [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::delete('usuarios/{id}',        [UsuarioController::class, 'baja'])->name('usuarios.baja');
    Route::patch('usuarios/{id}/restaurar',[UsuarioController::class, 'restaurar'])->name('usuarios.restaurar');
    Route::get('permisos',                [PermisoController::class, 'index'])->name('permisos.index');
    Route::post('permisos/{id}',          [PermisoController::class, 'store'])->name('permisos.store');
    Route::delete('permisos/{id}',        [PermisoController::class, 'destroy'])->name('permisos.destroy');
});
```
Dentro del grupo `chat` (antes de `/{id}/mensajes`):
```php
Route::get('/perfil/{id}', [ChatController::class, 'perfil'])->whereNumber('id')->name('perfil');
```

## config/modulos.php
Agregar dentro del array principal:
```php
'creables' => [
    'Administrador'       => ['SecretariaAcademica', 'Secretaria', 'Director', 'Profesor'],
    'SecretariaAcademica' => ['Secretaria'],
],
'asignables' => [   // módulos cuyos niveles pueden dar como permiso (temporal o base)
    'Administrador'       => ['MSC', 'MA'],
    'SecretariaAcademica' => ['MSC'],
],
```

## app/Services/AccesoService.php
Imports: `use App\Models\NivelAcceso; use App\Models\Rol; use Illuminate\Database\Eloquent\Builder;`
Agregar métodos (antes de `/* ───────── internos`):
```php
public function puedeGestionarUsuarios(?Usuario $u): bool
{
    return $u && ($this->esSuperUsuario($u) || in_array($u->Rol, ['Administrador', 'SecretariaAcademica'], true));
}

public function rolesCreables(Usuario $u): array
{
    return $this->esSuperUsuario($u)
        ? Rol::where('Activo', 1)->pluck('Clave')->all()
        : config('modulos.creables.' . $u->Rol, []);
}

/** SU: todos · Admin: su facultad · Sec. Académica: sus secretarias (de su facultad). */
public function usuariosGestionables(Usuario $u): Builder
{
    $q = Usuario::query();
    if (!$this->esSuperUsuario($u)) {
        $q->where('IdFacultad', $u->IdFacultad)->deRol($this->rolesCreables($u));
    }
    return $q;
}

public function nivelesAsignables(Usuario $u): Collection
{
    $n = NivelAcceso::with('modulo')->where('Activo', 1)->get();
    if ($this->esSuperUsuario($u)) return $n;
    $mods = config('modulos.asignables.' . $u->Rol, []);
    return $n->filter(fn ($x) => in_array($x->modulo?->Clave, $mods, true))->values();
}
```

## app/View/Composers/NavegacionComposer.php
Import `use App\Models\Usuario;`. En `$data = [...]` agregar:
```php
'puedeSimular' => false, 'simulado' => null, 'secretariasSim' => collect(), 'puedeGestion' => false,
```
Dentro de `if ($user) { ... }` agregar:
```php
$data['puedeGestion'] = $this->acceso->puedeGestionarUsuarios($user);
if ($this->acceso->esSuperUsuario($user)) {
    $data['puedeSimular']   = true;
    $data['simulado']       = ($sid = session('simulando')) ? Usuario::with('facultad')->find($sid) : null;
    $data['secretariasSim'] = Usuario::with('facultad')->where('Activo', 1)
        ->deRol(['Secretaria', 'SecretariaAcademica'])->orderBy('IdFacultad')->orderBy('Nombre')->get()
        ->groupBy(fn ($s) => $s->facultad->Clave ?? 'Sin facultad');
}
```

## resources/views/layouts/app.blade.php
1) Debajo de `<link rel="stylesheet" href="{{ asset('css/theme.css') }}">`:
```html
<link rel="stylesheet" href="{{ asset('css/cati-extra.css') }}">
```
2) Debajo de `@include('components.header')`:
```blade
@auth @include('components.simular') @endauth
```

## resources/views/components/header.blade.php
1) Reemplazar el bloque del avatar:
```blade
<div class="usu-avatar usu-avatar--sec">
    {{ strtoupper(substr(auth()->user()->Nombre ?? 'U', 0, 1)) }}
</div>
```
por:
```blade
@if (auth()->user()->foto_src)
    <img src="{{ auth()->user()->foto_src }}" alt="" class="usu-avatar">
@else
    <div class="usu-avatar usu-avatar--sec">{{ strtoupper(substr(auth()->user()->Nombre ?? 'U', 0, 1)) }}</div>
@endif
```
2) `{{ auth()->user()->Rol }}` → `{{ auth()->user()->rol_nombre }}`
3) Después del `<li>` de "Panel principal" agregar:
```blade
<li><a class="dropdown-item small" href="{{ route('perfil') }}"><i class="fa-solid fa-id-card w-20px"></i> Mi perfil</a></li>
@if ($puedeSimular)
    <li><button type="button" class="dropdown-item small" data-bs-toggle="modal" data-bs-target="#modalSimular">
        <i class="fa-solid fa-user-secret w-20px"></i> Simular secretaria</button></li>
@endif
```

## resources/views/components/sidebar.blade.php
Antes del `<li class="nav-item">` de "Mis módulos":
```blade
@if ($puedeGestion)
    <li class="nav-item mb-1">
        <a href="{{ route('admin.usuarios.index') }}" class="nav-link {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}">
            <i class="fa-solid fa-users w-20px"></i><span>Usuarios</span></a>
    </li>
    <li class="nav-item mb-1">
        <a href="{{ route('admin.permisos.index') }}" class="nav-link {{ request()->routeIs('admin.permisos.*') ? 'active' : '' }}">
            <i class="fa-solid fa-user-clock w-20px"></i><span>Permisos temporales</span></a>
    </li>
@endif
```

---
## Simulación del superusuario (actor)
En **ComisionController.php**, **ConstanciaController.php**: reemplazar TODAS las ocurrencias de `$r->user()` por `$this->actor($r)`.
En **ModuloController.php**: `estadisticas($user)` → `estadisticas($this->actor($request))`.

## Cierre propio + sin fecha por defecto

### ComisionController::validar — agregar regla
```php
'Cierre' => ['nullable', 'string', 'max:1000'],
```
### ConstanciaController::update
```php
$data = $r->validate([
    'Contexto' => 'required|string|min:10|max:1000',
    'Cierre'   => 'nullable|string|max:1000',
], [ ...mismos mensajes... ]);
...
$this->svc->actualizarConstancia($this->actor($r), $c, $data['Contexto'], $data['Cierre'] ?? null);
```

### ComisionService.php
1) `profesores()`: `Usuario::where('Rol', 'Profesor')->where('Activo', 1)` → `Usuario::deRol('Profesor')->where('Activo', 1)`
2) `snapshots()`: `Usuario::where('Rol', 'Director')->where('IdFacultad', $idFacultad)` → `Usuario::deRol('Director')->where('IdFacultad', $idFacultad)`
3) En `crear()` y `actualizar()`, debajo de `'Contexto' => $d['Contexto'],` agregar `'Cierre' => $this->cierre($d),`
4) En `previa()` reemplazar las 3 líneas Asunto/Contexto/FechaInicio por:
```php
'Asunto'      => $in['Asunto'] ?? '',
'Contexto'    => $in['Contexto'] ?? '',
'Cierre'      => $in['Cierre'] ?? '',
'FechaInicio' => !empty($in['FechaInicio']) ? $in['FechaInicio'] : null,   // sin fecha por defecto
```
5) En `crearConstancia()`, debajo de `'Contexto' => $c->Contexto,` agregar `'Cierre' => $plantilla->Cierre,`
6) `actualizarConstancia` completo cambia su firma y update:
```php
public function actualizarConstancia(Usuario $u, Comision $c, string $contexto, ?string $cierre = null): Constancia
...
$k->update([
    'Contexto' => $contexto,
    'Cierre'   => ($cierre !== null && trim($cierre) !== '') ? $cierre : $k->Cierre,
    'FechaElaboracion' => now(), 'UpdatedBy' => $u->Matricula,
]);
```
7) Nuevo método privado:
```php
/** Cierre capturado; si viene vacío, el de la plantilla. */
private function cierre(array $d): string
{
    return trim((string) ($d['Cierre'] ?? '')) !== ''
        ? trim($d['Cierre'])
        : (string) (PlantillaDocumento::find($d['IdPlantilla'])->Cierre ?? '');
}
```

### NotificacionService::secretaria()
`Usuario::where('Activo', 1)->where('IdFacultad', $c->IdFacultad)->where('Rol', 'SecretariaAcademica')->pluck('Id')` →
`Usuario::deRol('SecretariaAcademica')->where('Activo', 1)->where('IdFacultad', $c->IdFacultad)->pluck('Id')`

### DocumentoService.php
1) En `datos()` reemplazar la línea de `$cierre`:
```php
$f = $this->fechas($c);
$cierreTxt = trim((string) $doc->Cierre) !== '' ? (string) $doc->Cierre : (string) $p->Cierre;
$cierre = str_replace('{fechas}', $f, $cierreTxt);
if ($f === '') $cierre = preg_replace('/\s+([.,;])/u', '$1', $cierre);   // sin fecha: no se escribe nada
```
2) `fechas()`: reemplazar las dos primeras líneas por:
```php
if (!$c->FechaInicio) return '';
$i = $c->FechaInicio->copy()->locale('es');
$f = $c->FechaFin?->copy()->locale('es');
```

### comisiones/form.blade.php
1) En el `<option>` de plantilla agregar `data-cierre="{{ $p->Cierre }}"`.
2) Después del bloque del campo Contexto:
```blade
<div class="col-12">
    <label class="form-label" for="Cierre">Cierre</label>
    <textarea id="Cierre" name="Cierre" rows="3" maxlength="1000" class="form-control">{{ old('Cierre', $comision->Cierre ?? '') }}</textarea>
    <div class="form-text">Se propone el de la plantilla. Usa <code>{fechas}</code> donde deba ir la fecha; si no hay fecha, no se escribe nada.</div>
</div>
```
3) En el `<script>`, antes de `actualizarPrevia();` (última línea):
```js
const plant = document.getElementById('IdPlantilla'), cie = document.getElementById('Cierre');
let cierreSug = '';
function sugerirCierre() {
    const c = plant.selectedOptions[0]?.dataset.cierre || '';
    if (cie.value.trim() === '' || cie.value === cierreSug) { cie.value = c; cierreSug = c; }
}
plant.addEventListener('change', sugerirCierre);
sugerirCierre();
```
4) Quitar `required` de `FechaInicio` NO es necesario (la BD la exige); solo la vista previa deja de inventar fecha.

### comisiones/show.blade.php
1) Debajo del `<dt>` Contexto: `<dt class="col-sm-3 text-body-secondary">Cierre</dt><dd class="col-sm-9">{{ $c->Cierre ?: '—' }}</dd>`
2) En el modal `modalConstancia`, debajo del textarea `ContextoConst`:
```blade
<label class="form-label mt-3" for="CierreConst">Cierre de la constancia</label>
<textarea id="CierreConst" name="Cierre" rows="3" maxlength="1000" class="form-control">{{ $c->constancia->Cierre }}</textarea>
```
