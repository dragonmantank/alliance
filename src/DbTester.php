<?php
declare(strict_types=1);

namespace Alliance;

use Alliance\Library\Phinx;
use PHPUnit\Runner\BeforeTestHook;
use PHPUnit\Runner\AfterLastTestHook;
use Alliance\Library\LibraryInterface;

class DbTester implements BeforeTestHook, AfterLastTestHook
{
    /**
     * @var LibraryInterface
     */
    protected $library;

    public function __construct(string $library, array $options = [])
    {
        $configFilePath = $GLOBALS['__PHPUNIT_CONFIGURATION_FILE'];
        
        $options['phpunit_config_path'] = $configFilePath;

        if ($library === "phinx") {
            $this->library = new Phinx($options);
        } else {
            throw new \RuntimeException("Unknown migration library");
        }
    }

    /**
     * Clean out the database once we are done running tests
     * Since we normally clean up the DB before tests are run, this makes sure
     * the DB is clean for future runs.
     */
    public function executeAfterLastTest(): void
    {
        $this->library->destroyDatabase();
    }

    /**
     * Prep the DbTestCase and database for work
     * This is a bit heavy-handed, but gets around needing the user to create
     * the connection themselves in a `setUp()` method. This also initializes
     * the database for us before every test.
     */
    public function executeBeforeTest(string $test) : void
    {
        $chunks = explode('::', $test, 2);
        if (is_subclass_of($chunks[0], DbTestCase::class)) {
            $chunks[0]::setConnection($this->library->getConnection());
            $this->library->createDatabase();
        }
    }
}