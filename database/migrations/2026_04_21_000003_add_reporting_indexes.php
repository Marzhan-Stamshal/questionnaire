<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddReportingIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createIndexIfMissing(
            'responses',
            'idx_responses_survey_question_teacher_int',
            ['survey_id', 'question_id', 'teacher_id', 'value_int']
        );

        $this->createIndexIfMissing(
            'responses',
            'idx_responses_survey_session',
            ['survey_id', 'respondent_session_id']
        );

        $this->createIndexIfMissing(
            'responses',
            'idx_responses_survey_created_at',
            ['survey_id', 'created_at']
        );

        $this->createIndexIfMissing(
            'responses',
            'idx_responses_question_teacher_survey',
            ['question_id', 'teacher_id', 'survey_id']
        );

        $this->createIndexIfMissing(
            'surveys',
            'idx_surveys_template_year_semester',
            ['template_id', 'year', 'semester']
        );

        $this->createIndexIfMissing(
            'surveys',
            'idx_surveys_group_year_semester',
            ['group_id', 'year', 'semester']
        );

        $this->createIndexIfMissing(
            'surveys',
            'idx_surveys_group_status_dates',
            ['group_id', 'status', 'starts_at', 'ends_at']
        );

        $this->createIndexIfMissing(
            'respondent_sessions',
            'idx_resp_sessions_survey_submitted_at',
            ['survey_id', 'submitted_at']
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropIndexIfExists('responses', 'idx_responses_survey_question_teacher_int');
        $this->dropIndexIfExists('responses', 'idx_responses_survey_session');
        $this->dropIndexIfExists('responses', 'idx_responses_survey_created_at');
        $this->dropIndexIfExists('responses', 'idx_responses_question_teacher_survey');

        $this->dropIndexIfExists('surveys', 'idx_surveys_template_year_semester');
        $this->dropIndexIfExists('surveys', 'idx_surveys_group_year_semester');
        $this->dropIndexIfExists('surveys', 'idx_surveys_group_status_dates');

        $this->dropIndexIfExists('respondent_sessions', 'idx_resp_sessions_survey_submitted_at');
    }

    private function createIndexIfMissing(string $table, string $index, array $columns): void
    {
        if ($this->indexExists($table, $index)) {
            return;
        }

        $driver = DB::getDriverName();
        $wrappedTable = $driver === 'pgsql' ? "\"$table\"" : "`$table`";
        $wrappedIndex = $driver === 'pgsql' ? "\"$index\"" : "`$index`";
        $wrappedColumns = implode(
            ', ',
            array_map(
                fn($c) => $driver === 'pgsql' ? "\"$c\"" : "`$c`",
                $columns
            )
        );

        DB::statement("CREATE INDEX $wrappedIndex ON $wrappedTable ($wrappedColumns)");
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (!$this->indexExists($table, $index)) {
            return;
        }

        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement("DROP INDEX IF EXISTS \"$index\"");
            return;
        }

        DB::statement("DROP INDEX `$index` ON `$table`");
    }

    private function indexExists(string $table, string $index): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $dbName = DB::getDatabaseName();
            $rows = DB::select(
                'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
                [$dbName, $table, $index]
            );
            return !empty($rows);
        }

        if ($driver === 'pgsql') {
            $rows = DB::select(
                'SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ? LIMIT 1',
                [$table, $index]
            );
            return !empty($rows);
        }

        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA index_list('$table')");
            foreach ($rows as $row) {
                if (($row->name ?? null) === $index) {
                    return true;
                }
            }
        }

        return false;
    }
}

