<?php
namespace App\Shared\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

final class TooManyRequestsException extends HttpException implements ApiExceptionInterface
{
    public function __construct(
        ExceptionPayload $payload,
        ?\Throwable $previous = null
    ) {
        parent::__construct(429, $payload->message, $previous);

        $this->payload = $payload;
    }

    private ExceptionPayload $payload;

    public function getStatusCode(): int
    {
        return 429;
    }

    public function getErrorCode(): string
    {
        return $this->payload->errorCode instanceof \BackedEnum
            ? $this->payload->errorCode->value
            : $this->payload->errorCode;
    }

    public function getContext(): array
    {
        return $this->payload->context;
    }

    public function getContextAsString(): string
    {
        return implode(', ', $this->getContext()) ;
    }
}
