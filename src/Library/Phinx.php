<?php
declare(strict_types=1);

namespace Alliance\Library;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

class Phinx
{
    protected $environment = "testing";
    protected $configFile = 'phinx.yml';
    protected $configFilePath;
    protected $phpUnitConfigPath;
    protected $connectionSettings = [];

    public function __construct($options)
    {
        $this->environment = $options['environment'] ?? $this->environment;
        $this->configFile = $options['config_file'] ?? $this->configFile;

        $this->phpUnitConfigPath = dirname($options['phpunit_config_path']);
        $this->configFilePath = $this->phpUnitConfigPath . '/' . $this->configFile;
    }

    /**
     * @todo Move this away from exec
     */
    public function createDatabase() : void
    {
        $this->destroyDatabase();
        
        exec(
            sprintf(
                'php vendor/bin/phinx migrate -e %s -c %s',
                escapeshellarg($this->environment),
                escapeshellarg($this->configFilePath)
            )
        );

        exec(
            sprintf(
                'php vendor/bin/phinx seed:run -e %s -c %s',
                escapeshellarg($this->environment),
                escapeshellarg($this->configFilePath)
            )
        );
    }

    /**
     * @todo Move this away from exec
     */
    public function destroyDatabase() : void
    {
        if ($this->connectionSettings['driver'] == 'pdo_sqlite') {
            @unlink($this->connectionSettings['path']);
        } else {
            exec(
                sprintf(
                    'php vendor/bin/phinx rollback -e %s -c %s -t 0',
                    escapeshellarg($this->environment),
                    escapeshellarg($this->configFilePath)
                )
            );
        }
    }

    /**
     * @todo Add in the rest of the supported drivers
     */
    public function getConnection() : Connection
    {
        $config = Yaml::parseFile($this->configFilePath);
        $envConfig = $config['environments'][$this->environment];
        $connectionSettings = [
            'dbname' => $envConfig['name'],
        ];

        switch($envConfig['adapter']) {
            case 'mysql':
                $connectionSettings['driver'] = 'pdo_mysql';
                $connectionSettings['host'] = $envConfig['host'];
                $connectionSettings['user'] = $envConfig['user'];
                $connectionSettings['password'] = $envConfig['pass'];
                break;
            case "sqlite":
                $connectionSettings['driver'] = 'pdo_sqlite';
                $connectionSettings['path'] = $this->phpUnitConfigPath . '/' . $envConfig['name'] . $envConfig['suffix'];
                break;
            default:
                throw new RuntimeException("Unknown database adapter for Phinx supplied");
        }

        $this->connectionSettings = $connectionSettings;
        return DriverManager::getConnection($connectionSettings, new Configuration());
    }
}
