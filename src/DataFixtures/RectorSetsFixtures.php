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

    /*
     * @TODO: following sets exists:
      * action-injection-to-constructor-injection
      * array-str-functions-to-static-call
      * celebrity
      * code-quality
      * coding-style
      * contributte-to-symfony
      * dead-code
      * doctrine
      * doctrine-repository-as-service
      * jms-decouple
      * kdyby-to-symfony
      * laravel-static-to-injection
      * mysql-to-mysqli
      * nette-control-to-symfony-controller
      * nette-forms-to-symfony
      * nette-tester-to-phpunit
      * nette-to-symfony
      * nette-utils-code-quality
      * php-di-decouple
      * phpspec-to-phpunit
      * phpstan
      * phpunit-code-quality
      * phpunit-exception
      * phpunit-mock
      * phpunit-specific-method
      * phpunit-yield-data-provider
      * silverstripe
      * solid
      * symfony-code-quality
      * symfony-constructor-injection
      * symfony-phpunit
      * twig-underscore-to-namespace
      * type-declaration
     */
}
