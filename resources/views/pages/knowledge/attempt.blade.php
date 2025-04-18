<x-app-layout>
    <x-slot name="header">
        <h1 class="flex items-center gap-1 text-sm font-normal">
            <a href="{{ route('knowledge.index') }}" class="text-gray-500 hover:text-gray-700 transition duration-150 ease-in-out">
                {{ __('Bilans de connaissances') }}
            </a>
            <svg class="flex-shrink-0 w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="text-gray-700">{{ $qcm->title }}</span>
        </h1>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ __('Répondre au bilan :') }} {{ $qcm->title }}</h2>
                    <form method="POST" action="{{ route('knowledge.submit', $qcm->id) }}">
                        @csrf
                        @foreach ($qcm->questions as $question)
                            <div class="mb-6 border-b border-gray-200 pb-4">
                                <p class="font-semibold text-gray-700 mb-2">{{ $loop->iteration }}. {{ $question->question_text }} <span class="text-sm text-gray-500">({{ ucfirst($question->difficulty) }})</span></p>
                                <ul class="list-none ml-4">
                                    @foreach ($question->answers as $answer)
                                        <li class="py-1">
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="question_{{ $question->id }}" value="{{ $answer->id }}" class="form-radio h-5 w-5 text-indigo-600 transition duration-150 ease-in-out">
                                                <span class="ml-2 text-gray-700">{{ $answer->answer_text }}</span>
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('Soumettre les réponses') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
