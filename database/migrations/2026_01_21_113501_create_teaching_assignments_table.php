<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeachingAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();

            // опционально: если хотите учитывать период
            $table->string('semester')->nullable(); // "Fall", "Spring" или "2025-1"
            $table->unsignedSmallInteger('year')->nullable();

            $table->timestamps();

            $table->unique(['group_id', 'teacher_id', 'semester', 'year'], 'uq_assign');
            $table->index(['teacher_id']);
            $table->index(['group_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teaching_assignments');
    }
}
