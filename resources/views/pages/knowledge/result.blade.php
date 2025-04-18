<x-app-layout>
    <x-slot name="header">
        <h1 class="flex items-center gap-1 text-sm font-normal">
            <a href="{{ route('knowledge.index') }}" class="text-gray-500 hover:text-gray-700 transition duration-150 ease-in-out">
                {{ __('Bilans de connaissances') }}
            </a>
            <svg class="flex-shrink-0 w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="text-gray-700">{{ __('Résultat :') }} {{ $qcm->title }}</span>
        </h1>
    </x-slot>

    <div class="py-8">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200 text-center">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ __('Résultat du bilan :') }} {{ $qcm->title }}</h2>
                    <p class="mb-2 text-lg text-gray-700">{{ __('Score :') }} <span class="font-semibold">{{ $score }}</span> / {{ $totalQuestions }}</p>
                    <p class="text-2xl font-bold text-indigo-600">{{ number_format($note, 2) }} / 20</p>

                    <div class="mt-6">
                        <a href="{{ route('knowledge.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('Retour à la liste des bilans') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
