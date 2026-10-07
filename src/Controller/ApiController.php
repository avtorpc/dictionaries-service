<?php

namespace App\Controller;

use App\Infrastructure\Application\ApplicationInfo;
use App\Shared\Time\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ApiController extends AbstractController
{
    public function __construct(
        private ApplicationInfo $app,
        private ClockInterface $clock
    ) {}

    #[Route("/", name: "app_api", methods: ["GET"])]
    public function index(): JsonResponse
    {
        return $this->json([
            'service' => $this->app->name(),
            'version' => $this->app->version(),
            'status' => 'ok',
            'timestamp' => $this->clock->nowFormatted()
        ]);
    }

    #[Route("/test/error", name: "test_err", methods: ["GET"])]
    public function testError(): JsonResponse
    {
        throw new RuntimeException('Runtime error');
//        throw new DictionaryNotFoundException(
//            new ExceptionPayload(
//                message: 'Dictionary not found',
//                errorCode: 'NOT_FOUND_DICTIONARY',
//                context: [
//                    'dictionary' => 'dictionary_x'
//                ]
//            )
//        );

//        throw new BadRequestException(
//            new ExceptionPayload(
//                message: 'Invalid request body',
//                errorCode: 'BAD_REQUEST',
//                context: [
//                    'field' => 'email',
//                    'reason' => 'required'
//                ]
//            )
//        );

//        throw new TooManyRequestsException(
//            new ExceptionPayload(
//                message: 'Too many requests',
//                errorCode: 'TOO_MANY_REQUESTS',
//                context: [
//                    'field' => 'email',
//                    'reason' => 'слишком частые обращения'
//                ]
//            )
//        );

//        throw new UnprocessableEntityException(
//            new ExceptionPayload(
//                message: 'Validation failed',
//                errorCode: 'UNPROCESSABLE_ENTITY',
//                context: [
//                    'field' => 'email-1'
//                ]
//            )
//        );


//        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Forbidden');

 //       throw new \PDOException('DB broken');
    }
}
