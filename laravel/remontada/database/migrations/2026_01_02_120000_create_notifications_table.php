<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('month', 7); // YYYY-MM
            $table->string('type'); // sales_growth | margin_rate
            $table->string('severity'); // warning | critical
            $table->string('message');
            $table->decimal('value', 12, 2)->nullable();
            $table->decimal('threshold', 12, 2)->nullable();
            $table->timestamps();

            $table->index(['business_id', 'month']);
            $table->unique(['business_id', 'month', 'type', 'severity'], 'uniq_business_month_type_severity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
