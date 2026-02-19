<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity')->default(0);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->foreignId('voting_table_id')->constrained()->onDelete('cascade');
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->foreignId('election_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['voting_table_id', 'candidate_id', 'election_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
