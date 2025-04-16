<?php

namespace App\Http\Controllers;

use App\Models\QCM;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroupController extends Controller
{
    /**
     * Display groups and QCMs list
     *
     * @return Factory|View|Application|object
     */
    public function index()
    {
        $qcms = QCM::latest()->get();
        return view('pages.groups.index', compact('qcms'));
    }

    /**
     * Show QCM creation form
     */
    public function createQCM()
    {
        return view('pages.groups.create-qcm');
    }

    /**
     * Generate QCM with AI
     */
    public function generateQCM(Request $request)
    {
        $request->validate([
            'theme' => 'required|string|max:255',
            'question_count' => 'required|integer|min:1|max:20'
        ]);

        $prompt = $this->buildPrompt($request->theme, $request->question_count);

        try {
            $response = $this->callDeepSeekApi($prompt);

            if (!$response->successful()) {
                throw new \Exception("API request failed with status: ".$response->status());
            }

            $qcmData = $this->parseApiResponse($response);
            $createdQCM = $this->storeQCM($qcmData, $request->theme);

            return redirect()
                ->route('groups.qcm.show', $createdQCM->id)
                ->with('success', 'Quiz generated successfully!');

        } catch (\Exception $e) {
            Log::error('QCM Generation Error: '.$e->getMessage(), [
                'request' => $request->all(),
                'error' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to generate quiz: '.$e->getMessage());
        }
    }

    /**
     * Show a specific QCM
     */
    public function showQCM(QCM $qcm)
    {
        return view('pages.groups.show-qcm', compact('qcm'));
    }

    /**
     * Build the prompt for DeepSeek API
     */
    private function buildPrompt(string $theme, int $questionCount): string
    {
        return "Generate a multiple choice quiz (QCM) about '{$theme}' with {$questionCount} questions.
        Each question should have 4 possible answers with only one correct answer.
        Format the response in valid JSON with this exact structure:
        {
            \"title\": \"Quiz about [theme]\",
            \"questions\": [
                {
                    \"question\": \"...\",
                    \"answers\": [\"...\", \"...\", \"...\", \"...\"],
                    \"correct\": index_of_correct_answer
                }
            ]
        }";
    }

    /**
     * Call DeepSeek API
     */
    private function callDeepSeekApi(string $prompt)
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer '.env('DEEPSEEK_API_KEY'),
            'Content-Type' => 'application/json'
        ])->timeout(30)->post('https://api.deepseek.com/v1/chat/completions', [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7,
            'response_format' => ['type' => 'json_object']
        ]);
    }

    /**
     * Parse API response
     */
    private function parseApiResponse($response): array
    {
        $responseData = $response->json();

        if (!isset($responseData['choices'][0]['message']['content'])) {
            throw new \Exception("Invalid API response structure - missing choices");
        }

        $content = $responseData['choices'][0]['message']['content'];
        $jsonContent = $this->extractJsonFromString($content);

        $qcm = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON: ".json_last_error_msg());
        }

        $this->validateQcmStructure($qcm);

        return $qcm;
    }

    /**
     * Extract JSON from API response string
     */
    private function extractJsonFromString(string $content): string
    {
        $jsonStart = strpos($content, '{');
        $jsonEnd = strrpos($content, '}');

        if ($jsonStart === false || $jsonEnd === false) {
            throw new \Exception("No valid JSON found in API response");
        }

        return substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
    }

    /**
     * Validate QCM structure
     */
    private function validateQcmStructure(array $qcm): void
    {
        if (!isset($qcm['title']) || !isset($qcm['questions'])) {
            throw new \Exception("Missing required fields in QCM");
        }

        if (!is_array($qcm['questions'])) {
            throw new \Exception("Questions must be an array");
        }

        foreach ($qcm['questions'] as $index => $question) {
            if (!isset($question['question']) ||
                !isset($question['answers']) ||
                !isset($question['correct'])) {
                throw new \Exception("Invalid question structure at index {$index}");
            }

            if (!is_array($question['answers']) || count($question['answers']) !== 4) {
                throw new \Exception("Each question must have exactly 4 answers");
            }
        }
    }

    /**
     * Store QCM in database
     */
    private function storeQCM(array $qcmData, string $theme): QCM
    {
        return QCM::create([
            'title' => $qcmData['title'],
            'theme' => $theme,
            'questions' => $qcmData['questions'],
            'user_id' => auth()->id()
        ]);
    }
}
