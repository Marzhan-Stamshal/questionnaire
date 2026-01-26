<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSurveyQuestionOptionsTable extends Migration
{
    public function up()
    {
        Schema::create('survey_question_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('label');
            $table->string('value')->nullable(); // можно не заполнять, тогда = label
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('question_id')->references('id')->on('survey_questions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('survey_question_options');
    }
}
