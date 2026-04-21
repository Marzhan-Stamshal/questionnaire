<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    private function normalizeGroupKind($value)
    {
        $kind = mb_strtolower(trim((string) $value));

        if ($kind === 'group' || $kind === 'группа') {
            return 'group';
        }

        if ($kind === 'cycle' || $kind === 'цикл') {
            return 'cycle';
        }

        return 'cycle';
    }

    public function index()
    {
        return view('admin.import.index');
    }

    public function downloadTemplate(string $type)
    {
        $filename = "template_{$type}.csv";

        if ($type === 'groups') {
            $headers = ['name', 'kind', 'faculty', 'program', 'course', 'active'];
            $sample = ['ВЕТ-21-01', 'cycle', 'Ветеринария', 'Ветмедицина', '2', '1'];
            return $this->streamCsv($filename, $headers, [$sample]);
        }

        if ($type === 'teachers') {
            $headers = ['fio', 'department', 'active'];
            $sample = ['Иванов И.И.', 'Кафедра ветеринарии', '1'];
            return $this->streamCsv($filename, $headers, [$sample]);
        }

        if ($type === 'assignments') {
            $headers = ['group_name', 'teacher_fio', 'year', 'semester'];
            $sample = ['ВЕТ-21-01', 'Иванов И.И.', '2026', '2'];
            return $this->streamCsv($filename, $headers, [$sample]);
        }

        abort(404);
    }

    private function streamCsv(string $filename, array $headers, array $rows)
    {
        $responseHeaders = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 200, $responseHeaders);
    }

    private function readCsv($filePath)
    {
        $rows = [];
        $errors = [];
        $handle = fopen($filePath, 'r');

        // читаем с BOM нормально
        $firstLine = fgets($handle);
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
        $headers = str_getcsv(trim($firstLine));
        $lineNo = 1;

        while (($line = fgets($handle)) !== false) {
            $lineNo++;
            $data = str_getcsv(trim($line));
            if (count($data) !== count($headers)) {
                $errors[] = [
                    'row' => $lineNo,
                    'message' => 'Количество колонок не совпадает с заголовком CSV',
                    'data' => trim($line),
                ];
                continue;
            }

            $row = array_combine($headers, $data);
            $row['__row'] = $lineNo;
            $rows[] = $row;
        }

        fclose($handle);
        return [
            'rows' => $rows,
            'errors' => $errors,
            'headers' => $headers,
        ];
    }

    private function boolFromCsv($value, bool $default = true): bool
    {
        if ($value === null || trim((string) $value) === '') {
            return $default;
        }

        $v = mb_strtolower(trim((string) $value));
        if (in_array($v, ['1', 'true', 'yes', 'y', 'да'], true)) return true;
        if (in_array($v, ['0', 'false', 'no', 'n', 'нет'], true)) return false;

        return $default;
    }

    private function buildImportMessage(string $entity, array $stats, bool $dryRun): string
    {
        $prefix = $dryRun ? 'Сухой запуск завершён ✅' : 'Импорт завершён ✅';
        return "{$entity}: {$prefix} Создано: {$stats['created']}, обновлено: {$stats['updated']}, пропущено: {$stats['skipped']}, ошибок: {$stats['error_rows']}";
    }

    public function importGroups(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $dryRun = $request->boolean('dry_run');
        $parsed = $this->readCsv($request->file('file')->getRealPath());
        $rows = $parsed['rows'];
        $errors = $parsed['errors'];

        $stats = [
            'total_rows' => count($rows),
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'error_rows' => count($errors),
        ];

        foreach ($rows as $r) {
            $name = trim($r['name'] ?? '');
            if ($name === '') {
                $stats['error_rows']++;
                $errors[] = [
                    'row' => $r['__row'] ?? null,
                    'message' => 'Пустое поле name',
                    'data' => $r,
                ];
                continue;
            }

            $group = Group::where('name', $name)->first();

            $data = [
                'name' => $name,
                'kind' => $this->normalizeGroupKind($r['kind'] ?? ($r['type'] ?? 'cycle')),
                'faculty' => ($r['faculty'] ?? '') !== '' ? $r['faculty'] : null,
                'program' => ($r['program'] ?? '') !== '' ? $r['program'] : null,
                'course' => isset($r['course']) && $r['course'] !== '' ? (int) $r['course'] : null,
                'active' => $this->boolFromCsv($r['active'] ?? null, true),
            ];

            if (!is_null($data['course']) && ($data['course'] < 1 || $data['course'] > 7)) {
                $stats['error_rows']++;
                $errors[] = [
                    'row' => $r['__row'] ?? null,
                    'message' => 'Некорректный course (допустимо 1..7)',
                    'data' => $r,
                ];
                continue;
            }

            if ($group) {
                if (!$dryRun) {
                    $group->update($data);
                }
                $stats['updated']++;
            } else {
                if (!$dryRun) {
                    Group::create($data);
                }
                $stats['created']++;
            }
        }

        return back()
            ->with('success', $this->buildImportMessage('Группы', $stats, $dryRun))
            ->with('import_report', [
                'entity' => 'groups',
                'dry_run' => $dryRun,
                'stats' => $stats,
                'errors' => array_slice($errors, 0, 200),
            ]);
    }

    public function importTeachers(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $dryRun = $request->boolean('dry_run');
        $parsed = $this->readCsv($request->file('file')->getRealPath());
        $rows = $parsed['rows'];
        $errors = $parsed['errors'];

        $stats = [
            'total_rows' => count($rows),
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'error_rows' => count($errors),
        ];

        foreach ($rows as $r) {
            $fio = trim($r['fio'] ?? '');
            if ($fio === '') {
                $stats['error_rows']++;
                $errors[] = [
                    'row' => $r['__row'] ?? null,
                    'message' => 'Пустое поле fio',
                    'data' => $r,
                ];
                continue;
            }

            $teacher = Teacher::where('fio', $fio)->first();

            $data = [
                'fio' => $fio,
                'department' => ($r['department'] ?? '') !== '' ? $r['department'] : null,
                'active' => $this->boolFromCsv($r['active'] ?? null, true),
            ];

            if ($teacher) {
                if (!$dryRun) {
                    $teacher->update($data);
                }
                $stats['updated']++;
            } else {
                if (!$dryRun) {
                    Teacher::create($data);
                }
                $stats['created']++;
            }
        }

        return back()
            ->with('success', $this->buildImportMessage('Преподаватели', $stats, $dryRun))
            ->with('import_report', [
                'entity' => 'teachers',
                'dry_run' => $dryRun,
                'stats' => $stats,
                'errors' => array_slice($errors, 0, 200),
            ]);
    }

    public function importAssignments(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $dryRun = $request->boolean('dry_run');
        $parsed = $this->readCsv($request->file('file')->getRealPath());
        $rows = $parsed['rows'];
        $errors = $parsed['errors'];

        $stats = [
            'total_rows' => count($rows),
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'error_rows' => count($errors),
        ];

        foreach ($rows as $r) {
            $groupName = trim($r['group_name'] ?? '');
            $teacherFio = trim($r['teacher_fio'] ?? '');

            if ($groupName === '' || $teacherFio === '') {
                $stats['error_rows']++;
                $errors[] = [
                    'row' => $r['__row'] ?? null,
                    'message' => 'Нужны group_name и teacher_fio',
                    'data' => $r,
                ];
                continue;
            }

            $group = Group::where('name', $groupName)->first();
            $teacher = Teacher::where('fio', $teacherFio)->first();

            if (!$group || !$teacher) {
                $stats['error_rows']++;
                $errors[] = [
                    'row' => $r['__row'] ?? null,
                    'message' => 'Группа или преподаватель не найдены',
                    'data' => $r,
                ];
                continue;
            }

            $year = isset($r['year']) && $r['year'] !== '' ? (int)$r['year'] : null;
            $semester = isset($r['semester']) && $r['semester'] !== '' ? trim($r['semester']) : null;

            $exists = TeachingAssignment::where('group_id', $group->id)
                ->where('teacher_id', $teacher->id)
                ->where('year', $year)
                ->where('semester', $semester)
                ->exists();

            if ($exists) {
                $stats['skipped']++;
                continue;
            }

            if (!$dryRun) {
                TeachingAssignment::create([
                    'group_id' => $group->id,
                    'teacher_id' => $teacher->id,
                    'year' => $year,
                    'semester' => $semester,
                ]);
            }

            $stats['created']++;
        }

        return back()
            ->with('success', $this->buildImportMessage('Связи', $stats, $dryRun))
            ->with('import_report', [
                'entity' => 'assignments',
                'dry_run' => $dryRun,
                'stats' => $stats,
                'errors' => array_slice($errors, 0, 200),
            ]);
    }
}
