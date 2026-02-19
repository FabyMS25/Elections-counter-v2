<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('address')->nullable();
            // $table->foreignId('municipality_id')->nullable()->constrained()->onDelete('set null');            
            $table->foreignId('locality_id')->constrained('localities')->onDelete('cascade');
            $table->foreignId('district_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('zone_id')->nullable()->constrained()->onDelete('set null');
            
            $table->integer('registered_citizens')->nullable();
            $table->integer('total_computed_records')->default(0); // Total Actas Computadas
            $table->integer('total_annulled_records')->default(0); // Total Actas Anuladas  
            $table->integer('total_enabled_records')->default(0); // Total Actas Habilitadas
            
            $table->boolean('active')->default(true);
            $table->timestamps();
        });    }

    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
