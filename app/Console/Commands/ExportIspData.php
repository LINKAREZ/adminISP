<?php

namespace App\Console\Commands;

use App\Modules\Sistema\Models\Isp;
use App\Modules\Sistema\Services\IspExportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportIspData extends Command
{
    protected $signature = 'isp:export {isp_id : ID del ISP a exportar}
                                    {--format=sql : Formato de exportación (sql, json)}';

    protected $description = 'Exportar todos los datos de un ISP específico';

    public function handle(IspExportService $service): int
    {
        $ispId = $this->argument('isp_id');
        $format = $this->option('format');

        $isp = Isp::withoutGlobalScope(\App\Core\Scopes\IspScope::class)->find($ispId);

        if (!$isp) {
            $this->error("ISP con ID {$ispId} no encontrado.");
            return 1;
        }

        $this->info("Exportando datos del ISP: {$isp->nombre}");

        if (!in_array($format, ['sql', 'json'], true)) {
            $this->error("Formato no válido: {$format}. Use 'sql' o 'json'.");
            return 1;
        }

        File::ensureDirectoryExists(storage_path('app/exports'));
        $filename = 'isp_' . $isp->id . '_' . now()->format('Y-m-d_His') . '.' . $format;
        $filepath = storage_path("app/exports/{$filename}");

        if ($format === 'sql') {
            File::put($filepath, $service->exportToSql($isp));
        } else {
            File::put($filepath, $service->exportToJson($isp));
        }

        $this->info("✅ Archivo creado: {$filepath}");
        return 0;
    }
}
