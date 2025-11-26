<?php
declare(strict_types=1);

namespace SSync\Traits;

use Exception;
use PDOException;

trait ErrorTrait
{
    function error(string $message, ?PDOException $e = null): void
    {
        throw new Exception($e ? "$message: {$e->getMessage()}" : $message);
    }
}
