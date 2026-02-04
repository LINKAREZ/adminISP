<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $path = database_path('schema/consolidated.sql');
        if (!file_exists($path)) {
            throw new RuntimeException('No se encontró consolidated.sql en database/schema.');
        }

        $sql = file_get_contents($path);
        $lines = preg_split('/\r\n|\r|\n/', $sql);
        $filtered = array_filter($lines, static function ($line) {
            $trimmed = trim($line);
            return $trimmed !== '' && !str_starts_with($trimmed, '--');
        });
        $sql = implode("\n", $filtered);
        $statements = array_filter(array_map('trim', preg_split('/;\s*\n/', $sql)));

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach ($statements as $statement) {
            if ($statement !== '') {
                DB::unprepared($statement . ';');
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down(): void
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        $tables = $connection->select('SHOW TABLES');
        $key = 'Tables_in_' . $database;

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach ($tables as $row) {
            $table = $row->$key ?? array_values((array) $row)[0] ?? null;
            if ($table && $table !== 'migrations') {
                DB::statement("DROP TABLE IF EXISTS `{$table}`");
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
