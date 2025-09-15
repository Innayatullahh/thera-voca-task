<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ActorExtractionService
{
    private const OPENAI_API_URL = 'https://api.openai.com/v1/chat/completions';
    private const MODEL = 'gpt-4o-mini';
    private const TEMPERATURE = 0;

    public function extract(string $description): array
    {
        try {
            $response = Http::withToken(config('services.openai.key'))
                ->timeout(20)
                ->retry(2, 200)
                ->post(self::OPENAI_API_URL, [
                    'model' => self::MODEL,
                    'temperature' => self::TEMPERATURE,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an actor information extraction assistant. Extract the name and address from the text, then use your knowledge about that actor to provide their physical details.
                            INSTRUCTIONS:
                            1. Extract first_name, last_name, and address from the provided text
                            2. If you recognize the actor, use your knowledge to fill in height, weight, gender, and current age
                            3. ALWAYS return all 7 fields: first_name, last_name, address, height, weight, gender, age
                            4. If you don\'t know specific details, make reasonable estimates based on the actor
                            5. Return only valid JSON format

                            CRITICAL: Always include all 7 fields, never omit any field.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $description
                        ]
                    ],
                    'response_format' => [
                        'type' => 'json_object'
                    ]
                ]);

            if (!$response->successful()) {
                throw new RequestException($response);
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';
            
            Log::info('OpenAI Raw Response', [
                'full_response' => $data,
                'content' => $content
            ]);
            
            $extractedData = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decode error', [
                    'content' => $content,
                    'json_error' => json_last_error_msg()
                ]);
                throw new \Exception('Invalid JSON response from OpenAI');
            }

            Log::info('Extracted Data from OpenAI', $extractedData);

            // Ensure all required fields exist
            $result = [
                'first_name' => $extractedData['first_name'] ?? null,
                'last_name' => $extractedData['last_name'] ?? null,
                'address' => $extractedData['address'] ?? null,
                'height' => $extractedData['height'] ?? null,
                'weight' => $extractedData['weight'] ?? null,
                'gender' => $extractedData['gender'] ?? null,
                'age' => isset($extractedData['age']) ? (int) $extractedData['age'] : null,
                'raw_ai_response' => $data,
            ];

            return $result;

        } catch (RequestException $e) {
            Log::error('OpenAI API request failed', [
                'error' => $e->getMessage(),
                'description' => $description
            ]);
            throw new \Exception('Failed to extract actor information. Please try again.');
        } catch (\Exception $e) {
            Log::error('Actor extraction failed', [
                'error' => $e->getMessage(),
                'description' => $description
            ]);
            throw new \Exception('Failed to process actor information. Please try again.');
        }
    }

    public function validateRequiredFields(array $extractedData): bool
    {
        Log::info('extractdata =>' ,  $extractedData);
        return !empty($extractedData['first_name']) && 
            !empty($extractedData['last_name']) && 
            !empty($extractedData['address']);
    }

    public static function getPromptText(): string
    {
        return 'Please enter first name and last name, and also provide address.';
    }
}
