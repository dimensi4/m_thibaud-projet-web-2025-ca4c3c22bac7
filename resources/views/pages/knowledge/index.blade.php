<x-app-layout>
    <x-slot name="header">
        <h1 class="flex items-center gap-1 text-sm font-normal">
            <span class="text-gray-700">
                {{ __('Bilans de connaissances') }}
            </span>
        </h1>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <p class="mb-4 text-gray-600">{{ __('Bienvenue dans la section des bilans de connaissances. Choisissez un bilan pour commencer.') }}</p>

                    @if (auth()->check() && auth()->user()->is_admin)
                        <a href="{{ route('knowledge.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150 mt-4">
                            {{ __('Créer un nouveau bilan') }}
                        </a>
                    @endif

                    @if (session('success'))
                        <div class="mt-4 text-green-500">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="mt-4 text-yellow-500">
                            {{ session('warning') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mt-4 text-red-500">
                            {{ session('error') }}
                        </div>
                    @endif

                    <h2 class="text-xl font-semibold mt-6 mb-3 text-gray-800">{{ __('Bilans de Compétences Disponibles') }}</h2>

                    @if ($qcms->isNotEmpty())
                        <ul class="space-y-3">
                            @foreach ($qcms as $qcm)
                                <li class="bg-gray-100 rounded-md shadow-sm">
                                    @if (in_array($qcm->id, $completedQcms))
                                        <div class="block px-4 py-3 text-gray-500 font-medium line-through cursor-not-allowed">
                                            {{ $qcm->title }}
                                            <span class="text-sm text-gray-400">({{ $qcm->number_of_questions }} questions) - {{ __('Déjà complété') }}</span>
                                        </div>
                                    @else
                                        <a href="{{ route('knowledge.attempt', $qcm->id) }}" class="block px-4 py-3 text-indigo-600 font-medium hover:text-indigo-800 transition duration-200 ease-in-out">
                                            {{ $qcm->title }}
                                            <span class="text-sm text-gray-500">({{ $qcm->number_of_questions }} questions)</span>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-500">{{ __('Aucun bilan de compétences n\'est disponible pour le moment.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
