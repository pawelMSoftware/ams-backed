<?php

namespace App\AMS;

use Symfony\Component\HttpFoundation\StreamedResponse;

interface AMSDownloadInterface
{
    /**
     * returns url to file from storage
     */
    public function download(string|array $filePath, string $downloadedFileName, string $storageName = ''): string|StreamedResponse;

    /**
     * returns url to zip with packed files
     */
    public function downloadMany(array $filePaths, string $downloadedFileName, string $storageName = ''): string;
}
