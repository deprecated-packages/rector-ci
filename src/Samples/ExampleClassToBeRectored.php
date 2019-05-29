<?php declare (strict_types=1);

namespace Rector\RectorCI\Samples;

final class ExampleClassToBeRectored
{
    public static function createException(): \Rector\RectorCI\Samples\ExampleException
    {
        return new \Rector\RectorCI\Samples\ExampleException();
    }
}
