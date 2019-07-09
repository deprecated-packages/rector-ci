<?php declare(strict_types=1);

namespace Rector\RectorCI\DateTime;

use DateTimeImmutable;

final class DateTimeProvider
{
    public function provideNow(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
