<x-app-layout>
    <x-slot name="header">
        <h1 class="flex items-center gap-1 text-sm font-normal">
            <span class="text-gray-700">
                {{ __('Create New Task') }}
            </span>
        </h1>
    </x-slot>

    <div class="py-4">
        <form method="POST" action="{{ route('common-life.store') }}" class="bg-white shadow rounded p-4 max-w-md">
            @csrf

            <div class="mb-4">
                <label class="block text-sm text-gray-700">Title</label>
                <input type="text" name="title" class="w-full border rounded px-3 py-2 mt-1" value="{{ old('title') }}" required>
                @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm text-gray-700">Description</label>
                <textarea name="description" class="w-full border rounded px-3 py-2 mt-1">{{ old('description') }}</textarea>
                @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Save Task</button>
        </form>
    </div>
</x-app-layout>

