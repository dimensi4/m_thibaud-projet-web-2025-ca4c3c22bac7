<x-app-layout>
    <x-slot name="header">
        <h1 class="flex items-center gap-1 text-sm font-normal">
            <span class="text-gray-700">
                {{ $qcm->title }}
            </span>
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-xl font-semibold mb-4">{{ $qcm->title }}</h2>
                    <p class="text-gray-600 mb-6">Theme: {{ $qcm->theme }}</p>

                    <div class="space-y-6">
                        @foreach($qcm->questions as $index => $question)
                            <div class="border rounded-lg p-4">
                                <h3 class="font-medium mb-3">{{ $index + 1 }}. {{ $question['question'] }}</h3>
                                <div class="space-y-2">
                                    @foreach($question['answers'] as $i => $answer)
                                        <div class="flex items-center">
                                            <input type="radio"
                                                   id="q{{ $index }}a{{ $i }}"
                                                   name="q{{ $index }}"
                                                   value="{{ $i }}"
                                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                   @if($i == $question['correct']) checked @endif disabled>
                                            <label for="q{{ $index }}a{{ $i }}" class="ml-2 block text-sm text-gray-700">
                                                {{ $answer }}
                                                @if($i == $question['correct'])
                                                    <span class="text-green-600 ml-1">(Correct)</span>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
