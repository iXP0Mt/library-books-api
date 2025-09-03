<?php

use app\core\Database;

require __DIR__ . '/vendor/autoload.php';

function getMigrationPath(): string
{
    return str_replace('\\', '/', __DIR__ . '/migrations' . '/');
}

function getMigrationFiles(): array
{
    $path = getMigrationPath();
    $files = glob($path . '*.sql');
    return $files === false ? [] : $files;
}

function isFirstMigration(): bool
{
    try {
        $stmt = Database::pdo()->prepare("SHOW TABLES LIKE 'migrations';");
        $stmt->execute();

        return $stmt->rowCount() === 0;
    } catch (PDOException $e) {
        echo "ERROR: " . $e->getMessage();
        die;
    }
}

function getNamesMigrationsFromDB(): array
{
    try {
        $stmt = Database::pdo()->prepare("SELECT name FROM migrations;");
        $stmt->execute();

        $result = [];
        if($stmt->rowCount() > 0)
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    } catch (PDOException $e) {
        echo "ERROR: " . $e->getMessage();
        die;
    }

    return $result;
}

function getMigrationFilesToMigrate(): array
{
    $filesMigrations = getMigrationFiles();

    if(isFirstMigration()) {
        return $filesMigrations;
    }

    $namesAppliedMigrations = getNamesMigrationsFromDB();

    $appliedMigrationsPaths = [];
    foreach ($namesAppliedMigrations as $name) {
        $appliedMigrationsPaths[] = getMigrationPath() . $name . '.sql';
    }

    return array_diff($filesMigrations, $appliedMigrationsPaths);
}

function migrate($file): bool
{
    try {
        $sql = file_get_contents($file);
        $stmt = Database::pdo()->prepare($sql);
        return $stmt->execute();
    } catch (PDOException $e) {
        echo "ERROR: " . $e->getMessage();
        die;
    }
}

function addMigrationLog($name): void
{
    try {
        $stmt = Database::pdo()->prepare("INSERT INTO migrations (name) VALUES (:name);");
        $stmt->bindValue(':name', $name);
        $stmt->execute();
    } catch (PDOException $e) {
        echo "ERROR: " . $e->getMessage();
    }
}

function handler(): void
{
    $filesToMigrate = getMigrationFilesToMigrate();

    if(empty($filesToMigrate)) {
        echo 'No new migrations';
        return;
    }

    foreach($filesToMigrate as $file) {
        if(migrate($file)) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            addMigrationLog($name);
            echo 'Migration ' . $name . ' was applied!' . PHP_EOL;
        }
    }
}

handler();