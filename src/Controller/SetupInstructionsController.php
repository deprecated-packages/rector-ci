<?php declare (strict_types=1);

namespace RectorCI\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class SetupInstructionsController
{
    /**
     * @Route("/setup-instructions", methods={"GET"})
     */
    public function __invoke(): Response
    {
        return new Response();
    }
}
