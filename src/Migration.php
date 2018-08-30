<?php

namespace K;

use Closure;

class Migration
{
    /**
     * @var string
     */
    private $migration_id;

    /**
     * @return bool
     * @throws DbException
     */
    public static function createMigrationsTable(): bool
    {
        $sql = <<<SQL
CREATE TABLE migrations (
    migration_id varchar PRIMARY KEY,
    migrated_at timestamp with time zone DEFAULT now() NOT NULL,
    batch integer NOT NULL
);
SQL;
        $result = db()->query($sql);

        return $result !== false;
    }

    /**
     * @param $name string
     * @return bool|string false if fails or filename of new migration
     */
    public static function create(string $name): string
    {
        $filename = date('YmdHis') . '_' . $name . '.php';
        $tmpl = <<<'TMPL'
<?php

// COMMENT

if ($rollback === true) {
    // rollback logic
    return;
}

TMPL;
        $full_filename = __DIR__ . '/../migrations/' . $filename;

        $result = file_put_contents($full_filename, $tmpl);

        if ($result === false) {
            return '';
        }

        return $filename;
    }

    public static function runAll(Closure $run, Closure $done)
    {
        $files = array_diff(scandir(self::getMigrationsDir()), ['.', '..']);
        $db = db();
        $db->query('BEGIN');
        $batch = self::getNextBatchNumber($db);
        foreach ($files as $file) {
            $mig = new Migration($file);
            $skipped = true;
            if (!$mig->hasRun($db)) {
                call_user_func($run, $mig->getId());
                $mig->run(false, $batch);
                $skipped = false;
            }
            call_user_func($done, $mig->getId(), $skipped);
        }
        $db->query('COMMIT');
    }

    public static function rollback(Closure $run, Closure $done)
    {
        $db = db();
        $db->query('BEGIN');
        $result = $db->query(
            "SELECT migration_id FROM migrations WHERE batch = $1 ORDER BY 1 DESC",
            [self::getMostRecentBatchNumber($db)]
        );
        while ($migration_id = $result->fetchOne()) {
            $mig = new self($migration_id);
            call_user_func($run, $mig->getId());
            $mig->run(true);
            call_user_func($done, $mig->getId());
        }
        $db->query('COMMIT');
    }

    private static function getMigrationsDir(): string
    {
        return __DIR__ . '/../migrations';
    }

    private static function getNextBatchNumber(Db $db): int
    {
        $recent = self::getMostRecentBatchNumber($db);

        return $recent + 1;
    }

    private static function getMostRecentBatchNumber(Db $db): int
    {
        $recent = (int) $db->fetchOne("SELECT MAX(batch) FROM migrations");

        return $recent;
    }

    public function __construct(string $migration_id)
    {
        $this->migration_id = $migration_id;
    }

    public function getId()
    {
        return $this->migration_id;
    }

    private function hasRun(Db $db)
    {
        $sql = <<<SQL
SELECT 1
FROM migrations
WHERE migration_id = $1
SQL;
        return $db->exists($sql, [$this->getId()]);
    }

    private function run(bool $rollback = false, int $batch = null)
    {
        $migration_file = self::getMigrationsDir() . '/' . $this->getId();
        $mig = function ($rollback) use ($migration_file) {
            if ($rollback === true) {
                $res = require $migration_file;
                if ($res !== true) {
                    throw new MigrationException("Rollback must return true;");
                }
            } else {
                require $migration_file;
            }
        };
        call_user_func($mig, $rollback);

        if ($rollback === false) {
            $this->markAsRun($batch);
        } else {
            $this->markAsUnrun();
        }
    }

    private function markAsRun(int $batch)
    {
        db()->insert('migrations', [
            'migration_id' => $this->getId(),
            'batch'        => $batch,
        ]);
    }

    private function markAsUnrun()
    {
        db()->delete('migrations', "migration_id = $1", [$this->getId()]);
    }
}
