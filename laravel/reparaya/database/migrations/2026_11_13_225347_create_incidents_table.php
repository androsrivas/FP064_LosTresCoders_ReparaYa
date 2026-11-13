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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();

            $table->foreignId('client_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('technician_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('speciality_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('community_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete();

            $table->text('description');
            $table->string('address', 255);
            $table->dateTime('service_date');
            $table->enum('urgency', ['Estándar', 'Urgente'])->default('Estándar');
            $table->enum('status', ['Pendiente', 'Asignada', 'En curso', 'Finalizada', 'Cancelada'])
                ->default('Pendiente');
            $table->dateTime('finished_at')->nullable();
            $table->timestamps();

            $table->index('service_date');
            $table->index(['status', 'finished_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
