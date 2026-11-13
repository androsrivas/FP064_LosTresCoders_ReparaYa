<?php
declare(strict_types=1);

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
        Schema::create('commissions', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('incident_id')
                ->unique()
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('management_company_id')
                ->constrained()
                ->restrictOnDelete();

            // Valores "congelados" cuando finaliza
            $table->decimal('base_price', 10, 2);
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('amount', 10, 2);
            $table->dateTime('finished_at');
            $table->timestamps();

            $table->index(['management_company_id', 'finished_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
