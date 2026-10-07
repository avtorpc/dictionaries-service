<?php

namespace App\Shared\Exception;

interface ApiExceptionInterface
{
    public function getStatusCode(): int;
    public function getErrorCode(): string;
    public function getContext(): array;
    public function getContextAsString(): string;
}
