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
    public function index()
    {
        return view('pages.knowledge.index');
    }

    public function create()
    {
        return view('pages.knowledge.create');
    }

    public function generate(Request $request)
    {
        Log::info('=== DÉBUT DE LA GÉNÉRATION ===');
        Log::debug('Données reçues:', $request->all());

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

        Log::info('Configuration API', [
            'theme' => $theme,
            'questions_total' => $numberOfQuestions,
            'questions_simple' => $simpleCount,
            'questions_moyen' => $mediumCount,
            'questions_difficile' => $difficultCount,
            'answers_per_question' => $answersPerQuestion
        ]);

        $generatedQuestionsData = [];

        $generateQuestions = function ($difficulty, $count) use ($theme, $answersPerQuestion, $deepSeekApiKey, $deepSeekApiUrl, &$generatedQuestionsData) {
            if ($count <= 0) {
                return;
            }

            $prompt = "Générer $count questions de difficulté $difficulty sur le thème de '$theme' avec $answersPerQuestion options de réponse dont une correcte. Le format de chaque question doit être : 'Question: [la question]?\nRéponses: a) [réponse 1], b) [réponse 2], c) [réponse 3]...\nRéponse Correcte: [la lettre de la réponse correcte]'.";

            Log::debug("Prompt envoyé ($difficulty):", ['prompt' => $prompt]);

            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $deepSeekApiKey,
                ])->post($deepSeekApiUrl, [
                    'prompt' => $prompt,
                    'n' => 1,
                    'max_tokens' => 800,
                ]);

                Log::info("Réponse API ($difficulty)", [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);

                if ($response->successful()) {
                    $results = $response->json()['choices'][0]['text'] ?? '';
                    $questions = explode("\n\n", trim($results));

                    Log::debug("Questions brutes ($difficulty):", ['questions' => $questions]);

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

                    Log::info("Questions parsées ($difficulty):", $generatedQuestionsData);
                } else {
                    Log::error("Erreur API ($difficulty)", [
                        'status' => $response->status(),
                        'error' => $response->json()
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Exception API ($difficulty)", [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        };

        $generateQuestions('simple', $simpleCount);
        $generateQuestions('moyen', $mediumCount);
        $generateQuestions('difficile', $difficultCount);

        $allGeneratedQuestions = collect($generatedQuestionsData)->shuffle()->take($numberOfQuestions);

        Log::info('Questions finales sélectionnées:', $allGeneratedQuestions->toArray());

        if ($allGeneratedQuestions->isEmpty()) {
            Log::error('Aucune question générée');
            return back()->with('error', 'Échec de la génération des questions');
        }

        try {
            Log::info('Création du QCM...');
            $qcm = Qcm::create([
                'title' => $theme,
                'number_of_questions' => $numberOfQuestions,
                'answers_per_question' => $answersPerQuestion,
            ]);

            Log::info('QCM créé', ['id' => $qcm->id]);

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
                        $question->update(['correct_answer_id' => $correctAnswerId]);
                    }
                }

                Log::debug('Question créée', [
                    'question_id' => $question->id,
                    'correct_answer_id' => $correctAnswerId
                ]);
            }

            Log::info('=== GÉNÉRATION TERMINÉE AVEC SUCCÈS ===');
            return redirect()->route('knowledge.index')->with('success', 'QCM créé avec succès!');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Erreur lors de la sauvegarde: ' . $e->getMessage());
        }
    }

    public function show(Qcm $qcm)
    {
        $qcm->load('questions.answers');
        return view('pages.knowledge.show', compact('qcm'));
    }
}
