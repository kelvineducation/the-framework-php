<?php

use function \K\{db};
use \K\{Model};

$sql = <<<SQL
CREATE TEMPORARY TABLE t (
    id serial,
    name varchar,
    updated_at timestamp with time zone DEFAULT now() NOT NULL
)
SQL;
db()->query($sql);

class TestModel extends Model
{
    static protected $table_name = 't';
    static protected $primary_key = 'id';
    protected $data = [
        'id'         => Model::DEFAULT,
        'name'       => null,
        'updated_at' => Model::DEFAULT,
    ];
}

test("creating a new model", function ($t) {
    $new = TestModel::create(['name' => uniqid()]);
    $t->notEquals($new->getId(), Model::DEFAULT, "new id was set");
    $t->ok(!!$new->getId(), "new id is valid");
    $t->ok($new->getData('updated_at'), "updated at was set");
});

test("updating a model", function ($t) {
    $new = TestModel::create(['name' => 'Zach']);
    $updated_at = $new->getData('updated_at');
    $new->setData(['name' => 'Zachary']);
    $new->save();

    $sql = <<<SQL
SELECT name, updated_at
FROM t
WHERE id = $1
SQL;
    $db_row = db()->fetchRow($sql, [$new->getId()]);

    $t->notEquals($updated_at, $db_row['updated_at'], "model timestamp updated in db");
    $t->equals($db_row['name'], 'Zachary', "model field was updated in db");
});

test("deleting a model", function ($t) {
    $new = TestModel::create(['name' => uniqid()]);
    $id = $new->getId();
    $new->delete();

    $t->equals($new->getId(), Model::DEFAULT, "model's id gets reset");
    $exists = db()->exists("SELECT 1 FROM t WHERE id = $1", [$id]);
    $t->notOk($exists, "model no longer exists in db");
});
