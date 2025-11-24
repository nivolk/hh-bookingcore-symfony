<?php

declare(strict_types=1);

namespace App\Runtime;

use Symfony\Component\Runtime\SymfonyRuntime;

final class DockerSymfonyRuntime extends SymfonyRuntime
{
    public function __construct(array $options = [])
    {
        $projectDir = $options['project_dir'] ?? dirname(__DIR__, 2);
        $dotenvPath = $projectDir . '/.env';

        if (!is_file($dotenvPath)) {
            $options['disable_dotenv'] = true;
        }

        parent::__construct($options);
    }
}
