<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql';

    /**
     * Licencias asignadas a cada ISP (previo pago). Un ISP solo puede usar en sus routers
     * las licencias que estén en esta tabla.
     */
    public function up(): void
    {
        if (Schema::connection($this->connection)->hasTable('isp_plan')) {
            return;
        }
        Schema::connection($this->connection)->create('isp_plan', function (Blueprint $table) {
            $table->unsignedBigInteger('isp_id');
            $table->unsignedBigInteger('plan_id');
            $table->timestamps();
            $table->primary(['isp_id', 'plan_id']);
            $table->foreign('isp_id')->references('id')->on('isps')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('plans')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('isp_plan');
    }
};
