@extends('layouts.app')

@section('title', 'Iniciar sesión · CATI')

@section('content')
<div class="row justify-content-center align-items-center flex-grow-1">
    <div class="col-12 col-sm-10 col-md-6 col-lg-4">
        <div class="card p-4">
            <div class="text-center mb-4">
                <h3 class="font-syne mb-1">Iniciar sesión</h3>
                <p class="text-muted small mb-0">Accede con tu correo o matrícula</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger d-flex align-items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-12">
                    <label for="login" class="form-label">Correo o matrícula <span class="text-danger">*</span></label>
                    <input type="text" id="login" name="login" value="{{ old('login') }}"
                           class="form-control" autocomplete="username" autofocus required>
                </div>
                <div class="col-12">
                    <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                    <input type="password" id="password" name="password"
                           class="form-control" autocomplete="current-password" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-lg w-100">Ingresar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection