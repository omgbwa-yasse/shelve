<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        //
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // Si la requête vient de l'espace OPAC, rediriger vers la page de connexion OPAC
        if ($request->is('opac/*')) {
            return redirect()->guest(route('opac.login'));
        }

        // Sinon, utiliser le comportement par défaut
        return $request->expectsJson()
            ? response()->json(['message' => $exception->getMessage()], 401)
            : redirect()->guest(route('login'));
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // En cas d'erreur dans l'espace OPAC, rester dans l'espace OPAC
        if ($request->is('opac/*') && !$request->expectsJson() && $exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();

            if ($statusCode == 404) {
                return redirect()->route('opac.index')->with('error', 'Page non trouvée.');
            }

            if ($statusCode >= 500) {
                return redirect()->route('opac.index')->with('error', 'Une erreur est survenue. Veuillez réessayer.');
            }
        }

        return parent::render($request, $exception);
    }
}
