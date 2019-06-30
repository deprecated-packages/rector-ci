<?php declare(strict_types=1);

namespace Rector\RectorCI\Controller;

use Github\Client as Github;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DashboardController extends AbstractController
{
    /**
     * @var Github
     */
    private $github;


    public function __construct(Github $github)
    {
        $this->github = $github;
    }


    /**
     * @Route("/app/dashboard", name="dashboard", methods={"GET"})
     */
    public function __invoke(): Response
    {
        $this->github->

        return $this->render('dashboard/dashboard.twig');
    }
}
