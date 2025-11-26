<?php
declare(strict_types=1);

namespace SSync\Traits;

trait ConfigTrait
{
    use ErrorTrait;

    protected function getConfig(string $config, array $required = []): array
    {
        $path = realpath(__DIR__ . '/../../config');
        $file = "$path/$config/config.php";
        $config = @include $file;

        if (!$config || !is_array($config)) {
            $this->error("Failed to load configuration file: $file");
        }

        $required = array_unique(array_merge($required, ['root1', 'root2']));
        $missing = array_filter(
            $required,
            fn($field) => !isset($config[$field]),
        );
        if (!empty($missing)) {
            $missing = implode(', ', $missing);
            $this->error("Missing required configuration: $missing");
        }

        foreach (['root1', 'root2'] as $root) {
            $path = rtrim(realpath($config[$root]), '/');
            if (!is_dir($path)) {
                $this->error(
                    "Path $root does not exist or is not a directory: $path",
                );
            }
            $config[$root] = $path;
        }

        $config['configDir'] = dirname($file);

        return $config;
    }
}
