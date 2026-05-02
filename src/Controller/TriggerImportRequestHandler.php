<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class TriggerImportRequestHandler
{
    #[Route(path: '/trigger-import', methods: ['POST'], priority: 2)]
    public function handle(): JsonResponse
    {
        $logFile = '/tmp/strava_import.log';
        $doneFile = '/tmp/strava_import.done';

        @unlink($doneFile);
        file_put_contents($logFile, "STARTED\n");

        exec(sprintf(
            'sh -c "php /var/www/bin/console app:strava:import-data >> %s 2>&1 && php /var/www/bin/console app:strava:build-files >> %s 2>&1; echo DONE >> %s; touch %s" > /dev/null 2>&1 &',
            $logFile, $logFile, $logFile, $doneFile
        ));

        return new JsonResponse(['status' => 'started'], Response::HTTP_OK);
    }
}
