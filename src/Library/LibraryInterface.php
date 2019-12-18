<?php
declare(strict_types=1);

namespace Alliance\Library;

use Doctrine\DBAL\Driver\Connection;

interface LibraryInterface
{
    public function createDatabase() : void;
    public function destroyDatabase() : void;
    public function getConnection() : Connection;
}