<?php declare(strict_types=1);

namespace Rector\RectorCI\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Rector\RectorCI\Entity\RectorSet;

final class RectorSetsFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $objectManager): void
    {
        for ($i = 0; $i <= 10; $i++) {
            $set = new RectorSet(Uuid::uuid4(), 'set-' . $i, 'Set #' . ($i + 1));

            $objectManager->persist($set);
        }

        $objectManager->flush();
    }

    /**
     * @return string[]
     */
    public static function getGroups(): array
    {
        return [FixtureGroupName::BASE_DATA];
    }
}
