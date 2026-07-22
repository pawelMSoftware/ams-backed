<?php

namespace App\AMS;

use Symfony\Component\HttpFoundation\StreamedResponse;

class AMSDownload implements AMSDownloadInterface
{
    public function __construct(public readonly AMSDownloadInterface $downloadStrategy) {}

    public function download(string|array $filePath, string $downloadedFileName, string $storageName = ''): string|StreamedResponse
    {
        return $this->downloadStrategy->download($filePath, $downloadedFileName, $storageName);
    }

    public function downloadMany(array $filePaths, string $downloadedFileName, string $storageName = ''): string
    {
        return $this->downloadStrategy->downloadMany($filePaths, $downloadedFileName, $storageName);
    }
}
