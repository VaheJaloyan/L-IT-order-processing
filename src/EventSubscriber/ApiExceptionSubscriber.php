<?php

namespace App\EventSubscriber;

use App\Dto\ErrorResponseDto;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Converts unhandled exceptions into JSON error responses for all /api/* routes.
 *
 * Handles two cases:
 *  - Validation failures (HTTP 422) — flattens constraint violations into a field-keyed error map.
 *  - All other exceptions — wraps the message in an ErrorResponseDto with the appropriate HTTP status
 *    (derived from HttpExceptionInterface or defaulting to 500).
 */
class ApiExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * Registers this subscriber on the kernel exception event.
     *
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    /**
     * Intercepts exceptions thrown during the request lifecycle and converts them
     * to a structured JSON response. Non-API paths are ignored.
     *
     * Validation failures produce HTTP 422 with a field-keyed errors map:
     * `{ "errors": { "fieldName": ["message", ...] } }`
     *
     * All other exceptions produce: `{ "error": "<message>" }` with the matching HTTP status code.
     *
     * @param ExceptionEvent $event The kernel exception event carrying the throwable
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/')) {
            return;
        }

        $exception = $event->getThrowable();
        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : 500;

        $previous = $exception->getPrevious();
        if ($previous instanceof ValidationFailedException) {
            $errors = [];
            foreach ($previous->getViolations() as $violation) {
                $errors[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            $event->setResponse(new JsonResponse(['errors' => $errors], 422));
            return;
        }

        $event->setResponse(new JsonResponse(new ErrorResponseDto($exception->getMessage()), $statusCode));
    }
}
