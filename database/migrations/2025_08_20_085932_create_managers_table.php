<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('managers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('id_card')->nullable();
            $table->string('role')->default('presidente');
            $table->foreignId('voting_table_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'voting_table_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('managers');
    }
};
