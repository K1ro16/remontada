<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->decimal('kpi_growth_warn', 6, 2)->default(10.00)->after('address');
            $table->decimal('kpi_growth_crit', 6, 2)->default(-10.00)->after('kpi_growth_warn');
            $table->decimal('kpi_margin_warn', 6, 2)->default(30.00)->after('kpi_growth_crit');
            $table->decimal('kpi_margin_crit', 6, 2)->default(10.00)->after('kpi_margin_warn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['kpi_growth_warn', 'kpi_growth_crit', 'kpi_margin_warn', 'kpi_margin_crit']);
        });
    }
};
