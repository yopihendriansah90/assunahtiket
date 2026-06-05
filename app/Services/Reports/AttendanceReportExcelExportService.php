<?php

namespace App\Services\Reports;

use App\Models\Student;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AttendanceReportExcelExportService
{
    public function exportToTemporaryFile(iterable $students, string $fileName): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'attendance_report_');

        if ($temporaryPath === false) {
            throw new \RuntimeException('Tidak dapat membuat file sementara untuk export laporan kehadiran.');
        }

        $xlsxPath = $temporaryPath . '.xlsx';
        @rename($temporaryPath, $xlsxPath);

        $writer = app(Writer::class);
        $writer->openToFile($xlsxPath);

        $writer->addRow(Row::fromValues([
            'No.',
            'Acara',
            'Kelas',
            'Nama Peserta',
            'Jenis Kelamin',
            'Nama Ibu Kandung',
            'WhatsApp Ibu Kandung',
            'Kode Tiket',
            'Status Kehadiran',
            'Waktu Check-in',
            'Pintu Masuk',
            'Metode Scan',
        ]));

        $rowNumber = 1;

        foreach ($students as $student) {
            if (! $student instanceof Student) {
                continue;
            }

            $student->loadMissing([
                'event',
                'eventClass',
                'ticket.latestCheckin.gate',
            ]);

            $latestCheckin = $student->ticket?->latestCheckin;

            $writer->addRow(Row::fromValues([
                $rowNumber,
                data_get($student, 'event.name', '-'),
                data_get($student, 'eventClass.name', '-'),
                data_get($student, 'name', '-'),
                Student::genderLabel(data_get($student, 'gender')),
                data_get($student, 'mother_name', '-'),
                data_get($student, 'mother_whatsapp', '-'),
                data_get($student, 'ticket.ticket_code', '-'),
                $latestCheckin ? 'Hadir' : 'Belum Hadir',
                $latestCheckin?->checked_in_at?->format('d/m/Y H:i:s') ?? '-',
                data_get($latestCheckin, 'gate.name', '-'),
                $latestCheckin?->scan_method ? strtoupper((string) $latestCheckin->scan_method) : '-',
            ]));

            $rowNumber++;
        }

        $writer->close();

        return $xlsxPath;
    }

    public function downloadFile(string $path, string $downloadName): BinaryFileResponse
    {
        return response()
            ->download($path, $downloadName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }
}
