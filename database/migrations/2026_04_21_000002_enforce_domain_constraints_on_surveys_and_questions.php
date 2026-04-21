<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class EnforceDomainConstraintsOnSurveysAndQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('groups')
            ->whereNotIn('kind', ['cycle', 'group'])
            ->update(['kind' => 'cycle']);

        DB::table('survey_questions')
            ->whereNotIn('type', ['scale_0_10', 'yes_no', 'text', 'yes_no_with_text', 'single_choice', 'multiple_choice'])
            ->update(['type' => 'text']);

        DB::table('survey_questions')
            ->whereNotIn('target', ['survey', 'teacher'])
            ->update(['target' => 'survey']);

        DB::table('survey_questions')
            ->whereNotIn('render_mode', ['single', 'matrix', 'per_teacher'])
            ->update(['render_mode' => 'single']);

        DB::table('surveys')
            ->whereNotIn('status', ['draft', 'active', 'closed'])
            ->update(['status' => 'draft']);

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `groups` ADD CONSTRAINT `chk_groups_kind` CHECK (`kind` IN ('cycle','group'))");
            DB::statement("ALTER TABLE `survey_questions` ADD CONSTRAINT `chk_survey_questions_type` CHECK (`type` IN ('scale_0_10','yes_no','text','yes_no_with_text','single_choice','multiple_choice'))");
            DB::statement("ALTER TABLE `survey_questions` ADD CONSTRAINT `chk_survey_questions_target` CHECK (`target` IN ('survey','teacher'))");
            DB::statement("ALTER TABLE `survey_questions` ADD CONSTRAINT `chk_survey_questions_render_mode` CHECK (`render_mode` IN ('single','matrix','per_teacher'))");
            DB::statement("ALTER TABLE `surveys` ADD CONSTRAINT `chk_surveys_status` CHECK (`status` IN ('draft','active','closed'))");
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE groups ADD CONSTRAINT chk_groups_kind CHECK (kind IN ('cycle','group'))");
            DB::statement("ALTER TABLE survey_questions ADD CONSTRAINT chk_survey_questions_type CHECK (type IN ('scale_0_10','yes_no','text','yes_no_with_text','single_choice','multiple_choice'))");
            DB::statement("ALTER TABLE survey_questions ADD CONSTRAINT chk_survey_questions_target CHECK (target IN ('survey','teacher'))");
            DB::statement("ALTER TABLE survey_questions ADD CONSTRAINT chk_survey_questions_render_mode CHECK (render_mode IN ('single','matrix','per_teacher'))");
            DB::statement("ALTER TABLE surveys ADD CONSTRAINT chk_surveys_status CHECK (status IN ('draft','active','closed'))");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `groups` DROP CHECK `chk_groups_kind`");
            DB::statement("ALTER TABLE `survey_questions` DROP CHECK `chk_survey_questions_type`");
            DB::statement("ALTER TABLE `survey_questions` DROP CHECK `chk_survey_questions_target`");
            DB::statement("ALTER TABLE `survey_questions` DROP CHECK `chk_survey_questions_render_mode`");
            DB::statement("ALTER TABLE `surveys` DROP CHECK `chk_surveys_status`");
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE groups DROP CONSTRAINT IF EXISTS chk_groups_kind");
            DB::statement("ALTER TABLE survey_questions DROP CONSTRAINT IF EXISTS chk_survey_questions_type");
            DB::statement("ALTER TABLE survey_questions DROP CONSTRAINT IF EXISTS chk_survey_questions_target");
            DB::statement("ALTER TABLE survey_questions DROP CONSTRAINT IF EXISTS chk_survey_questions_render_mode");
            DB::statement("ALTER TABLE surveys DROP CONSTRAINT IF EXISTS chk_surveys_status");
        }
    }
}

