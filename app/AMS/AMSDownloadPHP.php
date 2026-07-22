<?php

namespace App\AMS;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @todo do it dummy - improve in the future:
 * - queue -> create file to download, broadcast, notification, download
 */
class AMSDownloadPHP implements AMSDownloadInterface
{
    public function download(string|array $filePath, string $downloadedFileName, string $storageName = ''): string|StreamedResponse
    {
        /**
         * OR MAYBE temporaryUrl() with valid time
         */
        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Length' => Storage::disk($storageName)->size($filePath),
        ];

        // Log::info(Storage::disk($storageName)->getDriver()->getMetadata());
        return response()->streamDownload(function () use ($filePath, $storageName) {
            $stream = Storage::disk($storageName)->readStream($filePath);
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, $downloadedFileName, $headers);
    }

    public function downloadMany(array $filePaths, string $downloadedFileName, string $storageName = ''): string
    {
        // TODO: Implement downloadMany() method.
        return '';
    }
}
