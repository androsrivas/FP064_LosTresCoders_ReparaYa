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
        Schema::create('communities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('management_company_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('zone_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('name', 150);
            $table->string('address', 255);
            $table->timestamps();

            $table->unique(['management_company_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communities');
    }
};
