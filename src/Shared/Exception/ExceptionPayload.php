<?php

namespace App\Shared\Exception;

use App\Shared\Exception\ErrorCode;

final class ExceptionPayload
{
    public function __construct(
        public string $message,
        public ErrorCode|string $errorCode,
        public array $context = [],
    ) {}
}
