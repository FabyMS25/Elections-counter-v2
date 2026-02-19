<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voting_tables', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->integer('number');
            $table->string('from_name')->nullable();
            $table->string('to_name')->nullable();

            $table->integer('registered_citizens')->nullable();
            $table->integer('computed_records')->default(0); // Papeletas Computadas o en Anfora
            $table->integer('annulled_records')->default(0); // Papeletas Anuladas
            $table->integer('enabled_records')->default(0); // Papeletas Habilitadas         

            $table->enum('status', ['activo', 'cerrado', 'pendiente'])->default('pendiente');
            $table->foreignId('institution_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['institution_id', 'number']);
            $table->unique(['institution_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voting_tables');
    }
};