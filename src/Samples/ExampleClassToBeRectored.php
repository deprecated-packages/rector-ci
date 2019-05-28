<?php declare (strict_types=1);

namespace RectorCI\Samples;

final class ExampleClassToBeRectored
{
    public static function createException(): \RectorCI\Samples\ExampleException
    {
        return new \RectorCI\Samples\ExampleException();
    }
}
