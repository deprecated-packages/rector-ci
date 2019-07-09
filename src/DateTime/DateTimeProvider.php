<?php declare(strict_types=1);

namespace Rector\RectorCI\DateTime;

final class DateTimeProvider
{
    public function provideNow(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
