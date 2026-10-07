<?php
namespace App\Shared\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DictionaryNotFoundException extends NotFoundHttpException implements ApiExceptionInterface
{
    private ExceptionPayload $payload;

    public function __construct(
        ExceptionPayload $payload,
        ?\Throwable $previous = null
    ) {
        parent::__construct($payload->message ?: 'Not found', $previous);

        $this->payload = $payload;
    }

    public function getStatusCode(): int
    {
        return 404;
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
