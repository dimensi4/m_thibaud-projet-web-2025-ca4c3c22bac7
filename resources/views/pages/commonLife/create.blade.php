<x-app-layout>
    <x-slot name="header">
        <h1 class="flex items-center gap-1 text-sm font-normal text-gray-700">
            {{ __('Create a Task') }}
        </h1>
    </x-slot>

    <div class="py-6 px-4 max-w-xl mx-auto">
        <form action="{{ route('common-life.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" id="title" class="w-full mt-1 border rounded px-3 py-2" required>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="4" class="w-full mt-1 border rounded px-3 py-2"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Save Task
                </button>
            </div>
        </form>
    </div>
</x-app-layout>

