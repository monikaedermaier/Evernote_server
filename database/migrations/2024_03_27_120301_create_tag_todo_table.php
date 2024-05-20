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
        Schema::create('tag_todo', function (Blueprint $table) {
            //foreign key
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->foreignId('todo_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // define primary key (both foreignkeys)
            $table->primary(['tag_id', 'todo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_todo');
    }
};
