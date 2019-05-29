<?php declare (strict_types=1);

namespace Rector\RectorCI\Samples;

final class ExampleClassToBeRectored
{
    public static function createException(): ExampleException
    {
        return new ExampleException();
    }
}
