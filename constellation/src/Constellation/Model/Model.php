<?php

namespace Constellation\Model;

use Constellation\Database\DB;
use Exception;
use PDO;

class Model
{
    private array $attributes = [];
    private bool $loaded = false;
    protected DB $db;
    private string $query = "";
    private array $args = [];
    private string $class;
    public function __construct(
        private string $table,
        private array $key,
        private ?array $id = null
    ) {
        $this->db = DB::getInstance();
        $this->loadAttributes();
    }

    /**
     * Find model by id
     * @param ?array $id
     */
    public static function find(?array $id)
    {
        $class = static::class;
        $model = new $class($id);
        return $model->isLoaded() ? $model : null;
    }

    /**
     * Find all models
     */
    public static function findAll()
    {
        $class = static::class;
        $model = new $class();
        $results = $model->db->selectMany(
            "SELECT *
            FROM $model->table"
        );
        $models = [];
        if ($results) {
            foreach ($results as $result) {
                $id = [];
                foreach ($model->key as $key) {
                    $id[] = $result->$key ?? null;
                }
                $models[] = new $class($id);
            }
        }
        return $models ? $models : null;
    }

    /**
     * Find model or throw exception
     * @param ?array $id
     */
    public static function findOrFail(?array $id)
    {
        $class = static::class;
        $model = $class::find($id);
        return !is_null($model)
            ? $model
            : throw new Exception("Model not found");
    }

    /**
     * Find model by attribute
     * @param string $attribute
     * @param string $value
     * @param bool $like
     */
    public static function findByAttribute(
        string $attribute,
        string $value,
        bool $like = false
    ) {
        $class = static::class;
        $model = new $class();
        $op = $like ? "LIKE" : "=";
        $value = $like ? "%$value%" : $value;
        $result = $model->db->selectOne(
            "SELECT *
            FROM $model->table
            WHERE $attribute {$op} ?",
            $value
        );
        if ($result) {
            // Note: $key and $id args are array lists (model)
            $id = [];
            foreach ($model->key as $key) {
                $id[] = $result->$key ?? null;
            }
        }
        return $result ? new $class($id) : null;
    }

    /**
     * Return first model in db
     */
    public static function first()
    {
        $class = static::class;
        $models = $class::findAll();
        return $models[0] ?? null;
    }

    /**
     * Get array of model attribute values
     * @param string $column
     * @return ?array
     */
    public static function pluck(string $column): ?array
    {
        $class = static::class;
        $model = new $class();
        $results = $model->db->selectCol(
            "SELECT $column
            FROM $model->table
            ORDER BY $column"
        );
        return $results;
    }

    /**
     * Model select (model query)
     * @param array $columns
     * @return self
     */
    public static function select(array $columns): self
    {
        $class = static::class;
        $model = new $class();
        foreach ($model->key as $key) {
            if (!in_array($key, $columns)) {
                $columns[] = $key;
            }
        }
        $model->class = $class;
        $model->query = "SELECT " . implode(", ", $columns);
        $model->query .= " FROM {$model->table}";
        return $model;
    }

    /**
     * Model where (model query)
     * @param array $clauses
     * @param array $args
     * @return self
     */
    public function where(array $clauses, array $args = []): self
    {
        $this->query .= " WHERE " . implode(" AND ", $clauses);
        $placeholder_count = substr_count($this->query, "?");
        $arg_count = count($args);
        if ($placeholder_count !== $arg_count) {
            throw new Exception(
                "Placeholder '?' does not match argument count"
            );
        }
        $this->args = [...$args];
        return $this;
    }

    /**
     * Model order by (model query)
     * @param string $clause
     * @return self
     */
    public function order(string $clause): self
    {
        $this->query .= " ORDER BY " . $clause;
        return $this;
    }

    /**
     * Model having (model query)
     * @param string $clause
     * @return self
     */
    public function having(string $clause): self
    {
        $this->query .= " HAVING " . $clause;
        return $this;
    }

    /**
     * Model group by (model query)
     * @param string $clause
     * @return self
     */
    public function group_by(string $clause): self
    {
        $this->query .= " GROUP BY " . $clause;
        return $this;
    }

