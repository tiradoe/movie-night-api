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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('imdb_id')->unique();
            $table->year('year');
            $table->string('director');
            $table->text('actors');
            $table->text('plot');
            $table->string('genre');
            $table->string('mpaa_rating');
            $table->text('critic_scores');
            $table->text('poster');
            $table->foreignId('added_by')
                ->nullable()
                ->constrained(table: 'users', indexName: 'movies_added_by')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
