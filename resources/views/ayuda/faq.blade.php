@extends('layouts.adminlte')

@section('title', 'Preguntas frecuentes')
@section('page-title', 'Preguntas frecuentes')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Ayuda', 'route' => 'dashboard'],
        ['label' => 'FAQ']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Preguntas frecuentes" icon="fa-question-circle" variant="info">
                <div class="accordion" id="accordionFaq">
                    <div class="card">
                        <div class="card-header" id="faq1">
                            <h6 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                                    ¿Cómo empiezo a configurar mi panel?
                                </button>
                            </h6>
                        </div>
                        <div id="collapse1" class="collapse show" aria-labelledby="faq1" data-parent="#accordionFaq">
                            <div class="card-body">
                                Sigue los pasos en el <a href="{{ route('dashboard') }}">Dashboard</a>: 1) Configura un router en Red > Routers, 2) Crea planes de internet en Servicios > Planes, 3) Registra tu primer cliente en Clientes.
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="faq2">
                            <h6 class="mb-0">
                                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                                    ¿Qué es PPPoE?
                                </button>
                            </h6>
                        </div>
                        <div id="collapse2" class="collapse" aria-labelledby="faq2" data-parent="#accordionFaq">
                            <div class="card-body">
                                PPPoE es el tipo de conexión habitual para fibra óptica. Cada cliente se identifica con un usuario y contraseña. El router MikroTik gestiona las conexiones PPPoE.
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="faq3">
                            <h6 class="mb-0">
                                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                    ¿Qué es una ONU?
                                </button>
                            </h6>
                        </div>
                        <div id="collapse3" class="collapse" aria-labelledby="faq3" data-parent="#accordionFaq">
                            <div class="card-body">
                                La ONU (Optical Network Unit) es el dispositivo que se instala en la casa del cliente para recibir la señal de fibra óptica y convertirla en conexión Ethernet/WiFi.
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="faq4">
                            <h6 class="mb-0">
                                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                                    ¿Dónde configuro los routers?
                                </button>
                            </h6>
                        </div>
                        <div id="collapse4" class="collapse" aria-labelledby="faq4" data-parent="#accordionFaq">
                            <div class="card-body">
                                En el menú lateral: <strong>Red</strong> > <strong>Routers</strong>. Desde ahí puedes añadir tus equipos MikroTik u otros routers PPPoE.
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="faq5">
                            <h6 class="mb-0">
                                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
                                    ¿Cómo genero recibos para los clientes?
                                </button>
                            </h6>
                        </div>
                        <div id="collapse5" class="collapse" aria-labelledby="faq5" data-parent="#accordionFaq">
                            <div class="card-body">
                                En <strong>Clientes</strong>, selecciona un router y usa el botón <strong>Crear Recibos</strong> para generar recibos masivos del mes. También puedes hacerlo desde <strong>Finanzas</strong>.
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection
