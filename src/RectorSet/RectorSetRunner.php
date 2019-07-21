<?php declare(strict_types=1);

namespace Rector\RectorCI\RectorSet;

use Symfony\Component\Process\Process;

final class RectorSetRunner
{
    /**
     * @var string
     */
    private $pathToRectorBinary;


    public function __construct(string $pathToRectorBinary)
    {
        $this->pathToRectorBinary = $pathToRectorBinary;
    }


    public function runSetOnDirectory(string $setName, string $directory): Process
    {
        // @TODO: determine what directories to search, recursive search for common used code directories? (src, packages/**/src, tests), or create .rector-ci.yaml?

        $rectorProcess = new Process([
            'php',
            $this->pathToRectorBinary,
            'process',
            'src',
            '--output-format=json',
        ], $directory, [
            'APP_ENV' => false,
            'APP_DEBUG' => false,
            'SYMFONY_DOTENV_VARS' => false,
        ]);
        $rectorProcess->setTimeout(null);
        $rectorProcess->mustRun();

        return $rectorProcess;
    }
}
