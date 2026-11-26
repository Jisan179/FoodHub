<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;

/** Creates the database, applies SQL migrations once each, and loads demo data. */
final class Migrator
{
    private array $dbConfig;

    public function __construct(array $dbConfig)
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $dbConfig['name'])) {
            throw new InvalidArgumentException('Database name may only contain letters, numbers and underscores.');
        }
        $this->dbConfig = $dbConfig;
    }

    public function drop(): void
    {
        (new Database($this->dbConfig, false))->pdo()->exec('DROP DATABASE IF EXISTS `' . $this->dbConfig['name'] . '`');
    }

    /** @return string[] names of the migrations applied in this run */
    public function migrate(): array
    {
        (new Database($this->dbConfig, false))->pdo()->exec(
            'CREATE DATABASE IF NOT EXISTS `' . $this->dbConfig['name'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
        );
        $db = new Database($this->dbConfig);
        $db->pdo()->exec('CREATE TABLE IF NOT EXISTS migrations (name VARCHAR(190) PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)');

        $applied = array_column($db->all('SELECT name FROM migrations'), 'name');
        $ran = [];
        $files = glob(BASE_PATH . '/database/migrations/*.sql');
        sort($files);
        foreach ($files as $file) {
            $name = basename($file);
            if (in_array($name, $applied, true)) {
                continue;
            }
            // Statements are separated by a semicolon at the end of a line
            $sql = preg_replace('/^\s*--.*$/m', '', file_get_contents($file));
            foreach (array_filter(array_map('trim', preg_split('/;\s*$/m', $sql))) as $statement) {
                $db->pdo()->exec($statement);
            }
            $db->insert('migrations', ['name' => $name]);
            $ran[] = $name;
        }

        return $ran;
    }

    public function seed(): bool
    {
        $db = new Database($this->dbConfig);
        if ((int) $db->value('SELECT COUNT(*) FROM users') > 0) {
            return false;
        }
        $seeder = require BASE_PATH . '/database/seed.php';
        $db->transaction($seeder);
        $this->linkDemoImages();

        return true;
    }

    /** Add made-up sales for the last seven days. Safe to run more than once. */
    public function addDemoSales(float $deliveryFee, int $commissionPercent): int
    {
        $db = new Database($this->dbConfig);
        $add = require BASE_PATH . '/database/demo_sales.php';

        return $db->transaction(static fn (Database $db) => $add($db, $deliveryFee, $commissionPercent));
    }

    /** Attach any photos found in public/assets/img/ to the demo records. */
    public function linkDemoImages(): int
    {
        $link = require BASE_PATH . '/database/demo_images.php';

        return $link(new Database($this->dbConfig));
    }
}
