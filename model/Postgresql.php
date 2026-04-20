<?php
set_time_limit(0);
ini_set('memory_limit', '-1');
error_reporting(E_ALL);

class Postgresql
{
    private $conn;
    private $query;
    private $limit;
    private $sql;
    private $result = false;

    function __construct()
    {
        $this->loadEnv(__DIR__ . '/.env');

        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $dbname = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASS'];

        $this->conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password")
            or die("Connection failed.");
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
            $name = trim($name);
            $value = trim($value);
            $_ENV[$name] = $value;
        }
    }

    function query($sql)
    {
        try {
            $this->query = pg_query($this->conn, $sql);
            if (!$this->query) {
                throw new Exception('Error executing query: ' . pg_last_error($this->conn));
            }
            return $this->query;
        } catch (Exception $e) {
            return $e;
        }
    }

    function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function getConnection()
    {
        return $this->conn;
    }

    function fetchAll($sql, $params = [])
    {
        $this->result = [];
        $stmt = "query_fetch_all_" . md5($sql);
        pg_prepare($this->conn, $stmt, $sql);
        $result = pg_execute($this->conn, $stmt, $params);

        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $this->result[] = $row;
            }
        }

        return !empty($this->result) ? $this->result : false;
    }

    function fetchRow($sql, $params = [])
    {
        $this->result = false;
        $stmt = "query_fetch_row_" . md5($sql);
        pg_prepare($this->conn, $stmt, $sql);
        $result = pg_execute($this->conn, $stmt, $params);

        if ($result) {
            $this->result = pg_fetch_assoc($result);
        }

        return $this->result;
    }

    function insert($data, $table)
    {
        $arr_column = array_keys($data);
        $columns = implode(',', $arr_column);

        $arr_values = array_values($data);
        $values = implode(", ", $arr_values);

        $sql = "INSERT into $table ($columns) values( $values) ";
        try {
            $this->query = $query = pg_query($this->conn, $sql) or die('Error message: ' . pg_last_error());
            return $this;
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

    function insert_get_id($data, $table)
    {
        $arr_column = array_keys($data);
        $columns = implode(',', $arr_column);

        $arr_values = array_values($data);
        $values = implode(", ", $arr_values);

        $sql = "INSERT into $table ($columns) values( $values) returning id";
        try {
            $this->result = false;
            $this->query = $query = pg_query($this->conn, $sql) or die('Error message: ' . pg_last_error());
            while ($result = pg_fetch_array($this->query)) {
                $this->result = $result;
            }
            if (!empty($this->result)) {
                return $this->result;
            }
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }
}
