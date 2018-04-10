<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/3/1
 * Time: 13:54
 */

namespace Surf\Database;

use Surf\Database\Connections\MysqlConnection;
use PDO;
use PDOException;

class ConnectionFactory
{
    /**
     * @param array $config
     * @param $name
     * @return null|MysqlConnection
     */
    public function make(array $config, $name)
    {
        if (!in_array($config['driver'], $this->supportDriver())) {
            throw new \InvalidArgumentException("Not driver");
        }
        return $this->createSingleConnection($config, $name);
    }

    /**
     * @param array $config
     * @param $name
     * @return null|MysqlConnection
     */
    protected function createSingleConnection(array $config, $name)
    {
        $pdoConnection = $this->getPdoResolver($config);
        return $this->createConnection(
            $config['driver'],
            $pdoConnection,
            $config['database'],
            $config,
            $config['prefix'] ?? '',
            $name
        );
    }

    /**
     * @param string $driver
     * @param callable $pdo
     * @param string $database
     * @param array $config
     * @param string $prefix
     * @param string $name
     * @return null|MysqlConnection
     */
    protected function createConnection(
        string $driver,
        callable $pdo,
        string $database,
        array $config,
        string $prefix = '',
        string $name = 'default'
    ) {
        $connection = null;
        switch ($driver) {
            case 'mysql':
                $connection = new MysqlConnection($pdo, $database, $config, $prefix, $name);
                break;
        }
        return $connection;
    }

    /**
     * @param array $config
     * @return \Closure
     */
    protected function getPdoResolver(array $config)
    {
        return function () use ($config) {
            $dns = $config['driver'] . ':dbname=' . $config['database'] . ';' .
                (
                    isset($config['socket'])
                    ? 'unix_socket=' . $config['socket']
                    : 'host=' . $config['host']
                );
            $dns .= ';port=' . ($config['port'] ?? 3306);
            $options = $config['options'] ?? [];
            $defaultOptions = [
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'' . ($config['charset'] ?? 'utf8mb4') . '\'',
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ] + $options;
            try {
                $pdo = new PDO($dns, $config['username'], $config['password'], $defaultOptions);
            } catch (PDOException $e) {
                throw $e;
            }
            return $pdo;
        };
    }

    /**
     * @return array
     */
    protected function supportDriver()
    {
        return [
            'mysql'
        ];
    }
}
