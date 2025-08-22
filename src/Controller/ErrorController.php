<?php

namespace App\Controller;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ErrorController extends AbstractController
{
    public function show(FlattenException $exception, \Twig\Environment $twig): Response
    {
        $statusCode = $exception->getStatusCode();
        $statusText = Response::$statusTexts[$statusCode] ?? 'Erreur';

        // On choisit un template spécifique si dispo, sinon error.html.twig
        $template = sprintf('bundles/TwigBundle/Exception/error%s.html.twig', $statusCode);

        if (!$twig->getLoader()->exists($template)) {
            $template = 'bundles/TwigBundle/Exception/error.html.twig';
        }

        return $this->render($template, [
            'status_code' => $statusCode,
            'status_text' => $statusText,
        ]);
    }
}
