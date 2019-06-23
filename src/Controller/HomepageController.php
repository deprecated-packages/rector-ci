<?php declare(strict_types=1);

namespace Rector\RectorCI\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HomepageController extends AbstractController
{
    /**
     * @var string
     */
    private $clientId;


    public function __construct(string $clientId)
    {
        $this->clientId = $clientId;
    }


    /**
     * @Route("/", methods={"GET"})
     */
    public function __invoke(): Response
    {
        return $this->render('homepage/homepage.twig', [
            'githubClientId' => $this->clientId,
        ]);
    }
}
