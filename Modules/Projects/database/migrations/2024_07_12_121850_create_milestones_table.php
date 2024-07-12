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
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->code();
            $table->string('title');
            $table->string('description');
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->enum('status',['OPEN','CLOSED']);
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('finish_date')->nullable();
            $table->integer('progress')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};
