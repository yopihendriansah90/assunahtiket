<?php

namespace App\Services\Tickets;

use App\Models\Student;
use App\Models\Ticket;
use App\Models\TicketFile;
use App\Models\User;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class TicketQrImageService
{
    private string $disk = 'public';
    private const TICKET_SEQUENCE_PADDING = 5;

    private const QR_LABEL_HEADER_HEIGHT = 44;

    private const QR_IMAGE_TOP_TRIM = 24;

    private const QR_LABEL_BUNDLED_FONT = 'resources/fonts/Roboto-Regular.ttf';

    public function ensureTicketForStudent(Student $student, ?User $generatedBy = null): Ticket
    {
        $student->loadMissing(['event.settings']);
        $this->ensureCanGenerate($student, $generatedBy);

        return DB::transaction(function () use ($student, $generatedBy): Ticket {
            $ticket = Ticket::query()
                ->where('student_id', $student->getKey())
                ->lockForUpdate()
                ->first();

            if ($ticket === null) {
                $ticket = new Ticket();
            }

            $ticket->event_id = $student->event_id;
            $ticket->student_id = $student->getKey();
            $ticket->ticket_code ??= $this->makeTicketCode($student);
            $ticket->qr_token ??= $this->makeQrToken();
            $ticket->status ??= 'active';
            $ticket->generated_at ??= now();
            $ticket->generated_by ??= $generatedBy?->getKey();
            $ticket->save();

            return $ticket;
        });
    }

    public function ensureQrImageForTicket(Ticket $ticket, ?User $generatedBy = null): TicketFile
    {
        $ticket->loadMissing(['event', 'student.event.settings']);

        $relativePath = $ticket->qrFilePath();
        $absolutePath = Storage::disk($this->disk)->path($relativePath);
        $directory = dirname($relativePath);

        Storage::disk($this->disk)->makeDirectory($directory);

        $options = new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'outputBase64' => false,
            'returnResource' => false,
            'scale' => 14,
            'imageTransparent' => false,
        ]);

        try {
            (new QRCode($options))->render($this->qrPayload($ticket), $absolutePath);
            $this->addTicketCodeLabelToQrImage($absolutePath, $ticket->ticket_code);
        } catch (\Throwable $throwable) {
            throw new RuntimeException('QR PNG gagal dibuat: ' . $throwable->getMessage(), previous: $throwable);
        }

        $size = Storage::disk($this->disk)->exists($relativePath)
            ? Storage::disk($this->disk)->size($relativePath)
            : null;

        return TicketFile::query()->updateOrCreate(
            [
                'ticket_id' => $ticket->getKey(),
                'type' => 'qr',
            ],
            [
                'disk' => $this->disk,
                'path' => $relativePath,
                'mime_type' => 'image/png',
                'size' => $size,
                'created_by' => $generatedBy?->getKey(),
            ],
        );
    }

    public function hasStoredQrImage(Ticket $ticket): bool
    {
        return Storage::disk($this->disk)->exists($ticket->qrFilePath());
    }

    public function qrPayload(Ticket $ticket): string
    {
        return $ticket->qrPayload();
    }

    public function downloadFilename(Ticket $ticket): string
    {
        return $ticket->qrDownloadFileName();
    }

    protected function ensureCanGenerate(Student $student, ?User $generatedBy): void
    {
        if ($generatedBy?->hasRole('super_admin')) {
            return;
        }

        if (blank($student->event_id)) {
            throw ValidationException::withMessages([
                'event_id' => 'Acara untuk siswa ini belum dipilih.',
            ]);
        }

        if ($student->event?->isLocked() === true) {
            throw ValidationException::withMessages([
                'event_id' => 'QR tiket pada acara yang sudah dikunci tidak dapat dibuat atau diperbarui.',
            ]);
        }
    }

    protected function makeTicketCode(Student $student): string
    {
        $prefix = $student->event?->settings?->ticket_code_prefix
            ?: $student->event?->code
            ?: 'TKT';

        $prefix = Str::of($prefix)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]/', '')
            ->toString();

        $prefix = $prefix !== '' ? $prefix : 'TKT';
        $sequence = $this->nextTicketSequenceNumber($student);

        return sprintf('%s-%0' . self::TICKET_SEQUENCE_PADDING . 'd', $prefix, $sequence);
    }

    protected function nextTicketSequenceNumber(Student $student): int
    {
        $startingSequence = max(1, (int) ($student->event?->settings?->ticket_sequence_start ?? 1));

        $existingSequences = Ticket::query()
            ->where('event_id', $student->event_id)
            ->lockForUpdate()
            ->pluck('ticket_code')
            ->map(fn (?string $ticketCode): int => $this->extractTicketSequenceNumber($ticketCode))
            ->filter(fn (int $sequence): bool => $sequence > 0);

        if ($existingSequences->isEmpty()) {
            return $startingSequence;
        }

        return max($startingSequence, $existingSequences->max() + 1);
    }

    protected function extractTicketSequenceNumber(?string $ticketCode): int
    {
        if (preg_match('/(\d+)$/', (string) $ticketCode, $matches) !== 1) {
            return 0;
        }

        return (int) $matches[1];
    }

    protected function makeQrToken(): string
    {
        do {
            $token = (string) Str::ulid();
        } while (Ticket::query()->where('qr_token', $token)->exists());

        return $token;
    }

    protected function addTicketCodeLabelToQrImage(string $absolutePath, ?string $ticketCode): void
    {
        if (! function_exists('imagecreatefrompng') || ! function_exists('imagecreatetruecolor') || ! function_exists('imagestring')) {
            throw new RuntimeException('Ekstensi GD tidak tersedia untuk menambahkan label QR.');
        }

        $sourceImage = imagecreatefrompng($absolutePath);

        if (! $sourceImage) {
            throw new RuntimeException('Gagal membuka file QR PNG untuk menambahkan label.');
        }

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        $headerHeight = self::QR_LABEL_HEADER_HEIGHT;
        $topTrim = min(self::QR_IMAGE_TOP_TRIM, max(0, $sourceHeight - 1));
        $trimmedSourceHeight = $sourceHeight - $topTrim;

        $targetImage = imagecreatetruecolor($sourceWidth, $trimmedSourceHeight + $headerHeight);

        if (! $targetImage) {
            $this->destroyImage($sourceImage);

            throw new RuntimeException('Gagal membuat canvas QR baru.');
        }

        $white = imagecolorallocate($targetImage, 255, 255, 255);
        $black = imagecolorallocate($targetImage, 18, 24, 38);

        imagefill($targetImage, 0, 0, $white);
        imagecopy($targetImage, $sourceImage, 0, $headerHeight, 0, $topTrim, $sourceWidth, $trimmedSourceHeight);

        $label = trim((string) $ticketCode);
        $fontFile = $this->resolveLabelFontFile();

        if ($fontFile && function_exists('imagettfbbox') && function_exists('imagettftext')) {
            $fontSize = 18;
            $rendered = $this->renderTrueTypeLabel(
                targetImage: $targetImage,
                sourceWidth: $sourceWidth,
                headerHeight: $headerHeight,
                label: $label !== '' ? $label : '-',
                fontFile: $fontFile,
                fontSize: $fontSize,
                color: $black,
            );

            if ($rendered) {
                imagepng($targetImage, $absolutePath);

                $this->destroyImage($sourceImage);
                $this->destroyImage($targetImage);

                return;
            }
        }

        $labelWidth = strlen($label) * imagefontwidth(5);
        $labelX = max(12, (int) floor(($sourceWidth - $labelWidth) / 2));
        $labelY = 14;

        imagestring($targetImage, 5, $labelX, $labelY, $label !== '' ? $label : '-', $black);

        imagepng($targetImage, $absolutePath);

        $this->destroyImage($sourceImage);
        $this->destroyImage($targetImage);
    }

    protected function resolveLabelFontFile(): ?string
    {
        $bundledFontPath = base_path(self::QR_LABEL_BUNDLED_FONT);

        if (is_file($bundledFontPath)) {
            return $bundledFontPath;
        }

        return null;
    }

    protected function renderTrueTypeLabel(
        \GdImage $targetImage,
        int $sourceWidth,
        int $headerHeight,
        string $label,
        string $fontFile,
        int $fontSize,
        int $color,
    ): bool {
        $bbox = @imagettfbbox($fontSize, 0, $fontFile, $label);

        if (! is_array($bbox)) {
            return false;
        }

        $textWidth = abs($bbox[4] - $bbox[0]);
        $textHeight = abs($bbox[5] - $bbox[1]);
        $labelX = (int) max(12, floor(($sourceWidth - $textWidth) / 2));
        $baselineY = (int) (($headerHeight - $textHeight) / 2) + $textHeight - 2;

        return @imagettftext($targetImage, $fontSize, 0, $labelX, $baselineY, $color, $fontFile, $label) !== false;
    }

    protected function destroyImage(?\GdImage $image): void
    {
        if (! $image instanceof \GdImage) {
            return;
        }

        if (PHP_VERSION_ID < 80500) {
            imagedestroy($image);
        }
    }
}
