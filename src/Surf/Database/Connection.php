<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/3/1
 * Time: 14:14
 */

namespace Surf\Database;

use Closure;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Class Connection
 * @package Surf\Database
 */
abstract class Connection
{

    /**
     * @var null|PDO
     */
    protected $pdo = null;

    /**
     * @var null
     */
    protected $database = null;

    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var int
     */
    protected $defaultFetchMode = PDO::FETCH_OBJ;

    /**
     * @var array
     */
    protected $log = [];


    /**
     * @var bool
     */
    protected $enableQueryLog = false;

    /**
     * @var null
     */
    protected $reconnectResolver = null;

    /**
     * @var string
     */
    protected $name = 'default';
    /**
     * Connection constructor.
     * @param callable $pdo
     * @param $database
     * @param $config
     * @param string $prefix
     */
    public function __construct(callable $pdo, $database, $config, $prefix = '', $name = 'default')
    {
        $this->pdo = $pdo;

        $this->database = $database;

        $this->config = $config;

        $this->prefix = $prefix;

        $this->name = $name;
    }

    /**
     * @param $query
     * @param array $bindings
     * @return mixed
     * @throws \Exception
     */
    public function cursor(string $query, array $bindings = [])
    {
        $statement = $this->run($query, $bindings, function ($query, array $bindings) {
            $statement = $this->setFetchMode($this->getPdo()->prepare($query));
            $this->bindValues($statement, $bindings);
            $statement->execute();
            return $statement;
        });
        while (($rows = $statement->fetch($this->defaultFetchMode, PDO::FETCH_ORI_NEXT))) {
            yield $rows;
        }
    }

    /**
     * @param string $query
     * @param array $bindings
     * @return mixed
     * @throws \Exception
     */
    public function fetchColumn(string $query, array $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, array $bindings) {
            $statement = $this->setFetchMode($this->getPdo()->prepare($query));
            $this->bindValues($statement, $bindings);
            $statement->execute();
            return $statement->fetchColumn();
        });
    }

    /**
     * @param $query
     * @param array $bindings
     * @return mixed
     * @throws \Exception
     */
    public function select(string $query, array $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, array $bindings) {
            $statement = $this->setFetchMode($this->getPdo()->prepare($query));

            $this->bindValues($statement, $bindings);

            $statement->execute();
            return $statement->fetchAll();
        });
    }

    /**
     * @param $query
     * @param array $bindings
     * @return mixed
     * @throws \Exception
     */
    public function statement(string $query, array $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, array $bindings) {
            $statement = $this->getPdo()->prepare($query);
            $this->bindValues($statement, $bindings);
            return $statement->execute();
        });
    }

    /**
     * @param string $query
     * @param array $bindings
     * @return mixed
     * @throws \Exception
     */
    public function affectingStatement(string $query, array $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, array $bindings) {
            $statement = $this->getPdo()->prepare($query);
            $this->bindValues($statement, $bindings);
            $statement->execute();
            return $statement->rowCount();
        });
    }

    /**
     * @param $query
     * @param array $bindings
     * @return mixed
     * @throws \Exception
     */
    public function insert(string $query, array $bindings = [])
    {
        return $this->statement($query, $bindings);
    }

    /**
     * @param string $query
     * @param array $bindings
     * @return mixed
     * @throws \Exception
     */
    public function update(string $query, array $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * @param $query
     * @param array $bindings
     * @return mixed
     * @throws \Exception
     */
    public function delete(string $query, array $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * @param $query
     * @param array $bindings
     * @param Closure $closure
     * @return mixed
     * @throws \Exception
     */
    protected function run(string $query, array $bindings, Closure $closure)
    {
        $start = microtime(true);
        try {
            $result = $this->runQueryCallback($query, $bindings, $closure);
        } catch (PDOException $e) {
            $result = $this->isDisconnectException($e, $query, $bindings, $closure);
        }
        $end = microtime(true);
        $this->log[] = $query;
        return $result;
    }

    /**
     * @param $query
     * @param array $bindings
     * @param Closure $callback
     * @return mixed
     */
    protected function runQueryCallback($query, array $bindings, Closure $callback)
    {
        try {
            $result = $callback($query, $bindings);
        } catch (PDOException $e) {
            throw $e;
        }
        return $result;
    }
    /**
     * @param $statement PDOStatement
     * @param array $values
     * @return PDOStatement
     */
    protected function bindValues(PDOStatement $statement, array $values)
    {
        foreach ($values as $key => $value) {
            $statement->bindValue(
                is_numeric($key) ? $key + 1 : $key,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
        return $statement;
    }

    /**
     * @return string
     */
    public function lastQuery()
    {
        return end($this->log);
    }

    /**
     * @param PDOException $e
     * @param string $query
     * @param array $bindings
     * @param Closure $callback
     * @return mixed
     */
    protected function isDisconnectException(PDOException $e, string $query, array $bindings, Closure $callback)
    {
        $message = $e->getMessage();

        if ($this->isContainsDisconnectMessage($message)) {
            $this->reconnect();
            return $this->runQueryCallback($query, $bindings, $callback);
        }

        throw $e;
    }

    /**
     * @param string $message
     * @return bool
     */
    protected function isContainsDisconnectMessage(string $haystack)
    {
        foreach ([
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'server closed the connection unexpectedly',
            'SSL connection has been closed unexpectedly',
            'Error writing data to the connection',
            'Resource deadlock avoided',
        ] as $needle) {
            if (strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param bool $state
     */
    public function setEnableQueryLog(bool $state)
    {
        $this->enableQueryLog = $state;
    }

    /**
     * @return bool
     */
    public function reconnect()
    {
        $this->disconnect();
        if ($this->reconnectResolver) {
            $pdo = call_user_func($this->reconnectResolver, $this);
            $this->setPdo($pdo);
            return true;
        }
        return false;
    }

    /**
     * @return $this
     */
    public function disconnect()
    {
        $this->setPdo(null);
        return $this;
    }

    /**
     * @param $pdo
     * @return $this
     */
    public function setPdo($pdo)
    {
        $this->pdo = $pdo;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * @param callable $resolver
     */
    public function setReconnectResolver(callable $resolver)
    {
        $this->reconnectResolver = $resolver;
    }
    /**
     * @return array
     */
    public function log()
    {
        return $this->log;
    }
    /**
     * @param $statement PDOStatement
     * @return PDOStatement
     */
    protected function setFetchMode(PDOStatement $statement)
    {
        $statement->setFetchMode($this->defaultFetchMode);
        return $statement;
    }

    /**
     * @return mixed|PDO
     */
    public function getPdo()
    {
        if ($this->pdo instanceof Closure) {
            return $this->pdo = call_user_func($this->pdo);
        }
        return $this->pdo;
    }

    /**
     *
     */
    public function __clone()
    {
        // TODO: Implement __clone() method.

        $this->reconnectResolver = clone $this->reconnectResolver;

        $this->pdo = clone $this->pdo;
    }
}
