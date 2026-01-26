<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('respondent_session_id')->constrained('respondent_sessions')->cascadeOnDelete();
            $table->foreignId('survey_id')->constrained('surveys')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();

            $table->foreignId('question_id')->constrained('survey_questions')->cascadeOnDelete();

            // NULL для общих вопросов, заполнено для вопросов про преподавателя
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();

            $table->integer('value_int')->nullable(); // 0-10 или yes/no
            $table->text('value_text')->nullable();   // комментарии

            $table->timestamps();

            $table->index(['survey_id']);
            $table->index(['teacher_id', 'question_id']);
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
        Schema::dropIfExists('responses');
    }
}
