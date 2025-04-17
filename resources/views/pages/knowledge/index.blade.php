<x-app-layout>
    <x-slot name="header">
        <h1 class="flex items-center gap-1 text-sm font-normal">
            <span class="text-gray-700">
                {{ __('Bilans de connaissances') }}
            </span>
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <p>{{ __('Bienvenue dans la section de gestion des bilans de connaissances.') }}</p>
                    <a href="{{ route('knowledge.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150 mt-4">
                        {{ __('Créer un nouveau bilan de compétences') }}
                    </a>

                    @if (session('success'))
                        <div class="mt-4 text-green-500">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mt-4 text-red-500">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Vous pouvez ajouter ici une liste des bilans de compétences existants --}}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
