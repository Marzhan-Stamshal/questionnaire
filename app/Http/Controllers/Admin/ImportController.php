<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function index()
    {
        return view('admin.import.index');
    }

    private function readCsv($filePath)
    {
        $rows = [];
        $handle = fopen($filePath, 'r');

        // читаем с BOM нормально
        $firstLine = fgets($handle);
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
        $headers = str_getcsv(trim($firstLine));

        while (($line = fgets($handle)) !== false) {
            $data = str_getcsv(trim($line));
            if (count($data) !== count($headers)) continue;

            $rows[] = array_combine($headers, $data);
        }

        fclose($handle);
        return $rows;
    }

    public function importGroups(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $rows = $this->readCsv($request->file('file')->getRealPath());

        $created = 0;
        $updated = 0;

        foreach ($rows as $r) {
            $name = trim($r['name'] ?? '');
            if ($name === '') continue;

            $group = Group::where('name', $name)->first();

            $data = [
                'name' => $name,
                'faculty' => $r['faculty'] ?? null,
                'program' => $r['program'] ?? null,
                'course' => isset($r['course']) && $r['course'] !== '' ? (int)$r['course'] : null,
                'active' => isset($r['active']) ? (bool)$r['active'] : true,
            ];

            if ($group) {
                $group->update($data);
                $updated++;
            } else {
                Group::create($data);
                $created++;
            }
        }

        return back()->with('success', "Группы импортированы ✅ Создано: $created, обновлено: $updated");
    }

    public function importTeachers(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $rows = $this->readCsv($request->file('file')->getRealPath());

        $created = 0;
        $updated = 0;

        foreach ($rows as $r) {
            $fio = trim($r['fio'] ?? '');
            if ($fio === '') continue;

            $teacher = Teacher::where('fio', $fio)->first();

            $data = [
                'fio' => $fio,
                'department' => $r['department'] ?? null,
                'active' => isset($r['active']) ? (bool)$r['active'] : true,
            ];

            if ($teacher) {
                $teacher->update($data);
                $updated++;
            } else {
                Teacher::create($data);
                $created++;
            }
        }

        return back()->with('success', "Преподаватели импортированы ✅ Создано: $created, обновлено: $updated");
    }

    public function importAssignments(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $rows = $this->readCsv($request->file('file')->getRealPath());

        $created = 0;
        $skipped = 0;

        foreach ($rows as $r) {
            $groupName = trim($r['group_name'] ?? '');
            $teacherFio = trim($r['teacher_fio'] ?? '');

            if ($groupName === '' || $teacherFio === '') {
                $skipped++;
                continue;
            }

            $group = Group::where('name', $groupName)->first();
            $teacher = Teacher::where('fio', $teacherFio)->first();

            if (!$group || !$teacher) {
                $skipped++;
                continue;
            }

            $year = isset($r['year']) && $r['year'] !== '' ? (int)$r['year'] : null;
            $semester = $r['semester'] ?? null;

            $exists = TeachingAssignment::where('group_id', $group->id)
                ->where('teacher_id', $teacher->id)
                ->where('year', $year)
                ->where('semester', $semester)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            TeachingAssignment::create([
                'group_id' => $group->id,
                'teacher_id' => $teacher->id,
                'year' => $year,
                'semester' => $semester,
            ]);

            $created++;
        }

        return back()->with('success', "Связи импортированы ✅ Создано: $created, пропущено: $skipped");
    }
}
