@extends('layouts.portal')
@section('title', 'Mis tickets')
@section('content')
<h1>Mis tickets</h1>
<p><a href="{{ route('portal.tickets.create') }}" class="btn btn-primary">Nuevo ticket</a></p>
<table>
    <thead><tr><th>#</th><th>Asunto</th><th>Estado</th><th></th></tr></thead>
    <tbody>
        @forelse($tickets as $t)
        <tr>
            <td>{{ $t->id }}</td>
            <td>{{ Str::limit($t->asunto, 50) }}</td>
            <td>{{ $t->estado }}</td>
            <td><a href="{{ route('portal.tickets.show', $t) }}">Ver</a></td>
        </tr>
        @empty
        <tr><td colspan="4">No tiene tickets.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $tickets->links() }}
<p><a href="{{ route('portal.dashboard') }}">Volver</a></p>
@endsection
