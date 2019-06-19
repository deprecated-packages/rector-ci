<?php declare(strict_types=1);

namespace Rector\RectorCI\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HomepageController extends AbstractController
{
    /**
     * @Route("/", methods={"GET"})
     */
    public function __invoke(): Response
    {
        return $this->render('homepage/homepage.twig');
    }
}
