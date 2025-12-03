<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIExplanationService
{
    protected string $apiKey;
    protected string $apiUrl;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->apiUrl = config('services.openai.api_url', 'https://api.openai.com/v1/chat/completions');
        $this->model = config('services.openai.model', 'gpt-4');
    }

    /**
     * Generate AI explanation for a security finding.
     */
    public function generateExplanation(array $finding): array
    {
        try {
            $prompt = $this->buildPrompt($finding);
            $response = $this->callAI($prompt);
            
            return $this->parseResponse($response);
        } catch (\Exception $e) {
            Log::error("AI explanation failed: " . $e->getMessage());
            
            // Return fallback explanation
            return $this->getFallbackExplanation($finding);
        }
    }

    /**
     * Build the AI prompt for the finding.
     */
    protected function buildPrompt(array $finding): string
    {
        $systemPrompt = <<<SYSTEM
You are a cybersecurity assistant that explains vulnerabilities to non-technical founders in clear, human language. Always include:

1. What this issue means in simple terms
2. What attackers can do in the real world
3. The real business impact (data loss, legal issues, etc.)
4. A simple, actionable fix

Be direct, avoid jargon, and focus on business consequences.
SYSTEM;

        $userPrompt = <<<USER
Issue: {$finding['title']}
Severity: {$finding['severity']}
Evidence: {$finding['evidence']}
Location: {$finding['location']}

Generate a security explanation with these sections:
1. Plain Explanation (2-3 sentences)
2. Real-World Attack Scenario (specific example)
3. Business Impact (consequences)
4. Fix Recommendation (clear steps)

Format your response as JSON with these keys: explanation, attack_scenario, business_impact, fix_recommendation
USER;

        return json_encode([
            'system' => $systemPrompt,
            'user' => $userPrompt,
        ]);
    }

    /**
     * Call the AI API.
     */
    protected function callAI(string $prompt): string
    {
        $promptData = json_decode($prompt, true);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post($this->apiUrl, [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $promptData['system'],
                ],
                [
                    'role' => 'user',
                    'content' => $promptData['user'],
                ],
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000,
        ]);

        if ($response->failed()) {
            throw new \Exception('AI API request failed: ' . $response->body());
        }

        return $response->json('choices.0.message.content');
    }

    /**
     * Parse AI response into structured data.
     */
    protected function parseResponse(string $response): array
    {
        // Try to parse as JSON first
        $decoded = json_decode($response, true);
        
        if ($decoded && isset($decoded['explanation'])) {
            return [
                'explanation' => $decoded['explanation'],
                'attack_scenario' => $decoded['attack_scenario'] ?? '',
                'business_impact' => $decoded['business_impact'] ?? '',
                'fix_recommendation' => $decoded['fix_recommendation'] ?? '',
            ];
        }

        // Fallback: Parse text response
        return $this->parseTextResponse($response);
    }

    /**
     * Parse text-based AI response.
     */
    protected function parseTextResponse(string $response): array
    {
        $sections = [
            'explanation' => '',
            'attack_scenario' => '',
            'business_impact' => '',
            'fix_recommendation' => '',
        ];

        // Simple text parsing logic
        $lines = explode("\n", $response);
        $currentSection = 'explanation';

        foreach ($lines as $line) {
            $line = trim($line);
            
            if (stripos($line, 'attack scenario') !== false) {
                $currentSection = 'attack_scenario';
                continue;
            } elseif (stripos($line, 'business impact') !== false) {
                $currentSection = 'business_impact';
                continue;
            } elseif (stripos($line, 'fix') !== false || stripos($line, 'recommendation') !== false) {
                $currentSection = 'fix_recommendation';
                continue;
            }

            if (!empty($line)) {
                $sections[$currentSection] .= $line . ' ';
            }
        }

        return array_map('trim', $sections);
    }

    /**
     * Get fallback explanation when AI fails.
     */
    protected function getFallbackExplanation(array $finding): array
    {
        $templates = [
            'critical' => [
                'explanation' => 'This is a critical security vulnerability that could allow attackers to gain unauthorized access to your system or data.',
                'attack_scenario' => 'An attacker could exploit this vulnerability to steal sensitive information, modify data, or take control of your application.',
                'business_impact' => 'This could result in data breaches, legal liability, loss of customer trust, and potential business shutdown.',
                'fix_recommendation' => 'Immediate action is required. Review your security configuration and implement proper access controls.',
            ],
            'high' => [
                'explanation' => 'This is a high-severity security issue that significantly increases your risk of being compromised.',
                'attack_scenario' => 'Attackers could use this weakness to gain unauthorized access or extract sensitive information from your system.',
                'business_impact' => 'This could lead to data exposure, reputation damage, and potential regulatory penalties.',
                'fix_recommendation' => 'This should be fixed before launch. Implement the recommended security measures.',
            ],
            'medium' => [
                'explanation' => 'This is a moderate security concern that should be addressed to improve your overall security posture.',
                'attack_scenario' => 'While not immediately critical, this could be combined with other vulnerabilities to compromise your system.',
                'business_impact' => 'This could contribute to security incidents and make your system more vulnerable to attacks.',
                'fix_recommendation' => 'Plan to address this issue soon to maintain good security hygiene.',
            ],
            'low' => [
                'explanation' => 'This is a minor security concern that represents a best practice violation.',
                'attack_scenario' => 'The risk is relatively low, but addressing this will improve your overall security posture.',
                'business_impact' => 'The immediate business impact is minimal, but it\'s good practice to fix this.',
                'fix_recommendation' => 'Address this when convenient as part of your security improvements.',
            ],
        ];

        return $templates[$finding['severity']] ?? $templates['medium'];
    }

    /**
     * Generate explanations for multiple findings in batch.
     */
    public function generateBatchExplanations(array $findings): array
    {
        $results = [];

        foreach ($findings as $finding) {
            $results[] = array_merge(
                $finding,
                $this->generateExplanation($finding)
            );
        }

        return $results;
    }
}
