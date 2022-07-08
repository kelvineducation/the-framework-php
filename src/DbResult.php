<?php

namespace The;

class DbResult
{
    private $pg_result;

    public function __construct($pg_result)
    {
        $this->pg_result = $pg_result;
    }

    public function getNumRows(): int
    {
        return pg_num_rows($this->pg_result);
    }

    public function fetchRow(): array
    {
        $row = pg_fetch_assoc($this->pg_result) ?: [];

        return $row;
    }

    public function fetchOne(): ?string
    {
        $row = pg_fetch_row($this->pg_result);

        return $row[0] ?? null;
    }

    public function fetchList(): array
    {
        $list = [];
        while ($row = pg_fetch_array($this->pg_result, null, PGSQL_NUM)) {
            list($key, $value) = $row;
            $list[$key] = $value;
        }

        return $list;
    }

    public function fetchCol(): array
    {
        $col = [];
        while ($val = $this->fetchOne()) {
            $col[] = $val;
        }

        return $col;
    }

    public function fetchAll(): \Generator
    {
        while ($row = $this->fetchRow()) {
            yield $row;
        }
    }
}
