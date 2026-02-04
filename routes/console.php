<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Generar recibos mensuales el día 1 de cada mes a las 00:00
Schedule::command('recibos:generar-mensuales')
    ->monthlyOn(1, '00:00')
    ->timezone('America/Lima')
    ->description('Generar recibos mensuales para todos los servicios activos');

// Actualizar promesas vencidas diariamente
Schedule::command('promesas:actualizar-vencidas')
    ->daily()
    ->timezone('America/Lima')
    ->description('Actualizar estado de promesas de pago vencidas');
