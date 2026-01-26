<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class TeachingAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $groupId = $request->get('group_id');
        $teacherId = $request->get('teacher_id');
        $year = $request->get('year');
        $semester = $request->get('semester');

        $q = TeachingAssignment::with(['group', 'teacher'])->orderByDesc('id');

        if ($groupId) $q->where('group_id', (int)$groupId);
        if ($teacherId) $q->where('teacher_id', (int)$teacherId);
        if ($year !== null && $year !== '') $q->where('year', (int)$year);
        if ($semester !== null && $semester !== '') $q->where('semester', $semester);

        $assignments = $q->paginate(30)->withQueryString();

        $groups = Group::orderBy('name')->get();
        $teachers = Teacher::orderBy('fio')->get();

        return view('admin.assignments.index', compact(
            'assignments',
            'groups',
            'teachers',
            'groupId',
            'teacherId',
            'year',
            'semester'
        ));
    }

    public function create()
    {
        $groups = Group::orderBy('name')->get();
        $teachers = Teacher::orderBy('fio')->get();
        return view('admin.assignments.create', compact('groups', 'teachers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'teacher_id' => 'required|exists:teachers,id',
            'year' => 'nullable|integer|min:2000|max:2100',
            'semester' => 'nullable|string|max:50',
        ]);

        $exists = TeachingAssignment::where('group_id', $data['group_id'])
            ->where('teacher_id', $data['teacher_id'])
            ->where('year', $data['year'] ?? null)
            ->where('semester', $data['semester'] ?? null)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Такая связь уже существует')->withInput();
        }

        TeachingAssignment::create($data);

        return redirect()->route('admin.assignments.index')->with('success', 'Связь добавлена ✅');
    }

    public function edit(TeachingAssignment $assignment)
    {
        $groups = Group::orderBy('name')->get();
        $teachers = Teacher::orderBy('fio')->get();
        return view('admin.assignments.edit', compact('assignment', 'groups', 'teachers'));
    }

    public function update(Request $request, TeachingAssignment $assignment)
    {
        $data = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'teacher_id' => 'required|exists:teachers,id',
            'year' => 'nullable|integer|min:2000|max:2100',
            'semester' => 'nullable|string|max:50',
        ]);

        $exists = TeachingAssignment::where('group_id', $data['group_id'])
            ->where('teacher_id', $data['teacher_id'])
            ->where('year', $data['year'] ?? null)
            ->where('semester', $data['semester'] ?? null)
            ->where('id', '!=', $assignment->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Такая связь уже существует')->withInput();
        }

        $assignment->update($data);

        return redirect()->route('admin.assignments.index')->with('success', 'Связь обновлена ✅');
    }

    public function destroy(TeachingAssignment $assignment)
    {
        $assignment->delete();
        return redirect()->route('admin.assignments.index')->with('success', 'Связь удалена ✅');
    }
}
