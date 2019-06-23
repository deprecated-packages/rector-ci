<?php declare (strict_types=1);

namespace Rector\RectorCI\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DashboardController extends AbstractController
{
    /**
     * @Route("/app/dashboard", name="dashboard", methods={"GET"})
     */
    public function __invoke(): Response
    {
        // @TODO: security

        return $this->render('dashboard/dashboard.twig');
    }
}
