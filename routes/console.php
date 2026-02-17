<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Generar recibos mensuales diariamente: cada día se generan solo para servicios con ese día de facturación
Schedule::command('recibos:generar-mensuales')
    ->dailyAt('00:00')
    ->timezone('America/Lima')
    ->description('Generar recibos mensuales (por día de facturación del servicio)');

// Actualizar promesas vencidas diariamente
Schedule::command('promesas:actualizar-vencidas')
    ->daily()
    ->timezone('America/Lima')
    ->description('Actualizar estado de promesas de pago vencidas');

// Cortar servicios con recibos pasados de fecha de corte (vencimiento + días de gracia)
Schedule::command('servicios:cortar-vencidos')
    ->dailyAt('00:30')
    ->timezone('America/Lima')
    ->description('Cortar servicios activos con deuda pasada de fecha de corte');

// Recordatorio de pago por correo (X días antes del vencimiento)
Schedule::command('recordatorio:enviar-correo')
    ->dailyAt('08:00')
    ->timezone('America/Lima')
    ->description('Enviar recordatorio por correo a clientes con recibo por vencer');
