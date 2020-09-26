<?php declare(strict_types=1);

namespace Rector\RectorCI\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RunnerController extends AbstractController
{
    /**
     * @Route("/api/v4/jobs/request", methods={"GET", "POST"})
     */
    public function __invoke(): Response
    {
        return $this->json([
            'id' => 123,
        ], 201);
    }
}
