<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class McpService
{
    private const URL = 'https://pakistancaselaw.com/mcp';
    private ?string $sessionId = null;
    private int $callId = 10;

    public function initialize(): void
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json, text/event-stream',
        ])->post(self::URL, [
            'jsonrpc' => '2.0',
            'method'  => 'initialize',
            'id'      => 1,
            'params'  => [
                'protocolVersion' => '2024-11-05',
                'clientInfo'      => ['name' => 'mizan-ai', 'version' => '1.0'],
                'capabilities'    => [],
            ],
        ]);

        $this->sessionId = $response->header('Mcp-Session-Id');
    }

    public function searchJudgments(int $limit = 50, int $offset = 0): array
    {
        $text = $this->callTool('caselaw_search', [
            'limit'           => $limit,
            'offset'          => $offset,
            'response_format' => 'json',
        ]);

        $data = json_decode($text, true);
        return $data['cases'] ?? [];
    }

    public function getFullCase(int $caseId): ?array
    {
        $text = $this->callTool('caselaw_get_case', [
            'case_id'         => $caseId,
            'section'         => 'full',
            'response_format' => 'json',
        ]);

        return json_decode($text, true);
    }

    private function callTool(string $name, array $arguments): string
    {
        $response = Http::withHeaders([
            'Accept'         => 'application/json, text/event-stream',
            'Mcp-Session-Id' => $this->sessionId,
        ])->post(self::URL, [
            'jsonrpc' => '2.0',
            'method'  => 'tools/call',
            'id'      => $this->callId++,
            'params'  => ['name' => $name, 'arguments' => $arguments],
        ]);

        $data = $this->parseSSE($response->body());
        return $data['result']['content'][0]['text'] ?? '{}';
    }

    private function parseSSE(string $body): ?array
    {
        foreach (explode("\n", $body) as $line) {
            if (str_starts_with($line, 'data: ')) {
                return json_decode(substr($line, 6), true);
            }
        }
        return null;
    }
}