<x-app-layout>
    <x-slot name="header">
        <h1 class="flex items-center gap-1 text-sm font-normal">
            <span class="text-gray-700">
                {{ __('Vie Commune - Tasks List') }}
            </span>
        </h1>
    </x-slot>

    <div class="py-4">
        <a href="{{ route('tasks.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Create New Task</a>

        <div class="mt-6 bg-white shadow rounded p-4">
            @if (session('success'))
                <div class="mb-4 text-green-600">
                    {{ session('success') }}
                </div>
            @endif

            <table class="w-full text-left table-auto">
                <thead>
                <tr>
                    <th class="border-b p-2">Title</th>
                    <th class="border-b p-2">Description</th>
                    <th class="border-b p-2">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($tasks as $task)
                    <tr>
                        <td class="p-2">{{ $task->title }}</td>
                        <td class="p-2">{{ $task->description }}</td>
                        <td class="p-2 flex gap-2">
                            <a href="{{ route('tasks.edit', $task) }}" class="text-blue-500">Edit</a>
                            <form action="{{ route('tasks.destroy', $task) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-500" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
