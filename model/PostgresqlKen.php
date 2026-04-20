<?php
set_time_limit(0);
ini_set('memory_limit', '-1');
error_reporting(E_ALL);

class PostgresqlKen
{
    private $conn;

    function __construct()
    {
        $this->loadEnv(__DIR__ . '/.env');

        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $dbname = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASS'];

        $this->conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

        if (!$this->conn) {
            throw new Exception("Connection failed: " . pg_last_error());
        }
    }

    private function loadEnv($path)
    {
        if (!file_exists($path)) {
            throw new Exception(".env file not found at: $path");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;

            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }

    /** TRANSACTION METHODS */
    public function beginTransaction()
    {
        $result = pg_query($this->conn, "BEGIN");
        if (!$result) {
            throw new Exception("Failed to start transaction: " . pg_last_error($this->conn));
        }
        return $result;
    }

    public function commit()
    {
        $result = pg_query($this->conn, "COMMIT");
        if (!$result) {
            throw new Exception("Failed to commit transaction: " . pg_last_error($this->conn));
        }
        return $result;
    }

    public function rollBack()
    {
        $result = pg_query($this->conn, "ROLLBACK");
        if (!$result) {
            throw new Exception("Failed to rollback transaction: " . pg_last_error($this->conn));
        }
        return $result;
    }

    /** QUERY METHODS */
    // public function query($sql, $params = [])
    // {
    //     if (!empty($params)) {
    //         $stmt = "stmt_" . md5($sql);
    //         pg_prepare($this->conn, $stmt, $sql);
    //         $result = pg_execute($this->conn, $stmt, $params);
    //     } else {
    //         $result = pg_query($this->conn, $sql);
    //     }

    //     if (!$result) {
    //         throw new Exception('Query failed: ' . pg_last_error($this->conn));
    //     }

    //     return $result;
    // }
    public function query($sql, $params = [])
    {
        if (!empty($params)) {
            $result = pg_query_params($this->conn, $sql, $params);
        } else {
            $result = pg_query($this->conn, $sql);
        }

        if (!$result) {
            throw new Exception('Query failed: ' . pg_last_error($this->conn));
        }

        return $result;
    }



    public function fetchAll($sql, $params = [])
    {
        $result = $this->query($sql, $params);
        $rows = [];
        while ($row = pg_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function fetchRow($sql, $params = [])
    {
        $result = $this->query($sql, $params);
        return pg_fetch_assoc($result) ?: false;
    }

    /** INSERT METHODS */
    public function insert($table, $data)
    {
        $columns = array_keys($data);
        $values = array_values($data);

        // PHP 7.4 safe placeholders
        $placeholders = array_map(function ($i) {
            return '$' . ($i + 1);
        }, array_keys($values));

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(",", $columns),
            implode(",", $placeholders)
        );

        $this->query($sql, $values);
        return true;
    }

    public function insert_get_id($table, $data, $idColumn = 'id')
    {
        $columns = array_keys($data);
        $values = array_values($data);

        $placeholders = array_map(function ($i) {
            return '$' . ($i + 1);
        }, array_keys($values));

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s) RETURNING %s",
            $table,
            implode(",", $columns),
            implode(",", $placeholders),
            $idColumn
        );

        $row = $this->fetchRow($sql, $values);

        // Safe return for PHP 7.4
        return is_array($row) && isset($row[$idColumn]) ? $row[$idColumn] : false;
    }

    /** Get raw connection (optional) */
    public function getConnection()
    {
        return $this->conn;
    }
}
