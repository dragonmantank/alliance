<?php
declare(strict_types=1);

namespace Alliance;

use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Query\QueryBuilder;

class DbTestCase extends TestCase
{
    /**
     * @var Connection
     */
    static protected $conn;

    /**
     * Set the DBAL connection for DbTestCases to use
     */
    static public function setConnection(Connection $conn) : void
    {
        static::$conn = $conn;
    }

    /**
     * Common code for building a select query with DBAL
     */
    protected function buildSelectQuery(string $table, array $query) : QueryBuilder
    {
        /** @var QueryBuilder $qb */
        $qb = static::$conn->createQueryBuilder();

        $qb = $qb->select(array_keys($query))
            ->from($table)
        ;

        $paramCounter = 0;
        foreach ($query as $column => $value) {
            $qb = $qb->andWhere($column . ' = ?')
                ->setParameter($paramCounter, $value);
            $paramCounter++;
        }

        return $qb;
    }

    /**
     * Assert that the specified query returns 0 rows
     */
    protected function assertNotInTable(string $table, array $query) : void
    {
        $qb = $this->buildSelectQuery($table, $query);
        $response = $qb->execute();

        if (SqlitePlatform::class == get_class(static::$conn->getDatabasePlatform())) {
            $this->assertSame(0, count($response->fetchAll()));
        } else {
            $this->assertSame(0, $response->rowCount());
        }
    }

    /**
     * Assert that the specified query only returns 1 row
     */
    protected function assertSingleRowInTable(string $table, array $query) : void
    {
        $qb = $this->buildSelectQuery($table, $query);
        $response = $qb->execute();

        if (SqlitePlatform::class == get_class(static::$conn->getDatabasePlatform())) {
            $this->assertSame(1, count($response->fetchAll()));
        } else {
            $this->assertSame(1, $response->rowCount());
        }
    }
}
