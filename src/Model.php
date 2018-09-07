<?php

namespace K;

use Closure;

/**
 * @package K
 */
class Model
{
    /**
     * Default a columns value to whatever the default is in postgres.
     * Essentially this will exclude the column when doing a db->insert()
     */
    const DEFAULT = 'PG-DEFAULT';

    /**
     * @var Db|callable
     */
    private static $db;

    /**
     * @var bool
     */
    private static $db_loaded = false;

    /**
     * @var string
     */
    protected static $table_name  = '';

    /**
     * @var string
     */
    protected static $primary_key = '';

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    private $changed = [];

    /**
     * @param Closure $db
     */
    public static function setDb(Closure $db)
    {
        self::$db = $db;
    }

    /**
     * @return Db
     */
    protected static function db(): Db
    {
        if (self::$db_loaded === true) {
            return self::$db;
        }

        self::$db = call_user_func(self::$db);
        self::$db_loaded = true;

        return self::$db;
    }

    /**
     * @param array $data
     * @return Model
     */
    public static function create(array $data)
    {
        $model = new static($data);
        $model->save();

        return $model;
    }

    /**
     * @param $id
     * @return static|null
     * @throws DbException
     */
    public static function find($id): ?Model
    {
        $sql = <<<SQL
SELECT *
FROM %s
WHERE %s = $1
LIMIT 1
SQL;
        $sql = sprintf($sql, static::$table_name, static::$primary_key);
        $row = self::db()->fetchRow($sql, [$id]);
        if (!$row) {
            return null;
        }
        $model = new static($row, false);

        return $model;
    }

    /**
     * @param string $where
     * @param array $params
     * @return static|null
     * @throws ModelException
     * @throws DbException
     */
    public static function findWhere(string $where, array $params = []): ?Model
    {
        if (empty(trim($where))) {
            throw new ModelException(sprintf(
                "No \$where condition passed when trying to findWhere '%s'",
                static::$table_name
            ));
        }

        $sql = <<<SQL
SELECT *
FROM %s
WHERE %s
LIMIT 1
SQL;
        $sql = sprintf($sql, static::$table_name, $where);
        $row = self::db()->fetchRow($sql, $params);
        if (!$row) {
            return null;
        }
        $model = new static($row, false);

        return $model;
    }

    /**
     * @param string $where
     * @param array $params
     * @return array
     * @throws DbException
     * @throws ModelException
     */
    public static function fetchAllWhere(string $where, array $params = []): array
    {
        if (empty(trim($where))) {
            throw new ModelException(sprintf(
                "No \$where condition passed when trying to fetchAllWhere '%s'",
                static::$table_name
            ));
        }

        $sql = <<<SQL
SELECT *
FROM %s
WHERE %s
SQL;
        $sql = sprintf($sql, static::$table_name, $where);
        $res = self::db()->query($sql, $params);
        $models = [];
        while ($row = $res->fetchRow()) {
            $models[] = new static($row, false);
        }

        return $models;
    }

    /**
     * Model constructor.
     *
     * @param array $data
     * @param bool $dirty
     */
    public function __construct(array $data = [], $dirty = true)
    {
        $this->setData($data);
        if ($dirty === false) {
            $this->changed = [];
        }
    }

    /**
     * @return array|mixed
     */
    public function getId()
    {
        return $this->getData(static::$primary_key);
    }

    /**
     * @param string $field
     * @return array|mixed
     */
    public function getData($field = '')
    {
        if ($field !== '') {
            return $this->data[$field];
        }

        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data = [])
    {
        foreach ($data as $field => $value) {
            if (array_key_exists($field, $this->data)) {
                $this->data[$field] = $value;
                $this->changed[$field] = true;
            }
        }
    }

    /**
     * @throws DbException
     */
    public function save()
    {
        if ($this->getId() !== Model::DEFAULT) {
            $this->update();
            return;
        }

        $this->insert();
    }

    /**
     * @throws DbException
     */
    public function delete()
    {
        $this->db()->delete(
            static::$table_name,
            $this->db()->quoteCol(static::$primary_key) . ' = $1',
            [$this->getId()]
        );
        $this->setData([static::$primary_key => self::DEFAULT]);
    }

    /**
     * @throws DbException
     */
    private function update()
    {
        if (empty($this->changed)) {
            return;
        }

        if (is_callable([$this, 'beforeUpdate'])) {
            $this->beforeUpdate();
        }

        // pre-update hook to set the updated_at column
        // this is magical, but I think it might be better than
        // adding it to every Model
        $this->setData([
            'updated_at' => new DbExpr('CURRENT_TIMESTAMP'),
        ]);
        $changed_data = array_intersect_key($this->data, $this->changed);
        $row = $this->db()->update(
            static::$table_name,
            $changed_data,
            $this->db()->quoteCol(static::$primary_key) . ' = $1',
            [$this->getId()]
        );
        $this->setData($row);
        $this->changed = [];

        if (is_callable([$this, 'afterUpdate'])) {
            $this->afterUpdate();
        }
    }

    /**
     *
     */
    private function insert()
    {
        if (is_callable([$this, 'beforeCreate'])) {
            $this->beforeCreate();
        }

        $data = array_filter($this->getData(), function ($val) {
            return $val !== Model::DEFAULT;
        });
        $row = $this->db()->insert(static::$table_name, $data);
        $this->setData($row);
        $this->changed = [];

        if (is_callable([$this, 'afterCreate'])) {
            $this->afterCreate();
        }
    }
}
