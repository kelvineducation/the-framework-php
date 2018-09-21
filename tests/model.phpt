--TEST--
creating a new model
--FILE--
<?php

use function K\db;
use K\Model;

require_once __DIR__ . '/../bootstrap.php';

Model::setDb(function() {
    return db();
});

$sql = <<<SQL
CREATE TEMPORARY TABLE t (
    id serial,
    name varchar,
    updated_at timestamp with time zone DEFAULT now() NOT NULL
)
SQL;
db()->query($sql);

class Test extends Model
{
    static protected $table_name = 't';
    static protected $primary_key = 'id';
    protected $data = [
        'id'         => Model::DEFAULT,
        'name'       => null,
        'updated_at' => Model::DEFAULT,
    ];
}

// test creating a new model
$t = new Test(['name' => 'What a test']);
$t->save();
var_dump($t->getId());
$updated_at = $t->getData('updated_at');
var_dump(!empty($updated_at));

// test model updates data
$t->setData([
    'name'       => 'Updated...',
]);
$t->save();
$new_updated_at = db()->fetchOne('SELECT updated_at FROM t WHERE id = $1', [$t->getId()]);
var_dump($new_updated_at != $updated_at);
var_dump(db()->fetchOne('SELECT name FROM t WHERE id = $1', [$t->getId()]));

// test model gets deleted
$t->delete();
var_dump($t->getId() === Model::DEFAULT);
var_dump(db()->fetchOne("SELECT COUNT(*) FROM t WHERE id = 1"));

?>
--EXPECT--
string(1) "1"
bool(true)
bool(true)
string(10) "Updated..."
bool(true)
string(1) "0"
