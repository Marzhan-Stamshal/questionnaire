<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\TeachingAssignmentController;
use App\Http\Controllers\Admin\SurveyTemplateController;
use App\Http\Controllers\Admin\SurveyController;
use App\Http\Controllers\PublicSurveyController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\SurveyQuestionController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\SurveySessionsController;
use App\Http\Controllers\Admin\TeacherReportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BulkSurveyController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AdminAuditLogController;


Route::middleware(['auth', 'admin', 'admin.audit'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.surveys.index');
    })->name('dashboard1');

    Route::resource('groups', GroupController::class);
    Route::resource('teachers', TeacherController::class);
    Route::resource('assignments', TeachingAssignmentController::class);


    Route::get('reports/teachers', [TeacherReportController::class, 'index'])->name('reports.teachers.index');
    Route::get('reports/teachers/{teacher}', [TeacherReportController::class, 'show'])->name('reports.teachers.show');
    Route::get('reports/analytics', [AnalyticsController::class, 'index'])->name('reports.analytics.index');

    Route::resource('templates', SurveyTemplateController::class)->except(['show']);
    Route::resource('surveys', SurveyController::class)->except(['show']);
    Route::post('templates/{template}/questions', [SurveyQuestionController::class, 'store'])->name('templates.questions.store');
    Route::put('questions/{question}', [SurveyQuestionController::class, 'update'])->name('questions.update');
    Route::delete('questions/{question}', [SurveyQuestionController::class, 'destroy'])->name('questions.destroy');
    Route::get('reports/teachers-export', [TeacherReportController::class, 'exportTeachersCsv'])
        ->name('reports.teachers.export');

    Route::get('reports/teachers/{teacher}/export', [TeacherReportController::class, 'exportTeacherDetailCsv'])
        ->name('reports.teachers.exportDetail');

    Route::middleware(['sensitive'])->group(function () {
        Route::get('audit/logs', [AdminAuditLogController::class, 'index'])->name('audit.logs.index');

        Route::get('reports/surveys/{survey}/export-raw', [\App\Http\Controllers\Admin\SurveyReportController::class, 'exportRaw'])
            ->name('reports.surveys.exportRaw');

        Route::get('reports/surveys/{survey}', [\App\Http\Controllers\Admin\SurveyReportController::class, 'show'])
            ->name('reports.surveys.show');
        Route::get('reports/surveys/{survey}/risks', [\App\Http\Controllers\Admin\SurveyReportController::class, 'risks'])
            ->name('reports.surveys.risks');
        Route::get('reports/surveys/{survey}/answers', [\App\Http\Controllers\Admin\SurveyReportController::class, 'answers'])
            ->name('reports.surveys.answers');
        Route::get('reports/surveys/{survey}/matrix', [\App\Http\Controllers\Admin\SurveyReportController::class, 'matrix'])
            ->name('reports.surveys.matrix');

        Route::get('reports/surveys/{survey}/teachers/{teacher}', [\App\Http\Controllers\Admin\SurveyReportController::class, 'teacherInSurvey'])
            ->name('reports.surveys.teacher');

        Route::get('reports/surveys/{survey}/comments', [\App\Http\Controllers\Admin\SurveyReportController::class, 'comments'])
            ->name('reports.surveys.comments');

        Route::get('reports/surveys/{survey}/matrix-export', [\App\Http\Controllers\Admin\SurveyReportController::class, 'exportMatrixCsv'])
            ->name('reports.surveys.exportMatrix');
    });


    Route::get('import', [ImportController::class, 'index'])->name('import.index');
    Route::get('import/template/{type}', [ImportController::class, 'downloadTemplate'])->name('import.template');

    Route::post('import/groups', [ImportController::class, 'importGroups'])->name('import.groups');
    Route::post('import/teachers', [ImportController::class, 'importTeachers'])->name('import.teachers');
    Route::post('import/assignments', [ImportController::class, 'importAssignments'])->name('import.assignments');
    Route::get('surveys/bulk-create', [BulkSurveyController::class, 'create'])->name('surveys.bulk.create');
    Route::post('surveys/bulk-create', [BulkSurveyController::class, 'store'])->name('surveys.bulk.store');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Route::get('reports/surveys/{survey}/sessions', [SurveySessionsController::class, 'index'])
    //     ->name('admin.reports.surveys.sessions');

    // Route::get('reports/surveys/{survey}/sessions/{session}', [SurveySessionsController::class, 'show'])
    //     ->name('admin.reports.surveys.sessions.show');
    Route::middleware(['sensitive'])->group(function () {
        Route::get('reports/surveys/{survey}/sessions', [SurveySessionsController::class, 'index'])
            ->name('reports.surveys.sessions');

        Route::get('reports/surveys/{survey}/sessions/{session}', [SurveySessionsController::class, 'show'])
            ->name('reports.surveys.sessions.show');
        Route::get('exports/responses.csv', [ExportController::class, 'responsesCsv'])
            ->name('exports.responses.csv');
    });
});

Route::get('/s/{token}', [PublicSurveyController::class, 'show'])->name('public.survey.show');
Route::post('/s/{token}', [PublicSurveyController::class, 'submit'])->name('public.survey.submit');

// Route::get('/survey', [PublicSurveyController::class, 'chooseGroup'])->name('public.survey.choose');
Route::post('/survey', [PublicSurveyController::class, 'goToSurvey'])->name('public.survey.goto');


Route::get('/survey', [PublicSurveyController::class, 'chooseGroup'])->name('public.chooseGroup');
Route::post('/survey/go', [PublicSurveyController::class, 'goToSurvey'])->name('public.goToSurvey');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/', fn() => redirect()->route('public.chooseGroup'));

Auth::routes();
