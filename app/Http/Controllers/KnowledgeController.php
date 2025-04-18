<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Qcm; 
use App\Models\Question;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KnowledgeController extends Controller
{
    /**
     * Display the main knowledge index page, showing a list of available quizzes.
     *
     * @return Factory|View|Application|object
     */
    public function index()
    {
        $qcms = Qcm::all(); // Retrieve all available quizzes from the database
        return view('pages.knowledge.index', compact('qcms'));
    }

    /**
     * Display the form to create a new quiz.
     *
     * @return Factory|View|Application|object
     */
    public function create()
    {
        return view('pages.knowledge.create');
    }

    /**
     * Generate new quiz questions using the Gemini API and store the quiz in the database,
     * providing more time for the API to respond.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generate(Request $request)
    {
        Log::info('=== START OF GENERATION (Gemini) ===');
        Log::debug('Received data:', $request->all());

        // Validate the incoming request data
        $request->validate([
            'theme' => 'required|string',
            'number_of_questions' => 'required|integer|min:1',
            'answers_per_question' => 'required|integer|min:2|max:10',
        ]);

        $language = $request->input('theme'); // Get the selected programming language from the form
        $numberOfQuestions = $request->input('number_of_questions');
        $answersPerQuestion = $request->input('answers_per_question');

        // Calculate the number of questions for each difficulty level
        $simpleCount = round($numberOfQuestions * 0.3);
        $mediumCount = round($numberOfQuestions * 0.4);
        $difficultCount = $numberOfQuestions - $simpleCount - $mediumCount;

        // Retrieve the Gemini API key from the configuration
        $googleApiKey = config('services.gemini.api_key');
        $geminiApiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

        // Log the API configuration for debugging
        Log::info('API Configuration (Gemini)', [
            'language' => $language,
            'questions_total' => $numberOfQuestions,
            'questions_simple' => $simpleCount,
            'questions_moyen' => $mediumCount,
            'questions_difficile' => $difficultCount,
            'answers_per_question' => $answersPerQuestion,
            'api_key_configured' => !empty($googleApiKey),
            'api_url' => $geminiApiUrl,
        ]);

        $generatedQuestionsData = [];

        // Closure to handle the question generation for a specific difficulty
        $generateQuestions = function ($difficulty, $count) use ($language, $answersPerQuestion, $googleApiKey, $geminiApiUrl, &$generatedQuestionsData) {
            if ($count <= 0) {
                return;
            }

            // Construct the prompt for the Gemini API
            $prompt = "Generate $count questions of $difficulty difficulty on the programming language '$language' with $answersPerQuestion answer options, one of which is correct. The format of each question should be: 'Question: [the question]?\nAnswers: a) [answer 1], b) [answer 2], c) [answer 3]...\nCorrect Answer: [the letter of the correct answer]'.";

            Log::debug("Prompt sent (Gemini - $difficulty):", ['prompt' => $prompt]);

            try {
                // Increased timeout to allow Gemini more time to respond (in seconds)
                $response = Http::withOptions(['timeout' => 60])->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post($geminiApiUrl . '?key=' . $googleApiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

                // Log the API response for debugging
                Log::info("API Response (Gemini - $difficulty)", [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);

                // Process the successful API response
                if ($response->successful()) {
                    $results = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    $questions = explode("\n\n", trim($results));

                    Log::debug("Raw questions (Gemini - $difficulty):", ['questions' => $questions]);

                    // Parse each generated question
                    foreach ($questions as $questionText) {
                        if (preg_match('/^Question: (.+?)\nAnswers: (.+?)\nCorrect Answer: ([a-z])\)?$/ms', $questionText, $matches)) {
                            $questionBody = trim($matches[1]);
                            $answersText = trim($matches[2]);
                            $correctAnswerLetter = strtolower(trim($matches[3]));

                            $answersArray = [];
                            preg_match_all('/([a-z])\)\s*([^,]+)(?:,|$)/i', $answersText, $answerMatches, PREG_SET_ORDER);
                            foreach ($answerMatches as $match) {
                                $answersArray[strtolower($match[1])] = trim($match[2]);
                            }

                            // Add the parsed question data to the array
                            if (!empty($questionBody) && count($answersArray) >= $answersPerQuestion && isset($answersArray[$correctAnswerLetter])) {
                                $generatedQuestionsData[] = [
                                    'question' => $questionBody,
                                    'answers' => $answersArray,
                                    'correct_answer' => $correctAnswerLetter,
                                    'difficulty' => $difficulty,
                                ];
                            }
                        }
                    }

                    Log::info("Parsed questions (Gemini - $difficulty):", $generatedQuestionsData);
                } else {
                    // Log API errors
                    Log::error("API Error (Gemini - $difficulty)", [
                        'status' => $response->status(),
                        'error' => $response->json()
                    ]);
                }
            } catch (\Exception $e) {
                // Log any exceptions during the API call
                Log::error("API Exception (Gemini - $difficulty)", [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        };

        // Generate questions for each difficulty level
        $generateQuestions('simple', $simpleCount);
        $generateQuestions('moyen', $mediumCount);
        $generateQuestions('difficile', $difficultCount);

        // Shuffle the generated questions and take the required number
        $allGeneratedQuestions = collect($generatedQuestionsData)->shuffle()->take($numberOfQuestions);

        Log::info('Final selected questions (Gemini):', $allGeneratedQuestions->toArray());

        // Check if any questions were generated
        if ($allGeneratedQuestions->isEmpty()) {
            Log::error('No questions generated by Gemini');
            return back()->with('error', 'Failed to generate questions with Gemini');
        }

        try {
            Log::info('Creating the quiz...');
            // Create a new quiz record in the database
            $qcm = Qcm::create([
                'title' => $language, // Set the quiz title to the selected language
                'number_of_questions' => $numberOfQuestions,
                'answers_per_question' => $answersPerQuestion,
            ]);

            Log::info('Quiz created', ['id' => $qcm->id]);

            // Create question and answer records for the quiz
            foreach ($allGeneratedQuestions as $generatedQuestion) {
                $question = Question::create([
                    'qcm_id' => $qcm->id,
                    'question_text' => $generatedQuestion['question'],
                    'difficulty' => $generatedQuestion['difficulty'],
                ]);

                $correctAnswerId = null;
                $answerKeys = array_keys($generatedQuestion['answers']);

                // Create answer records for the question
                foreach ($generatedQuestion['answers'] as $key => $answerText) {
                    $isCorrect = (strtolower($key) === $generatedQuestion['correct_answer']);
                    $answer = Answer::create([
                        'question_id' => $question->id,
                        'answer_text' => $answerText,
                        'is_correct' => $isCorrect,
                    ]);

                    // Store the ID of the correct answer
                    if ($isCorrect) {
                        $correctAnswerId = $answer->id;
                        $question->update(['correct_answer_id' => $correctAnswerId]);
                    }
                }

                Log::debug('Question created', [
                    'question_id' => $question->id,
                    'correct_answer_id' => $correctAnswerId
                ]);
            }

            Log::info('=== GENERATION COMPLETED SUCCESSFULLY (Gemini) ===');
            return redirect()->route('knowledge.index')->with('success', 'Quiz created successfully with Gemini!');

        } catch (\Exception $e) {
            // Log any errors during database operations
            Log::error('Error during creation (Gemini)', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error during saving (Gemini): ' . $e->getMessage());
        }
    }

    /**
     * Display a specific quiz with its questions and answers.
     *
     * @param Qcm $qcm
     * @return Factory|View|Application|object
     */
    public function show(Qcm $qcm)
    {
        $qcm->load('questions.answers'); // Eager load questions and answers
        return view('pages.knowledge.show', compact('qcm'));
    }

    /**
     * Display the quiz for the user to attempt.
     *
     * @param Qcm $qcm
     * @return Factory|View|Application|object
     */
    public function attempt(Qcm $qcm)
    {
        $qcm->load('questions.answers'); // Eager load questions and answers
        return view('pages.knowledge.attempt', compact('qcm'));
    }

    /**
     * Process the user's submitted answers for a quiz and calculate the score.
     *
     * @param Request $request
     * @param Qcm $qcm
     * @return Factory|View|Application|object
     */
    public function submit(Request $request, Qcm $qcm)
    {
        $score = 0;
        $totalQuestions = $qcm->questions->count();

        // Iterate through each question in the quiz
        foreach ($qcm->questions as $question) {
            $userAnswerId = $request->input('question_' . $question->id); // Get the user's answer ID for the question
            if ($userAnswerId) {
                // Find the correct answer for the current question
                $correctAnswer = Answer::where('question_id', $question->id)
                    ->where('is_correct', true)
                    ->first();

                // Increment the score if the user's answer matches the correct answer
                if ($correctAnswer && $correctAnswer->id == $userAnswerId) {
                    $score++;
                }
            }
        }

        // Calculate the final grade (out of 20)
        $note = ($score / $totalQuestions) * 20;

        // Display the result to the user
        return view('pages.knowledge.result', compact('qcm', 'score', 'totalQuestions', 'note'));
    }
}
