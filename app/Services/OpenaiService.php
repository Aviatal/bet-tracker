<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenaiService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
    }

    public function parseScreenshot($imagePath)
    {
        $imageData = base64_encode(file_get_contents($imagePath));
        $mimeType = $imagePath->getMimeType();

        try {
            $response = Http::withToken($this->apiKey)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Jesteś ekspertem od zakładów. Zwracasz dane WYŁĄCZNIE w formacie JSON. Nie używaj znaczników markdown (```json).'
                        ],
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => '
                                        Wyodrębnij dla każdego zdarzenia (bets):
                                            -gospodarz (home)
                                            -gość (away)
                                            -dyscyplina (discipline, poznasz po ikonie przy nazwie zdarzenia np. ikona piłki do piłki nożnej)
                                            -rodzaj zakładu(event_type, co zostało obstawione np. Wynik meczu. W przypadku gdy w typie jest nazwa drużyny użyj "Gospodarz" lub "Gość", czyli np. Rzuty rożne gospodarz zamiast rzuty rożne Torino)
                                            -wybór typu (selection, w przypadku gdy w typie jest nazwa drużyny użyj "Gospodarz" lub "Gość")
                                        Dodatkowo dla całego kuponu(slip) pokaż kurs (odds) i stawkę (stake). Jeśli czegoś nie widzisz, wpisz null. Wszystkie dane zwróć po polsku.'
                                ],
                                [
                                    'type' => 'image_url',
                                    'image_url' => [
                                        'url' => "data:$mimeType;base64,$imageData",
                                        'detail' => 'high'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'max_tokens' => 1000,
                ]);
            if ($response->successful()) {
                $jsonString = $response->json('choices.0.message.content');
                return json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);
            }

            Log::error('OpenAI Error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('OCR Service Exception: ' . $e->getMessage());
            return null;
        }
    }
}
