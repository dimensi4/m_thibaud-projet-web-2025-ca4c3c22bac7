<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // Display a listing of all tasks
    public function index()
    {
        $tasks = Task::all();
        return view('tasks.index', compact('tasks'));
    }

    // Show the form to create a new task
    public function create()
    {
        return view('tasks.create');
    }

    // Store a newly created task in the database
    public function store(Request $request)
    {
        // Validate the input fields
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Create a new task with the validated data
        Task::create($request->all());

        // Redirect to the task list with a success message
        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    // Show the form to edit an existing task
    public function edit(Task $task)
    {
        return view('tasks.edit', compact('task'));
    }

    // Update the task with new data
    public function update(Request $request, Task $task)
    {
        // Validate the input fields
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Update the task in the database
        $task->update($request->all());

        // Redirect to the task list with a success message
        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    // Delete a task from the database
    public function destroy(Task $task)
    {
        $task->delete();

        // Redirect to the task list with a success message
        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }
}
