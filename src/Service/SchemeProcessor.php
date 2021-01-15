<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

class SchemeProcessor implements EnvVarProcessorInterface
{
    public function getEnv(string $prefix, string $name, \Closure $getEnv)
    {
        $env = $getEnv($name);

        return strtolower(explode(':', $env, 2)[0]);
    }

    public static function getProvidedTypes()
    {
        return [
            'scheme' => 'string',
        ];
    }
}
