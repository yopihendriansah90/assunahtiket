<?php

namespace App\Services\Reports;

use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class DatabaseBackupService
{
    /**
     * @return array{path:string, download_name:string}
     */
    public function createTemporaryBackup(string $label): array
    {
        $connectionName = config('database.default');
        $connection = config("database.connections.{$connectionName}");
        $driver = $connection['driver'] ?? $connectionName;
        $timestamp = now()->format('Ymd-His');
        $safeLabel = Str::of($label)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9]+/', '-')
            ->trim('-')
            ->lower()
            ->toString();

        $safeLabel = $safeLabel !== '' ? $safeLabel : 'database';

        return match ($driver) {
            'sqlite' => $this->backupSqlite($connection['database'] ?? null, $safeLabel, $timestamp),
            'mysql', 'mariadb' => $this->backupMysqlLike($connection, $safeLabel, $timestamp, $driver),
            'pgsql' => $this->backupPostgres($connection, $safeLabel, $timestamp),
            default => throw new RuntimeException('Driver database "' . $driver . '" belum didukung untuk backup otomatis.'),
        };
    }

    /**
     * @param array<string, mixed> $connection
     * @return array{path:string, download_name:string}
     */
    private function backupMysqlLike(array $connection, string $label, string $timestamp, string $driver): array
    {
        $binary = 'mysqldump';

        if (! $this->binaryExists($binary)) {
            throw new RuntimeException('Binary mysqldump tidak ditemukan di server.');
        }

        $temporaryPath = $this->createTemporaryPath('attendance-backup-', '.sql');

        $process = new Process([
            $binary,
            '--single-transaction',
            '--skip-lock-tables',
            '--routines',
            '--triggers',
            '--hex-blob',
            '--default-character-set=utf8mb4',
            '--host=' . ($connection['host'] ?? '127.0.0.1'),
            '--port=' . ($connection['port'] ?? '3306'),
            '--user=' . ($connection['username'] ?? 'root'),
            $connection['database'] ?? '',
        ]);

        $process->setTimeout(300);
        $process->setEnv([
            'MYSQL_PWD' => (string) ($connection['password'] ?? ''),
        ]);
        $process->run();

        if (! $process->isSuccessful()) {
            @unlink($temporaryPath);

            throw new RuntimeException('Gagal membuat backup database: ' . $process->getErrorOutput());
        }

        file_put_contents($temporaryPath, $process->getOutput());

        return [
            'path' => $temporaryPath,
            'download_name' => sprintf('%s-%s-%s.sql', $label, $driver, $timestamp),
        ];
    }

    /**
     * @param array<string, mixed> $connection
     * @return array{path:string, download_name:string}
     */
    private function backupPostgres(array $connection, string $label, string $timestamp): array
    {
        $binary = 'pg_dump';

        if (! $this->binaryExists($binary)) {
            throw new RuntimeException('Binary pg_dump tidak ditemukan di server.');
        }

        $temporaryPath = $this->createTemporaryPath('attendance-backup-', '.sql');

        $process = new Process([
            $binary,
            '--no-owner',
            '--no-acl',
            '--format=plain',
            '--host=' . ($connection['host'] ?? '127.0.0.1'),
            '--port=' . ($connection['port'] ?? '5432'),
            '--username=' . ($connection['username'] ?? 'root'),
            '--file=' . $temporaryPath,
            $connection['database'] ?? '',
        ]);

        $process->setTimeout(300);
        $process->setEnv([
            'PGPASSWORD' => (string) ($connection['password'] ?? ''),
        ]);
        $process->run();

        if (! $process->isSuccessful()) {
            @unlink($temporaryPath);

            throw new RuntimeException('Gagal membuat backup database: ' . $process->getErrorOutput());
        }

        return [
            'path' => $temporaryPath,
            'download_name' => sprintf('%s-pgsql-%s.sql', $label, $timestamp),
        ];
    }

    /**
     * @param string|mixed $databasePath
     * @return array{path:string, download_name:string}
     */
    private function backupSqlite(mixed $databasePath, string $label, string $timestamp): array
    {
        if (! is_string($databasePath) || $databasePath === '' || ! is_file($databasePath)) {
            throw new RuntimeException('File database SQLite tidak ditemukan.');
        }

        $temporaryPath = $this->createTemporaryPath('attendance-backup-', '.sqlite');

        if (! copy($databasePath, $temporaryPath)) {
            @unlink($temporaryPath);

            throw new RuntimeException('Gagal menyalin file backup SQLite.');
        }

        return [
            'path' => $temporaryPath,
            'download_name' => sprintf('%s-sqlite-%s.sqlite', $label, $timestamp),
        ];
    }

    private function createTemporaryPath(string $prefix, string $suffix): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), $prefix);

        if ($temporaryPath === false) {
            throw new RuntimeException('Tidak dapat membuat file sementara untuk backup.');
        }

        $path = $temporaryPath . $suffix;
        @rename($temporaryPath, $path);

        return $path;
    }

    private function binaryExists(string $binary): bool
    {
        $process = Process::fromShellCommandline(sprintf('command -v %s', escapeshellarg($binary)));
        $process->run();

        return $process->isSuccessful();
    }
}
