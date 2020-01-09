<?php declare(strict_types=1);

namespace Rector\RectorCI\Controller;

use Github\Client as Github;
use Github\ResultPager;
use Psr\Cache\CacheItemPoolInterface;
use Rector\RectorCI\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DashboardController extends AbstractController
{
    /**
     * @var Github
     */
    private $github;

    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;

    public function __construct(Github $github, CacheItemPoolInterface $cacheItemPool)
    {
        $this->github = $github;
        $this->cacheItemPool = $cacheItemPool;
    }

    /**
     * @Route("/app/dashboard", name="dashboard", methods={"GET"})
     */
    public function __invoke(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $this->github->authenticate($user->getGithubAccessToken(), null, Github::AUTH_HTTP_TOKEN);
        $this->github->addCache($this->cacheItemPool);

        $installedRepositories = $this->getInstalledRepositories();

        $pager = new ResultPager($this->github);
        $repositories = $pager->fetchAll($this->github->currentUser(), 'repositories', ['all']);

        $repositories = array_filter($repositories, static function (array $repository) use (
            $installedRepositories
        ): bool {
            foreach ($installedRepositories as $installedRepository) {
                if ($installedRepository['id'] === $repository['id']) {
                    return false;
                }
            }

            return true;
        });

        return $this->render('dashboard/dashboard.twig', [
            'installedRepositories' => $installedRepositories,
            'repositories' => $repositories,
        ]);
    }

    private function getInstalledRepositories(): array
    {
        $userInstallations = $this->github->currentUser()->installations();
        $installedRepositories = [];

        foreach ($userInstallations['installations'] as $installation) {
            $repositoriesByInstallation = $this->github->currentUser()->repositoriesByInstallation($installation['id']);
            $installedRepositories = array_merge($installedRepositories, $repositoriesByInstallation['repositories']);
        }

        return $installedRepositories;
    }
}
