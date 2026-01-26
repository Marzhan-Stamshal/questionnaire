<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::orderBy('id', 'desc')->paginate(20);
        return view('admin.groups.index', compact('groups'));
    }

    public function create()
    {
        return view('admin.groups.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:groups,name',
            'faculty' => 'nullable|string|max:255',
            'program' => 'nullable|string|max:255',
            'course' => 'nullable|integer|min:1|max:7',
            'active' => 'nullable',
        ]);

        $data['active'] = $request->has('active');

        Group::create($data);

        return redirect()->route('admin.groups.index')->with('success', 'Группа добавлена ✅');
    }

    public function edit(Group $group)
    {
        return view('admin.groups.edit', compact('group'));
    }

    public function update(Request $request, Group $group)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:groups,name,' . $group->id,
            'faculty' => 'nullable|string|max:255',
            'program' => 'nullable|string|max:255',
            'course' => 'nullable|integer|min:1|max:7',
            'active' => 'nullable',
        ]);

        $data['active'] = $request->has('active');

        $group->update($data);

        return redirect()->route('admin.groups.index')->with('success', 'Группа обновлена ✅');
    }

    public function destroy(Group $group)
    {
        $group->delete();
        return redirect()->route('admin.groups.index')->with('success', 'Группа удалена ✅');
    }
}
