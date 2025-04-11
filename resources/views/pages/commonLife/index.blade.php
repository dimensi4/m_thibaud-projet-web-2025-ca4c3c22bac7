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

        <!-- Task Table -->
        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Title') }}
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Description') }}
                    </th>
                    @auth
                        @if(Auth::user()->is_admin)
                            <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Actions') }}
                            </th>
                        @endif
                    @endauth
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @foreach($tasks as $task)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $task->title }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $task->description }}
                        </td>
                        @auth
                            @if(Auth::user()->is_admin)
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <!-- Edit Button -->
                                    <a href="{{ route('common-life.edit', $task->id) }}" class="text-blue-600 hover:text-blue-900">
                                        {{ __('Edit') }}
                                    </a>

                                    <!-- Delete Button -->
                                    <form action="{{ route('common-life.destroy', $task->id) }}" method="POST" class="inline-block ml-4">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                </td>
                            @endif
                        @endauth
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
