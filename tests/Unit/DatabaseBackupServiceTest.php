<?php

namespace Tests\Unit;

use App\Services\Reports\DatabaseBackupService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DatabaseBackupServiceTest extends TestCase
{
    public function test_it_creates_a_copy_of_the_sqlite_database_file(): void
    {
        $databasePath = database_path('testing-backup.sqlite');
        File::ensureDirectoryExists(dirname($databasePath));
        File::put($databasePath, 'sqlite-backup-source');

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $databasePath);

        $backup = app(DatabaseBackupService::class)->createTemporaryBackup('reset-kehadiran');

        $this->assertFileExists($backup['path']);
        $this->assertSame('sqlite-backup-source', file_get_contents($backup['path']));
        $this->assertStringContainsString('reset-kehadiran-sqlite-', $backup['download_name']);
        $this->assertStringEndsWith('.sqlite', $backup['download_name']);

        @unlink($backup['path']);
        @unlink($databasePath);
    }
}
