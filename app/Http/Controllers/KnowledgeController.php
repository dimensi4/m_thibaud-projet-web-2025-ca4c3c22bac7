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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class KnowledgeController extends Controller
{
    /**
     * Display the main knowledge page.
     *
     * @return Factory|View|Application|object
     */
    public function index()
    {
        return view('pages.knowledge.index');
    }

    /**
     * Display the form to create a new QCM.
     *
     * @return Factory|View|Application|object
     */
    public function create()
    {
        return view('pages.knowledge.create');
    }

    /**
     * Generate and store the QCM questions using the DeepSeek API.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generate(Request $request)
    {
        $request->validate([
            'theme' => 'required|string',
            'number_of_questions' => 'required|integer|min:1',
            'answers_per_question' => 'required|integer|min:2|max:10',
        ]);

        $theme = $request->input('theme');
        $numberOfQuestions = $request->input('number_of_questions');
        $answersPerQuestion = $request->input('answers_per_question');

        $simpleCount = round($numberOfQuestions * 0.3);
        $mediumCount = round($numberOfQuestions * 0.4);
        $difficultCount = $numberOfQuestions - $simpleCount - $mediumCount;

        $deepSeekApiKey = env('DEEPSEEK_API_KEY');
        $deepSeekApiUrl = env('DEEPSEEK_API_URL');

        $generatedQuestionsData = [];

        $generateQuestions = function ($difficulty, $count) use ($theme, $answersPerQuestion, $deepSeekApiKey, $deepSeekApiUrl, &$generatedQuestionsData) {
            if ($count <= 0) {
                return;
            }

            $prompt = "Générer $count questions de difficulté $difficulty sur le thème de '$theme' avec $answersPerQuestion options de réponse dont une correcte. Le format de chaque question doit être : 'Question: [la question]?\nRéponses: a) [réponse 1], b) [réponse 2], c) [réponse 3]...\nRéponse Correcte: [la lettre de la réponse correcte]'.";

            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $deepSeekApiKey,
                ])->post($deepSeekApiUrl, [
                    'prompt' => $prompt,
                    'n' => 1,
                    'max_tokens' => 800, // Adjust as needed
                ]);

                if ($response->successful()) {
                    $results = $response->json()['choices'][0]['text'] ?? '';
                    $questions = explode("\n\n", trim($results));

                    foreach ($questions as $questionText) {
                        if (preg_match('/^Question: (.+?)\nRéponses: (.+?)\nRéponse Correcte: ([a-z])\)?$/ms', $questionText, $matches)) {
                            $questionBody = trim($matches[1]);
                            $answersText = trim($matches[2]);
                            $correctAnswerLetter = strtolower(trim($matches[3]));

                            $answersArray = [];
                            preg_match_all('/([a-z])\)\s*([^,]+)(?:,|$)/i', $answersText, $answerMatches, PREG_SET_ORDER);
                            foreach ($answerMatches as $match) {
                                $answersArray[strtolower($match[1])] = trim($match[2]);
                            }

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
                } else {
                    \Illuminate\Support\Facades\Log::error("Erreur API DeepSeek ($difficulty):", ['response' => $response->json()]);
                }
            } catch (\Exception $e) {
                logger()->error("DeepSeek API Exception ($difficulty):", ['message' => $e->getMessage()]);
            }
        };

        $generateQuestions('simple', $simpleCount);
        $generateQuestions('moyen', $mediumCount);
        $generateQuestions('difficile', $difficultCount);

        $allGeneratedQuestions = collect($generatedQuestionsData)->shuffle()->take($numberOfQuestions);

        if ($allGeneratedQuestions->isEmpty()) {
            return back()->with('error', 'Erreur lors de la génération des questions par l\'IA.');
        }

        // Create the QCM record
        $qcm = Qcm::create([
            'title' => $theme,
            'number_of_questions' => $numberOfQuestions,
            'answers_per_question' => $answersPerQuestion,
        ]);

        // Store the generated questions and answers in the database
        foreach ($allGeneratedQuestions as $generatedQuestion) {
            $question = Question::create([
                'qcm_id' => $qcm->id,
                'question_text' => $generatedQuestion['question'],
                'difficulty' => $generatedQuestion['difficulty'],
            ]);

            $correctAnswerId = null;
            $answerKeys = array_keys($generatedQuestion['answers']);

            foreach ($generatedQuestion['answers'] as $key => $answerText) {
                $isCorrect = (strtolower($key) === $generatedQuestion['correct_answer']);
                $answer = Answer::create([
                    'question_id' => $question->id,
                    'answer_text' => $answerText,
                    'is_correct' => $isCorrect,
                ]);
                if ($isCorrect) {
                    $correctAnswerId = $answer->id;
                }
            }
            $question->update(['correct_answer_id' => $correctAnswerId]);
        }

        return redirect()->route('knowledge.index')->with('success', 'Bilan de compétences créé avec succès !');
    }

    /**
     * Display a specific QCM.
     *
     * @param Qcm $qcm
     * @return Factory|View|Application|object
     */
    public function show(Qcm $qcm)
    {
        $qcm->load('questions.answers'); // Eager load relations
        return view('pages.knowledge.show', compact('qcm'));
    }
}
