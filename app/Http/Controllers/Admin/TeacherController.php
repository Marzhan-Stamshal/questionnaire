<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::orderBy('fio')->paginate(30);
        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('admin.teachers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fio' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'active' => 'nullable',
        ]);

        $data['active'] = $request->has('active');

        Teacher::create($data);

        return redirect()->route('admin.teachers.index')->with('success', 'Преподаватель добавлен ✅');
    }

    public function edit(Teacher $teacher)
    {
        return view('admin.teachers.edit', compact('teacher'));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $data = $request->validate([
            'fio' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'active' => 'nullable',
        ]);

        $data['active'] = $request->has('active');

        $teacher->update($data);

        return redirect()->route('admin.teachers.index')->with('success', 'Преподаватель обновлён ✅');
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->delete();
        return redirect()->route('admin.teachers.index')->with('success', 'Преподаватель удалён ✅');
    }
}
