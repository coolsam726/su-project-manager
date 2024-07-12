<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Projects\Support\Enums\ProjectStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->team();
            $table->code(uniquePerTeam: true);
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default(ProjectStatus::Backlog->value);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->float('progress')->default(0);
            $table->string('color')->nullable();
            $table->string('image')->nullable();
            $table->active();
            $table->audit();
            $table->nestedSet();
            $table->foreignId('project_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->bigInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
