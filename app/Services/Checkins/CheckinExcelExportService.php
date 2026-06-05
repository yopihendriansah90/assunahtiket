<?php

namespace App\Services\Checkins;

use App\Models\Checkin;
use App\Models\Student;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CheckinExcelExportService
{
    public function exportToTemporaryFile(iterable $checkins, string $fileName): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'checkins_export_');

        if ($temporaryPath === false) {
            throw new \RuntimeException('Tidak dapat membuat file sementara untuk export.');
        }

        $xlsxPath = $temporaryPath . '.xlsx';
        @rename($temporaryPath, $xlsxPath);

        $writer = app(Writer::class);
        $writer->openToFile($xlsxPath);

        $writer->addRow(Row::fromValues([
            'Acara',
            'Nama Siswa',
            'Kelas',
            'Jenis Kelamin',
            'Nama Ibu Kandung',
            'WhatsApp Ibu Kandung',
            'Kode Tiket',
            'Pintu Masuk',
            'Operator',
            'Metode Scan',
            'Nilai Scan',
            'Waktu Check-in',
        ]));

        foreach ($checkins as $checkin) {
            if (! $checkin instanceof Checkin) {
                continue;
            }

            $checkin->loadMissing([
                'event',
                'gate',
                'user',
                'ticket.student.eventClass',
            ]);

            $writer->addRow(Row::fromValues([
                data_get($checkin, 'event.name', '-'),
                data_get($checkin, 'ticket.student.name', '-'),
                data_get($checkin, 'ticket.student.eventClass.name', '-'),
                Student::genderLabel(data_get($checkin, 'ticket.student.gender')),
                data_get($checkin, 'ticket.student.mother_name', '-'),
                data_get($checkin, 'ticket.student.mother_whatsapp', '-'),
                data_get($checkin, 'ticket.ticket_code', '-'),
                data_get($checkin, 'gate.name', '-'),
                data_get($checkin, 'user.name', '-'),
                data_get($checkin, 'scan_method') ? strtoupper((string) $checkin->scan_method) : '-',
                data_get($checkin, 'scan_value', '-'),
                optional($checkin->checked_in_at)->format('d/m/Y H:i:s') ?? '-',
            ]));
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
