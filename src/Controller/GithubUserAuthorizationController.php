<?php declare (strict_types=1);

namespace Rector\RectorCI\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GithubUserAuthorizationController
{
    /**
     * @Route("/authorization/github", methods={"GET"})
     */
    public function __invoke(): Response
    {
        return new Response();
    }
}
