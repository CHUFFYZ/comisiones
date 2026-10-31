@extends('layouts.app')

@section('title', 'Zona de super usuario · CATI')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-6 col-lg-4">
        <form method="POST" action="{{ route('su.validar') }}" class="card p-4" data-sin-carga>
            @csrf
            <div class="text-center mb-3">
                <div class="fs-1 text-primary"><i class="fa-solid fa-user-shield"></i></div>
                <h4 class="font-syne mb-1">Zona de super usuario</h4>
                <p class="text-muted small mb-0">Confirma tu contraseña para continuar. Aquí se reabren o eliminan comisiones cerradas.</p>
            </div>
            @error('password')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror
            <label class="form-label" for="password">Contraseña</label>
            <input type="password" id="password" name="password" class="form-control mb-3" autocomplete="current-password" autofocus required>
            <button class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>
</div>
@endsection
