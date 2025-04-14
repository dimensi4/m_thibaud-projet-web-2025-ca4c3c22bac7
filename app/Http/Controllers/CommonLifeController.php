<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommonLifeController extends Controller
{
    /**
     * Display a listing of tasks.
     * Shows only tasks not yet completed by the current user.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // Get all tasks
        $allTasks = Task::all();

        // Get task IDs already completed by this user
        $completedTaskIds = $user->completedTasks()->pluck('tasks.id')->toArray();

        // Filter tasks to show only uncompleted ones
        $tasks = $allTasks->filter(fn($task) => !in_array($task->id, $completedTaskIds));

        // Get completed tasks with pivot data
        $completedTasks = $user->completedTasks()->get();

        return view('pages.commonLife.index', compact('tasks', 'completedTasks'));
    }

    /**
     * Show the form to create a new task (admin only).
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('pages.commonLife.create');
    }

    /**
     * Store a newly created task in the database (admin only).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Task::create($request->all());

        return redirect()->route('common-life.index')->with('success', 'Task created successfully.');
    }

    /**
     * Show the form to edit an existing task (admin only).
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\View\View
     */
    public function edit(Task $task)
    {
        return view('pages.commonLife.edit', compact('task'));
    }

    /**
     * Update the given task with new data (admin only).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $task->update($request->all());

        return redirect()->route('common-life.index')->with('success', 'Task updated successfully.');
    }

    /**
     * Delete the given task from the database (admin only).
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('common-life.index')->with('success', 'Task deleted successfully.');
    }

    /**
     * Mark a task as completed by the authenticated user.
     * Stores the comment and completion timestamp in the pivot table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsCompleted(Request $request, Task $task)
    {
        $request->validate([
            'comment' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        // Save the completion info in the pivot table
        $user->tasks()->syncWithoutDetaching([
            $task->id => [
                'completed_at' => now(),
                'comment' => $request->comment,
            ]
        ]);

        return redirect()->back()->with('success', 'Task marked as completed.');
    }
}
