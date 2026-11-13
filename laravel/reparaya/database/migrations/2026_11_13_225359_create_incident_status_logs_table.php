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
        $statuses = ['Pendiente', 'Asignada', 'En curso', 'Finalizada', 'Cancelada'];

        Schema::create('incident_status_logs', function (Blueprint $table) use ($statuses): void {
            $table->id();

            $table->foreignId('incident_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->enum('previous_status', $statuses)->nullable();
            $table->enum('new_status', $statuses);
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['incident_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_status_logs');
    }
};
