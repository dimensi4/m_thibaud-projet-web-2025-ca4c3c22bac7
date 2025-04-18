<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800">
                {{ __('Créer un Bilan de Compétences') }}
            </h1>
            <a href="{{ route('knowledge.index') }}" class="inline-flex items-center px-3 py-2 bg-gray-200 border border-transparent rounded-md text-xs text-gray-700 hover:bg-gray-300 focus:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 transition duration-150 ease-in-out">
                {{ __('Retour aux Bilans') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="px-6 py-8">
                    <h2 class="text-lg font-medium text-gray-900 mb-6">{{ __('Paramètres du Nouveau Bilan') }}</h2>
                    <form method="POST" action="{{ route('knowledge.generate') }}" class="space-y-6">
                        @csrf

                        <div class="space-y-1">
                            <label for="theme" class="block text-sm font-semibold text-gray-700">{{ __('Langage de programmation à évaluer') }}</label>
                            <select id="theme" name="theme" class="block w-full mt-1 rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="" disabled selected>{{ __('Sélectionner un langage') }}</option>
                                <option value="PHP">PHP</option>
                                <option value="JavaScript">JavaScript</option>
                                <option value="Python">Python</option>
                                <option value="Java">Java</option>
                                <option value="C#">C#</option>
                                <option value="C++">C++</option>
                                <option value="Ruby">Ruby</option>
                                <option value="Swift">Swift</option>
                                <option value="Kotlin">Kotlin</option>
                                <option value="Go">Go</option>
                            </select>
                            @error('theme')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1">
                            <label for="number_of_questions" class="block text-sm font-semibold text-gray-700">{{ __('Nombre de questions') }}</label>
                            <input id="number_of_questions" class="block w-full mt-1 rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" type="number" name="number_of_questions" value="{{ old('number_of_questions') }}" required min="1">
                            @error('number_of_questions')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1">
                            <label for="answers_per_question" class="block text-sm font-semibold text-gray-700">{{ __('Nombre de réponses par question') }}</label>
                            <input id="answers_per_question" class="block w-full mt-1 rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" type="number" name="answers_per_question" value="{{ old('answers_per_question') }}" required min="2" max="10">
                            @error('answers_per_question')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Générer le Bilan') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
