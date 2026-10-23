@extends('layouts.app')

@section('title', 'Constancias · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h2 class="font-syne text-dark mb-1">Constancias</h2>
        <p class="text-muted small mb-0">Comisiones finalizadas de tu facultad.</p>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr><th>Oficio</th><th>Asunto</th><th class="d-none d-md-table-cell">Profesores</th><th class="text-end">Constancia</th></tr>
                </thead>
                <tbody>
                @forelse ($comisiones as $c)
                    <tr>
                        <td class="font-monospace small">{{ $c->NOficio }}</td>
                        <td>{{ $c->Asunto }}</td>
                        <td class="d-none d-md-table-cell">{{ $c->profesores->pluck('nombre_completo')->join(', ') }}</td>
                        <td class="text-end">
                            @if ($c->constancia)
                                <a href="{{ route('constancias.pdf', $c->constancia->Id) }}" target="_blank" class="btn btn-ghost is-success btn-sm">
                                    <i class="fa-solid fa-certificate me-1"></i> Ver
                                </a>
                            @else
                                <form action="{{ route('constancias.store', $c->Id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-success btn-sm"><i class="fa-solid fa-certificate me-1"></i> Generar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-body-secondary">
                            <i class="fa-solid fa-inbox fs-1 opacity-50 mb-2 d-block"></i> No hay comisiones finalizadas.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($comisiones->hasPages())
            <div class="card-footer bg-white">{{ $comisiones->links() }}</div>
        @endif
    </div>
</div>
@endsection