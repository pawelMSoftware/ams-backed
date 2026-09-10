<?php

namespace App\AMS;

class AMSDownloadXSendfile implements AMSDownloadInterface
{
    public function download(string|array $filePath, string $downloadedFileName, string $storageName = ''): string
    {
        // TODO: Implement download() method.
    }

    public function downloadMany(array $filePath, string $downloadedFileName, string $storageName = ''): string
    {
        // TODO: Implement downloadMany() method.
    }
}