    /**
     * Get the model query
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * Run the model query
     * @return ?array
     */
    public function run(): ?array
    {
        $results = $this->db->run($this->query, $this->args)->fetchAll();
        $models = [];
        if ($results) {
            foreach ($results as $result) {
                $result = (object) $result;
                $id = [];
                foreach ($this->key as $key) {
                    $id[] = $result->$key ?? null;
                }
                $class = $this->class;
                $models[] = new $class($id);
            }
        }
        return $models ? $models : null;
    }

    /**
     * Create a new model
     * @param array $attributes
     */
    public static function create(array $attributes)
    {
        $class = static::class;
        $model = new $class();
        return $model->insert($attributes);
    }

    /**
     * Remove a model
     * @param ?array $id
     */
    public static function remove(?array $id)
    {
        $class = static::class;
        $model = new $class($id);
        if ($model && $model->delete()) {
            return true;
        }
        return null;
    }

    /**
     * Refresh a model attributes
     * Alias of loadAttributes
     */
    public function refresh()
    {
        $this->loadAttributes();
    }

    /**
     * Are the model attributes loaded
     * @return bool
     */
    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    /**
     * Load the model attributes
     */
    public function loadAttributes()
    {
        if ($this->id) {
            $where_clause = $this->stmt($this->key, " AND ");
            $result = $this->db
                ->run(
                    "SELECT *
                FROM $this->table
                WHERE $where_clause",
                    $this->id
                )
                ?->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                $this->attributes = $result[0];
                $this->loaded = true;
            }
        } else {
            if ($this->db->getMode() !== "sqlite") {
                $result = $this->db
                    ->run("DESCRIBE $this->table")
                    ?->fetchAll(PDO::FETCH_COLUMN);
                if ($result) {
                    foreach ($result as $one) {
                        $this->attributes[$one] = null;
                    }
                }
            } else {
                $result = $this->db
                    ->run("PRAGMA table_info($this->table)")
                    ?->fetchAll(PDO::FETCH_COLUMN, 1);
                if ($result) {
                    foreach ($result as $one) {
                        $this->attributes[$one] = null;
                    }
                }
            }
        }
    }

    /**
     * Construct sql statement from array
     * @param array $list
     * @param array|string $seperator
     * @return string
     */
    private function stmt(array $list, array|string $seperator): string
    {
        foreach ($list as $one) {
            $clause[] = "$one = ?";
        }
        return implode($seperator, $clause);
    }

    /**
     * Return the model attributes
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Return model attributes keys
     * @return array
     */
    private function attributeKeys(): array
    {
        return array_keys($this->attributes);
    }

    /**
     * Return model attributes values
     * @return array
     */
    private function attributeValues(): array
    {
        return array_values($this->attributes);
    }

    /**
     * Insert new model with attributes
     * @param array $attributes
     * @return mixed
     */
    public function insert(array $attributes): mixed
    {
        $columns = array_keys($attributes);
        $values = array_values($attributes);
        $update_statement = $this->stmt($columns, ", ");
        $result = $this->db->run(
            "INSERT INTO $this->table
            SET $update_statement",
            $values
        );
        if ($result) {
            $this->refresh();
        }
        return $result;
    }

    /**
     * Update model attributes
     * @param array $attributes
     * @return mixed
     */
    public function update(array $attributes): mixed
    {
        $columns = array_keys($attributes);
        $values = array_values($attributes);
        $update_statement = $this->stmt($columns, ", ");
        $where_clause = $this->stmt($this->key, " AND ");
        $result = $this->db->run(
            "UPDATE $this->table
            SET $update_statement
            WHERE $where_clause",
            [...$values, ...$this->id]
        );
        if ($result) {
            $this->refresh();
        }
        return $result;
    }

    /**
     * Delete model
     * @return mixed
     */
    public function delete(): mixed
    {
        $where_clause = $this->stmt($this->key, " AND ");
        $result = $this->db->run(
            "DELETE FROM $this->table
            WHERE $where_clause",
            $this->id
        );
        if ($result) {
            $this->id = null;
            $this->refresh();
        }
        return $result;
    }

    public function __get($name)
    {
        return $this->attributes[$name];
    }

    public function __set($name, $value)
    {
        return $this->attributes[$name] = $value;
    }
}
