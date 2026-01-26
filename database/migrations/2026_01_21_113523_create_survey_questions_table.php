<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSurveyQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('survey_templates')->cascadeOnDelete();

            $table->string('code')->nullable(); // Q1, Q2...
            $table->text('text');

            // scale_0_10 | yes_no | text | yes_no_with_text
            $table->string('type', 30);

            // teacher = нужен teacher_id, survey = общий (teacher_id NULL)
            $table->string('target', 20)->default('survey');

            // matrix | single | per_teacher
            $table->string('render_mode', 20)->default('single');

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['template_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('survey_questions');
    }
}
