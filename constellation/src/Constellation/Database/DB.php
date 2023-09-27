<?php

namespace Constellation\Database;

use Constellation\Container\Container;
use Constellation\Validation\Validate;
use Constellation\Alerts\Flash;
use Error;
use PDO;
use PDOException;

/**
 * @class DB
 */
class DB
{
    protected static $instance;
    private $pdo;
    private $time;
    public $num_queries = 0;
    public $total_time = 0;
    public $trace_counts = [];
    public $stmt = null;
    private $show_errors;
    private $mode;

    public function __construct(private array $config)
    {
        Validate::keys($this->config, ["type"]);
        $this->show_errors = isset($this->config["show_errors"])
            ? $this->config["show_errors"]
            : false;
        $this->establishConnection();
    }

    public function __call($method, $args)
    {
        return $this->pdo->$method(...$args);
    }

    public function getMode()
    {
        return $this->mode;
    }

    public static function sql()
    {
        return self::getInstance();
    }

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = Container::getInstance()->get(DB::class);
        }

        return static::$instance;
    }

    private function establishConnection()
    {
        if (!isset($this->config["type"]) || $this->config["type"] == "none") {
            return null;
        }
        match ($this->config["type"]) {
            "mysql" => $this->mysql(),
            "pgsql" => $this->pgsql(),
            "sqlite" => $this->sqlite(),
        };
    }

    private function mysql()
    {
        Validate::keys($this->config, [
            "host",
            "port",
            "dbname",
            "username",
            "password",
        ]);
        extract($this->config);
        $dsn = sprintf(
            "mysql:host=%s;port=%s;dbname=%s",
            $host,
            $port,
            $dbname
        );
        $options = [];
        if (defined("MYSQL_ATTR_FOUND_ROWS")) {
            $options[PDO::MYSQL_ATTR_FOUND_ROWS] = true;
        }
        $this->mode = "mysql";
        $this->pdo = new PDO($dsn, $username, $password, $options);
    }

    private function pgsql()
    {
        Validate::keys($this->config, [
            "host",
            "port",
            "dbname",
            "username",
            "password",
        ]);
        extract($this->config);
        $dsn = sprintf(
            "pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s;",
            $host,
            $port,
            $dbname,
            $username,
            $password
        );
        $this->mode = "pgsql";
        $this->pdo = new PDO($dsn);
    }

    private function isCommandLineInterface()
    {
        return php_sapi_name() === "cli";
    }

    private function sqlite()
    {
        Validate::keys($this->config, ["path"]);
        extract($this->config);
        $dsn = sprintf("sqlite:%s", $path);
        $this->mode = "sqlite";
        $this->pdo = new PDO($dsn);
    }

    public function run(string $query, $args = null)
    {
        if (!trim($query)) {
            return $this->stmt;
        }
        if (!$this->pdo) {
            throw new Error("No established database connection");
        }
        $this->time = microtime(true);
        try {
            if (!$args) {
                $stmt = $this->pdo->query($query);
            } elseif (is_array($args) && !empty($args)) {
                $stmt = $this->pdo->prepare($query);
                $stmt->execute($args);
            }
        } catch (PDOException $pe) {
            if ($this->show_errors) {
                $msg = "<strong>PDOException</strong><br>";
                $msg .= "<strong>Query:</strong> <i>$query</i><br>";
                $msg .= "<strong>Message:</strong> <i>{$pe->getMessage()}</i><br>";
                Flash::addFlash("database", $msg);
            }
            error_log($query);
            error_log($pe->getMessage());
            error_log($pe->getFile() . ":" . $pe->getLine());
        }
        $this->time = microtime(true) - $this->time;
        if ($this->time > 1) {
            error_log("DB: slow query took {$this->time}: $query");
        }
        $this->num_queries++;
        $this->total_time += $this->time;
        if ($this->show_errors && !$this->isCommandLineInterface()) {
            $traces = debug_backtrace(0);
            $i = 0;
            while ($traces[$i]["class"] == "Constellation\Database\DB") {
                $i++;
            }
            $key =
                $traces[$i]["file"] .
                " @ " .
                $traces[$i]["line"] .
                " (" .
                $traces[$i]["function"] .
                ")";

            if (!isset($this->trace_counts[$key]["count"])) {
                $this->trace_counts[$key]["count"] = 1;
            } else {
                $this->trace_counts[$key]["count"]++;
            }

            if (!isset($this->trace_counts[$key]["time"])) {
                $this->trace_counts[$key]["time"] = $this->time;
            } else {
                $this->trace_counts[$key]["time"] += $this->time;
            }
        }
        $this->stmt = $stmt ?? null;
        return $this->stmt;
    }

    public function query(string $query, ...$args)
    {
        return count($args) > 0
            ? $this->run($query, $args)
            : $this->run($query);
    }

    public function selectOne(string $query, ...$args)
    {
        $stmt = $this->run($query, $args);
        return $stmt ? $stmt->fetchObject() : null;
    }

    public function selectMany(string $query, ...$args)
    {
        $stmt = $this->run($query, $args);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_OBJ) : null;
    }

    public function selectVar(string $query, ...$args)
    {
        $result = (array) $this->selectOne($query, ...$args);
        $var = array_values($result);
        return isset($var[0]) ? $var[0] : null;
    }

    public function selectCol(string $query, ...$args)
    {
        $results = (array) $this->selectMany($query, ...$args);
        if ($results) {
            $cols = [];
            foreach ($results as $one) {
                $result = (array) $one;
                $var = array_values($result);
                if (isset($var[0])) {
                    $cols[] = $var[0];
                }
            }
            return $cols;
        }
        return null;
    }

    public function getColumnNames()
    {
        $names = [];
        if (is_null($this->stmt)) {
            return $names;
        }
        foreach (range(0, $this->stmt->columnCount() - 1) as $column_index) {
            if ($column_index > -1) {
                $names[] = $this->stmt->getColumnMeta($column_index)["name"];
            }
        }
        return $names;
    }
}
