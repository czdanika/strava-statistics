<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ImportStatusRequestHandler
{
    #[Route(path: '/import-status', methods: ['GET'], priority: 2)]
    public function handle(): JsonResponse
    {
        $logFile = '/tmp/strava_import.log';
        $doneFile = '/tmp/strava_import.done';

        if (!file_exists($logFile)) {
            return new JsonResponse(['running' => false, 'line' => '']);
        }

        $done = file_exists($doneFile);

        $content = file_get_contents($logFile);
        $lines = array_values(array_filter(
            array_map('trim', explode("\n", $content)),
            fn(string $l) => $l !== '' && $l !== 'STARTED' && $l !== 'DONE'
        ));

        $lastLine = end($lines) ?: '';
        // Strip ANSI colour codes
        $lastLine = preg_replace('/\x1B\[[0-9;]*[mGKHF]/', '', $lastLine);
        // Strip progress bar characters, keep the readable part
        $lastLine = preg_replace('/\[=*>?-*\]/', '', $lastLine);
        $lastLine = preg_replace('/\s+/', ' ', trim($lastLine));

        return new JsonResponse([
            'running' => !$done,
            'line'    => $lastLine,
        ]);
    }
}
