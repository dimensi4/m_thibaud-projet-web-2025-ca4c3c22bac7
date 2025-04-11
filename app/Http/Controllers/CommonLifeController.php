<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class CommonLifeController extends Controller
{
    // Show the list of all tasks (accessible to all authenticated users)
    public function index()
    {
        $tasks = Task::all();
        return view('pages.commonLife.index', compact('tasks'));
    }

    // Show the form to create a new task (admin only)
    public function create()
    {
        return view('pages.commonLife.create');
    }

    // Store a newly created task in the database (admin only)
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Task::create($request->all());

        return redirect()->route('common-life.index')->with('success', 'Task created successfully.');
    }

    // Show the form to edit an existing task (admin only)
    public function edit(Task $task)
    {
        return view('pages.commonLife.edit', compact('task'));
    }

    // Update the task with new data (admin only)
    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $task->update($request->all());

        return redirect()->route('common-life.index')->with('success', 'Task updated successfully.');
    }

    // Delete a task from the database (admin only)
    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('common-life.index')->with('success', 'Task deleted successfully.');
    }
}
