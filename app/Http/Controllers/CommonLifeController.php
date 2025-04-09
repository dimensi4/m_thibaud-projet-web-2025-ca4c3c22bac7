<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class CommonLifeController extends Controller
{
    public function index() {
        $tasks = Task::all();
        return view('pages.commonLife.index', compact('tasks'));
    }

    public function create() {
        return view('pages.commonLife.create');
    }

    public function store(Request $request) {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Task::create([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return redirect()->route('common-life.index')->with('success', 'Task created successfully.');
    }
}
