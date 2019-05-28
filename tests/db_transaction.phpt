<?php

use function \The\db;

$sql = <<<SQL
CREATE TEMPORARY TABLE t (
    num INTEGER NOT NULL
)
SQL;
db()->query($sql);

test("transaction is committed if name is accepted", function (\The\Tests\Test $t) {
    db()->query('TRUNCATE t');

    db()->begin('outer');
    db()->begin('first');
    db()->insert('t', ['num' => 2]);
    db()->insert('t', ['num' => 6]);
    db()->accept('first');
    db()->begin('second');
    db()->insert('t', ['num' => 7]);
    db()->accept('second');
    db()->insert('t', ['num' => 3]);
    db()->accept('outer');

    // causing an error ensures we're not
    // selecting records from an uncommitted transaction
    $t->throws(
        function () {
            @db()->query('SELECT oops');
        },
        '/Failed to run query/',
        'Make sure any active transaction is unusable'
    );

    $t->equals(
        db()->fetchList('SELECT num, num FROM t ORDER BY num'),
        [2 => 2, 3 => 3, 6 => 6, 7 => 7],
        'The records inserted during the transaction exist outside the transaction'
    );
});

test("transaction is not committed if name is not accepted", function (\The\Tests\Test $t) {
    db()->query('TRUNCATE t');

    db()->begin('outer');
    db()->begin('first');
    db()->insert('t', ['num' => 9]);
    db()->insert('t', ['num' => 1]);
    db()->accept('first');
    db()->rollbackAll();

    $t->equals(
        db()->fetchList('SELECT num, num FROM t ORDER BY num'),
        [],
        'The records inserted during the transaction do not exist after rollback'
    );
});

test("names must be accepted in opposite order they were started", function (\The\Tests\Test $t) {
    db()->begin('outer');
    db()->begin('inner');

    $t->throws(
        function () {
            db()->accept('outer');
        },
        '/TransactionAcceptanceOrderException/',
        'Accepting a transaction name in the wrong order causes an exception'
    );

    db()->rollbackAll();
});

test("names must be unique", function (\The\Tests\Test $t) {
    db()->begin('outer');

    $t->throws(
        function () {
            db()->begin('outer');
        },
        '/DuplicateTransactionNameException/',
        'Beginning a duplicate active transaction name causes an exception'
    );

    db()->rollbackAll();
});

test("accepting an unknown transaction name is not allowed", function (\The\Tests\Test $t) {
    $t->throws(
        function () {
            db()->accept('outer');
        },
        '/UnknownTransactionNameException/',
        'Accepting an unknown transaction name causes an exception'
    );
});

test("transaction can be rolled back even if a transaction is not started", function (\The\Tests\Test $t) {
    db()->rollbackAll();
    db()->rollbackAll();
    db()->rollbackAll();

    $t->pass('Rolled back transaction');
});
