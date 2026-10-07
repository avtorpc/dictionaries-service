<?php

namespace App\EventSubscriber;

use App\Shared\Exception\ApiExceptionInterface;
use App\Shared\Http\Contract\ResponseContractInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire(service: 'monolog.logger.api')]
        private LoggerInterface $logger,

        private ResponseContractInterface $responseContract,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 10],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // =========================
        // 1. OUR API EXCEPTIONS
        // =========================
        if ($exception instanceof ApiExceptionInterface) {

            $status = $exception->getStatusCode();
            $code = $this->normalizeCode($exception->getErrorCode());
            $message = $exception->getMessage();
            $context = $exception->getContextAsString();

        } else {

            // =========================
            // 2. GENERIC SYMFONY EXCEPTIONS
            // =========================
            $status = 500;
            $code = 'INTERNAL_SERVER_ERROR';
            $message = 'Внутренняя ошибка сервера';
            $env = $incomingEnv
                ?? ($_ENV['APP_ENV'] ?? null)
                ?? ($_SERVER['APP_ENV'] ?? null)
                ?? 'prod';

            $context = $this->formatThrowableByEnv($exception, $env);
        }

        // =========================
        // LOGGING
        // =========================
        $this->logger->error('API exception', [
            'error_code' => $code,
            'message' => $message,
            'path' => $event->getRequest()->getPathInfo(),
            'method' => $event->getRequest()->getMethod(),
            'context' => $context,
            'trace' => $exception->getTraceAsString(),
        ]);

        // =========================
        // RESPONSE
        // =========================
        $response = $this->responseContract->error(
            code: $code,
            message: $message,
            status: $status,
            contex: $this->buildSafeContext($exception)
        );

        $event->setResponse($response);
    }

    private function normalizeCode(string|\BackedEnum $code): string
    {
        return $code instanceof \BackedEnum ? $code->value : $code;
    }

    private function formatThrowableByEnv(\Throwable $e, string $env): string
    {
        // PROD — минимально безопасный лог
        if ($env === 'prod') {
            $result = sprintf(
                "[%s] %s",
                $e::class,
                $e->getMessage()
            );

            if ($prev = $e->getPrevious()) {
                $result .= sprintf(
                    "\nPrevious: [%s] %s",
                    $prev::class,
                    $prev->getMessage()
                );
            }

            return $result;
        }

        // DEV / STAGE — полный диагностический лог
        $result = sprintf(
            "[%s]\nMessage: %s\nFile: %s:%d\n\nTrace:\n%s",
            $e::class,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        if ($prev = $e->getPrevious()) {
            $result .= "\n\nPrevious:\n" . sprintf(
                    "[%s]\nMessage: %s\nFile: %s:%d\nTrace:\n%s",
                    $prev::class,
                    $prev->getMessage(),
                    $prev->getFile(),
                    $prev->getLine(),
                    $prev->getTraceAsString()
                );
        }

        return $result;
    }

    private function buildSafeContext(\Throwable $e): string
    {
        $parts = [];

        $parts[] = $e::class;

        if ($e->getPrevious()) {
            $parts[] = 'prev: ' . $e->getPrevious()::class;
        }

        // Doctrine / PDO / DBAL hint
        if (str_contains($e::class, 'Doctrine') || str_contains($e->getMessage(), 'SQLSTATE')) {
            $parts[] = 'type: database';
        }

        // Symfony HTTP layer
        if (str_contains($e::class, 'Http')) {
            $parts[] = 'type: http';
        }

        return implode(' | ', $parts);
    }
}
