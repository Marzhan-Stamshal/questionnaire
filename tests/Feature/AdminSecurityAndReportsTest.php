<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Response;
use App\Models\RespondentSession;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminSecurityAndReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sensitive_report_denied_without_sensitive_permission(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'role' => 'admin',
            'can_view_sensitive_reports' => false,
        ]);

        $fixture = $this->createSurveyFixture();

        $response = $this->actingAs($admin)
            ->get(route('admin.reports.surveys.answers', $fixture['survey']->id));

        $response->assertStatus(403);
    }

    public function test_sensitive_report_allowed_with_sensitive_permission(): void
    {
        $admin = User::factory()->admin()->create();
        $fixture = $this->createSurveyFixture();

        $response = $this->actingAs($admin)
            ->get(route('admin.reports.surveys.answers', $fixture['survey']->id));

        $response->assertOk();
        $response->assertSee('Ответы по анкете', false);
    }

    public function test_admin_audit_log_is_created_for_admin_requests(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.reports.analytics.index', [
            'answer_mode' => 'yes',
            'search' => 'test',
        ]))->assertOk();

        $this->assertDatabaseHas('admin_audit_logs', [
            'user_id' => $admin->id,
            'route_name' => 'admin.reports.analytics.index',
            'method' => 'GET',
            'path' => 'admin/reports/analytics',
            'status_code' => 200,
        ]);
    }

    public function test_analytics_yes_filter_returns_only_yes_rows(): void
    {
        $admin = User::factory()->admin()->create();
        $fixture = $this->createSurveyFixture();

        Response::create([
            'respondent_session_id' => $fixture['session']->id,
            'survey_id' => $fixture['survey']->id,
            'group_id' => $fixture['group']->id,
            'question_id' => $fixture['question']->id,
            'teacher_id' => $fixture['teacher']->id,
            'value_int' => 1,
        ]);

        Response::create([
            'respondent_session_id' => $fixture['session']->id,
            'survey_id' => $fixture['survey']->id,
            'group_id' => $fixture['group']->id,
            'question_id' => $fixture['question']->id,
            'teacher_id' => $fixture['teacher']->id,
            'value_int' => 0,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.analytics.index', [
            'survey_id' => $fixture['survey']->id,
            'answer_mode' => 'yes',
        ]));

        $response->assertOk();
        $response->assertViewHas('rows', function ($rows) {
            return $rows->total() === 1;
        });
    }

    public function test_sensitive_export_csv_is_allowed_for_sensitive_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $fixture = $this->createSurveyFixture();

        Response::create([
            'respondent_session_id' => $fixture['session']->id,
            'survey_id' => $fixture['survey']->id,
            'group_id' => $fixture['group']->id,
            'question_id' => $fixture['question']->id,
            'teacher_id' => $fixture['teacher']->id,
            'value_int' => 1,
            'value_text' => 'Комментарий',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.exports.responses.csv', [
            'survey_id' => $fixture['survey']->id,
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    private function createSurveyFixture(): array
    {
        $group = Group::create([
            'name' => 'G-' . Str::upper(Str::random(6)),
            'kind' => 'cycle',
            'active' => true,
        ]);

        $teacher = Teacher::create([
            'fio' => 'Тестовый Преподаватель',
            'active' => true,
        ]);

        $template = SurveyTemplate::create([
            'title' => 'Тестовый шаблон',
            'is_active' => true,
        ]);

        $survey = Survey::create([
            'template_id' => $template->id,
            'group_id' => $group->id,
            'status' => 'active',
            'public_token' => Str::random(40),
            'year' => 2026,
            'semester' => '2',
        ]);

        $question = SurveyQuestion::create([
            'template_id' => $template->id,
            'code' => 'Q1',
            'text' => 'Тестовый yes/no вопрос',
            'type' => 'yes_no',
            'target' => 'teacher',
            'render_mode' => 'matrix',
            'sort_order' => 1,
            'is_required' => true,
            'is_active' => true,
        ]);

        TeachingAssignment::create([
            'group_id' => $group->id,
            'teacher_id' => $teacher->id,
            'semester' => '2',
            'year' => 2026,
        ]);

        $session = RespondentSession::create([
            'survey_id' => $survey->id,
            'group_id' => $group->id,
            'token_hash' => hash('sha256', Str::random(20)),
            'submitted_at' => now(),
        ]);

        return compact('group', 'teacher', 'template', 'survey', 'question', 'session');
    }
}

