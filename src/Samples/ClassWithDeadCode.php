<?php declare(strict_types=1);

namespace Rector\RectorCI\Samples;

final class ClassWithDeadCode
{
    private function unusedPrivateMethod(): void
    {
    }
}
