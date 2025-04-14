<x-app-layout>
    <x-slot name="header">
        <h1 class="flex items-center gap-1 text-sm font-normal text-gray-700">
            {{ __('Common Life Tasks') }}
        </h1>
    </x-slot>

    <div class="py-6 px-4 max-w-7xl mx-auto">
        <!-- Success message display -->
        @if(session('success'))
            <div class="mb-4 bg-green-100 text-green-800 p-4 rounded-md">
                {{ session('success') }}
            </div>
        @endif

        <!-- Create New Task Button (admin only) -->
        @auth
            @if(Auth::user()->is_admin)
                <div class="mb-6">
                    <a href="{{ route('common-life.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        {{ __('Create a New Task') }}
                    </a>
                </div>
            @endif
        @endauth

        <!-- Tasks To Do -->
        <div class="mb-10">
            <h2 class="text-lg font-semibold mb-4">Tasks To Do</h2>

            @if($tasks->isEmpty())
                <p class="text-sm text-gray-500">You have no pending tasks 🎉</p>
            @else
                <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Title</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @foreach($tasks as $task)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $task->title }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $task->description }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <!-- Complete Task Form -->
                                    <form action="{{ route('common-life.complete', $task->id) }}" method="POST">
                                        @csrf
                                        <textarea name="comment" rows="2" class="w-full border rounded px-2 py-1 text-sm mb-2" placeholder="Add a comment (optional)"></textarea>
                                        <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700">
                                            Mark as Completed
                                        </button>
                                    </form>

                                    <!-- Admin actions -->
                                    @auth
                                        @if(Auth::user()->is_admin)
                                            <div class="mt-4 flex gap-4">
                                                <!-- Edit Button -->
                                                <a href="{{ route('common-life.edit', $task->id) }}" class="text-blue-600 hover:underline text-sm">
                                                    {{ __('Edit') }}
                                                </a>

                                                <!-- Delete Button -->
                                                <form action="{{ route('common-life.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this task?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:underline text-sm">
                                                        {{ __('Delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    @endauth
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Completed Tasks History -->
        <div>
            <h2 class="text-lg font-semibold mb-4">Your Completed Tasks</h2>

            @if($completedTasks->isEmpty())
                <p class="text-sm text-gray-500">You haven't completed any tasks yet.</p>
            @else
                <ul class="space-y-4">
                    @foreach($completedTasks as $task)
                        <li class="border p-4 rounded shadow-sm bg-gray-50">
                            <div class="font-bold">{{ $task->title }}</div>
                            <div class="text-sm text-gray-700">{{ $task->description }}</div>
                            <div class="text-xs text-gray-500 mt-1">
                                Completed on {{ \Carbon\Carbon::parse($task->pivot->completed_at)->format('d/m/Y \a\t H:i') }}
                            </div>
                            @if($task->pivot->comment)
                                <div class="mt-2 italic text-sm text-gray-600">"{{ $task->pivot->comment }}"</div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-app-layout>
