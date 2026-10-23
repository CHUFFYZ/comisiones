<!-- resources/views/publico/inicio.blade.php -->
@extends('layouts.app')

@section('title', 'Inicio · SisComisiones')

@section('content')
<div class="container-fluid p-0">
    <!-- Encabezado sutil coincidiendo con tu estilo -->
    <div class="mb-4">
        <h2 class="font-syne text-dark fw-bold mb-1" style="font-size: 2rem;">Gaceta Universitaria</h2>
        <p class="text-muted small mb-0">Boletín informativo y novedades institucionales de la FCI.</p>
    </div>

    <!-- Sección de Noticias (Estilo Tarjetas Limpias) -->
    <div class="row g-4">
        <!-- Noticia Principal (Estilo Banner Minimalista) -->
        <div class="col-lg-8">
            <div class="card h-100 border-0 p-4" style="border-radius: 16px; background-color: #fdfaf9; box-shadow: 0 4px 20px rgba(0,0,0,0.02);">
                <div class="card-body p-0 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge px-3 py-2 fw-semibold" style="background-color: #ffebeb; color: #ff6b6b; border-radius: 30px; font-size: 0.75rem;">CONVOCATORIA</span>
                            <span class="text-muted small"><i class="fa-solid fa-calendar-days me-1"></i> Octubre 2026</span>
                        </div>
                        <h4 class="font-syne fw-bold text-dark mb-3" style="line-height: 1.4;">Registro de Comisiones Académicas para el Periodo de Investigación</h4>
                        <p class="text-muted small mb-0" style="line-height: 1.6;">Se convoca a todo el cuerpo docente de la FCI a postular y registrar sus comitivas oficiales antes del cierre del presente mes académico mediante el módulo correspondiente.</p>
                    </div>
                    <div class="pt-4 border-top mt-4 d-flex justify-content-between align-items-center">
                        <span class="text-muted small fw-medium">Por: Secretaría Académica</span>
                        <a href="#" class="btn btn-sm px-4 fw-medium" style="background-color: #ffebeb; color: #ff6b6b; border-radius: 10px; font-size: 0.85rem;">Leer convocatoria</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Avisos Rápidos Laterales -->
        <div class="col-lg-4">
            <div class="card h-100 border-0" style="border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02);">
                <div class="card-header bg-transparent border-0 pt-4 px-4 font-syne fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 1.1rem;">
                    <span style="color: #ff6b6b;"><i class="fa-solid fa-bullhorn"></i></span> Avisos Importantes
                </div>
                <div class="card-body px-4 pb-4 pt-2">
                    <div class="list-group list-group-flush gap-3">
                        <div class="p-3 style-aviso" style="background-color: #fff9f9; border-radius: 12px; border-left: 4px solid #ff6b6b;">
                            <strong class="text-dark d-block mb-1 small fw-bold">⚠️ Mantenimiento de Plataforma</strong>
                            <p class="text-muted mb-0" style="font-size: 0.8rem;">La captura de constancias estará suspendida este viernes de 22:00 h a 02:00 h por actualizaciones.</p>
                        </div>
                        <div class="p-3 style-aviso" style="background-color: #f7f9fc; border-radius: 12px; border-left: 4px solid #4a90e2;">
                            <strong class="text-dark d-block mb-1 small fw-bold">🎓 Estímulos Docentes</strong>
                            <p class="text-muted mb-0" style="font-size: 0.8rem;">Revisa el calendario de validación para las actividades del módulo MSC en ventanilla.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Noticias Secundarias -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 p-4" style="border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02);">
                <div class="card-body p-0 d-flex flex-column justify-content-between">
                    <div>
                        <span class="badge mb-3 px-3 py-1.5 fw-semibold" style="background-color: #f0f2f5; color: #65676b; border-radius: 30px; font-size: 0.7rem;">FCI INFORMATIVO</span>
                        <h5 class="font-syne fw-bold text-dark mb-2">Nuevos Lineamientos de Viáticos</h5>
                        <p class="text-muted small mb-0" style="font-size: 0.85rem; line-height: 1.5;">Se han publicado los formatos estandarizados para la descarga y generación de reportes en PDF.</p>
                    </div>
                    <a href="#" class="btn btn-sm mt-4 fw-medium text-start p-0" style="color: #ff6b6b; font-size: 0.85rem;">Descargar formatos <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 p-4" style="border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02);">
                <div class="card-body p-0 d-flex flex-column justify-content-between">
                    <div>
                        <span class="badge mb-3 px-3 py-1.5 fw-semibold" style="background-color: #e3f2fd; color: #0d6efd; border-radius: 30px; font-size: 0.7rem;">VINCULACIÓN</span>
                        <h5 class="font-syne fw-bold text-dark mb-2">Congreso de Sistemas</h5>
                        <p class="text-muted small mb-0" style="font-size: 0.85rem; line-height: 1.5;">Docentes que requieran oficios de traslado, favor de generar su solicitud anticipadamente.</p>
                    </div>
                    <a href="#" class="btn btn-sm mt-4 fw-medium text-start p-0" style="color: #ff6b6b; font-size: 0.85rem;">Ver requisitos <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-lg-4">
            <div class="card h-100 border-0 p-4" style="border-radius: 16px; background-color: #ffebeb; box-shadow: 0 4px 20px rgba(0,0,0,0.01);">
                <div class="card-body p-0 d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="fw-bold font-syne text-dark mb-2" style="color: #ff6b6b !important;"><i class="fa-solid fa-circle-info me-2"></i>Soporte Técnico</h5>
                        <p class="small mb-0 text-dark opacity-75" style="font-size: 0.85rem; line-height: 1.5;">¿Problemas con los accesos o validaciones del sistema? Contacta de inmediato a la coordinación de desarrollo de la UNACAR.</p>
                    </div>
                    <div class="mt-4 pt-2 text-dark font-syne small fw-semibold">
                        <i class="fa-solid fa-phone me-1"></i> Extensiones: 4321, 4322
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
