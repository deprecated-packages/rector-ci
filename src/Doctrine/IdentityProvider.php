<?php declare(strict_types=1);

namespace Rector\RectorCI\Doctrine;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class IdentityProvider
{
    public function provide(): UuidInterface
    {
        return Uuid::uuid4();
    }
}
