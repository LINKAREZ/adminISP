@extends('layouts.installer')

@section('content')
<div class="installer-card">
    <div class="steps">
        <span class="step active">1. Requisitos</span>
        <span class="step">2. Base de datos</span>
        <span class="step">3. Migraciones</span>
        <span class="step">4. Administrador</span>
    </div>

    <h2 style="margin-bottom: 1rem; font-size: 1.2rem;">Requisitos del sistema</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    @if(!$envExists)
        <div class="alert alert-warning">
            <strong>El archivo .env no existe.</strong> Si subiste los archivos por FTP, copia <code>.env.example</code> a <code>.env</code> en la raíz del proyecto, o haz clic en el botón para crearlo automáticamente.
        </div>
        <form method="POST" action="{{ route('installer.create-env') }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-block">Crear archivo .env</button>
        </form>
    @else
        @if(!$appKeyExists)
            <div class="alert alert-warning">
                <strong>APP_KEY no configurada.</strong> Generando...
            </div>
            <form method="POST" action="{{ route('installer.create-env') }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-block">Generar APP_KEY</button>
            </form>
        @endif

        @php $allOk = collect($requirements)->every(fn($r) => $r['ok']); @endphp
        @foreach($requirements as $key => $req)
            <div class="requirement-item">
                <span class="requirement-icon {{ $req['ok'] ? 'requirement-ok' : 'requirement-fail' }}">
                    @if($req['ok']) ✓ @else ✗ @endif
                </span>
                <span>{{ $req['message'] }}</span>
            </div>
        @endforeach

        @if($allOk)
            <a href="{{ route('installer.database') }}" class="btn btn-primary btn-block" style="margin-top: 1.5rem;">Continuar → Base de datos</a>
        @else
            <div class="alert alert-danger" style="margin-top: 1rem;">
                Corrige los requisitos marcados con ✗ antes de continuar.
            </div>
        @endif
    @endif
</div>
@endsection
