<?php

declare(strict_types=1);

namespace App\Monolog;

use Monolog\LogRecord;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class RequestProcessor
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $request = $this->requestStack->getCurrentRequest();
        $extra = $record->extra;

        if ($request) {
            $extra['request'] = [
                'method' => $request->getMethod(),
                'path'   => $request->getPathInfo(),
                'query'  => $request->query->all(),
                'body'   => $request->getContent(),
            ];
        }
        return $record->with(extra: $extra);
    }
}
