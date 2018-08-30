<?php

namespace K;

class Db
{
    private $conn;

    /**
     * @param $url string postgres://user:pass@localhost:5432/dbname?param=val
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function query(string $sql, array $params = []): DbResult
    {
        if (empty($params)) {
            $pg_result = pg_query($this->getConn(), $sql);
        } else {
            $pg_result = pg_query_params($this->getConn(), $sql, $params);
        }

        if (!$pg_result) {
            $e = new DbException("Failed to run query");
            $e->setDb($this);
            throw $e;
        }

        $result = new DbResult($pg_result);

        return $result;
    }

    public function exists(string $sql, array $params = []): bool
    {
        $num_rows = $this->query($sql, $params)->getNumRows();

        return $num_rows !== 0;
    }

    public function fetchRow(string $sql, array $params = []): array
    {
        $row = $this->query($sql, $params)->fetchRow();

        return $row;
    }

    public function fetchOne(string $sql, array $params = []): ?string
    {
        $val = $this->query($sql, $params)->fetchOne();

        return $val;
    }

    public function insert(string $table, array $data = []): array
    {
        $cols = array_keys($data);
        $cols_sql = implode(', ', array_map([$this, 'quoteCol'], $cols));

        $vals = [];
        $params = [];
        $n = 1;
        foreach ($data as $val) {
            $vals[] = $this->quote($val, function($val) use (&$n, &$params) {
                $params[] = $val;
                return '$' . $n++;
            });
        }

        $sql = <<<SQL
INSERT INTO %s (%s) VALUES (%s)
    RETURNING *;
SQL;
        $sql = sprintf($sql, $table, $cols_sql, implode(", ", $vals));
        $row = $this->fetchRow($sql, $params);

        return $row;
    }

    public function delete($table, string $where, array $params = [])
    {
        if (empty(trim($where))) {
            throw new DbException(sprintf(
                'No $where condition passed when trying to delete from "%s"',
                $table
            ));
        }

        $sql = <<<SQL
DELETE FROM {$table}
WHERE {$where}
SQL;
        $this->query($sql, $params);
    }

    public function update($table, array $data, string $where, array $params = []): array
    {
        if (empty(trim($where))) {
            throw new DbException(sprintf(
                'No $where condition passed when trying to update "%s"',
                $table
            ));
        }

        $sql = <<<SQL
UPDATE %s SET %s
WHERE %s
    RETURNING *
SQL;
        $cols = [];
        $n = count($params) + 1;
        foreach ($data as $field => $val) {
            $val = $this->quote($val, function($val) use (&$n, &$params) {
                $params[] = $val;
                return '$' . $n++;
            });
            $cols[] = $this->quoteCol($field) . " = {$val}";
        }
        $sql = sprintf($sql, $table, implode(", ", $cols), $where);
        $row = $this->fetchRow($sql, $params);

        return $row;
    }

    public function quote($val, callable $quote_func = null): string
    {
        if (is_null($val)) {
            return 'NULL';
        }

        if (is_bool($val)) {
            return ($val ? 'TRUE' : 'FALSE');
        }

        if (is_numeric($val)) {
            return "{$val}";
        }

        if (is_object($val) && get_class($val) === DbExpr::class) {
            return "{$val}";
        }

        if (is_array($val)) {
            $quoted_vals = [];
            foreach ($val as $v) {
                $quoted_vals[] = $this->quote($v, $quote_func);
            }

            return 'ARRAY[' . implode(", ", $quoted_vals) . ']';
        }

        if (!$quote_func) {
            $quote_func = function($val) {
                return pg_escape_literal($this->getConn(), $val);
            };
        }
        $quoted_val = $quote_func($val);

        return $quoted_val;
    }

    public function quoteCol($col): string
    {
        $quoted_col = pg_escape_identifier($this->getConn(), $col);

        return $quoted_col;
    }

    public function getConn()
    {
        if ($this->conn) {
            return $this->conn;
        }

        $this->conn = pg_connect(self::pgConnStr($this->url));

        if (!$this->conn) {
            throw new DbException("Failed to connect to postgres");
        }

        return $this->conn;
    }

    public function getLastError(): string
    {
        $last_err = pg_last_error($this->getConn());

        if (!$last_err) {
            return '';
        }

        return $last_err;
    }

    private static function pgConnStr(string $url, int $conn_timeout = 2): string
    {
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT) ?: 5432;
        $database = substr(parse_url($url, PHP_URL_PATH), 1);
        $user = parse_url($url, PHP_URL_USER);
        $password = parse_url($url, PHP_URL_PASS);

        // get additional options like application_name
        parse_str(parse_url($url, PHP_URL_QUERY), $options);
        $options_str = implode(' ', array_map(function($arg, $val) {
            return "--{$arg}={$val}";
        }, array_keys($options), $options));

        $conn_str = "host={$host} port={$port} dbname={$database}"
            . " user={$user} password={$password}"
            . " connect_timeout={$conn_timeout}"
            . " options='{$options_str}'";

        return $conn_str;
    }
}
