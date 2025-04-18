<x-app-layout>
    <x-slot name="header">
        <h1 class="flex items-center gap-1 text-sm font-normal text-gray-700">
            {{ __('Tâches de la Vie Commune') }}
        </h1>
    </x-slot>

    <div class="py-6 px-4 max-w-7xl mx-auto">
        @if(session('success'))
            <div class="mb-4 bg-green-100 text-green-800 p-4 rounded-md">
                {{ session('success') }}
            </div>
        @endif

        @auth
            @if(Auth::user()->is_admin)
                <div class="mb-6">
                    <a href="{{ route('common-life.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        {{ __('Créer une Nouvelle Tâche') }}
                    </a>
                </div>
            @endif
        @endauth

        <div class="mb-10">
            <h2 class="text-lg font-semibold mb-4">Tâches À Faire</h2>

            @if($tasks->isEmpty())
                <p class="text-sm text-gray-500">Vous n'avez aucune tâche en attente 🎉</p>
            @else
                <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Titre</th>
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
                                    <form action="{{ route('common-life.complete', $task->id) }}" method="POST">
                                        @csrf
                                        <textarea name="comment" rows="2" class="w-full border rounded px-2 py-1 text-sm mb-2" placeholder="Ajouter un commentaire (facultatif)"></textarea>
                                        <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700">
                                            Marquer comme Terminée
                                        </button>
                                    </form>

                                    @auth
                                        @if(Auth::user()->is_admin)
                                            <div class="mt-4 flex gap-4">
                                                <a href="{{ route('common-life.edit', $task->id) }}" class="text-blue-600 hover:underline text-sm">
                                                    {{ __('Modifier') }}
                                                </a>

                                                <form action="{{ route('common-life.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:underline text-sm">
                                                        {{ __('Supprimer') }}
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

        <div>
            <h2 class="text-lg font-semibold mb-4">Vos Tâches Terminées</h2>

            @if($completedTasks->isEmpty())
                <p class="text-sm text-gray-500">Vous n'avez pas encore terminé de tâches.</p>
            @else
                <ul class="space-y-4">
                    @foreach($completedTasks as $task)
                        <li class="border p-4 rounded shadow-sm bg-gray-50">
                            <div class="font-bold">{{ $task->title }}</div>
                            <div class="text-sm text-gray-700">{{ $task->description }}</div>
                            <div class="text-xs text-gray-500 mt-1">
                                Terminée le {{ \Carbon\Carbon::parse($task->pivot->completed_at)->format('d/m/Y \à\ H:i') }}
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
