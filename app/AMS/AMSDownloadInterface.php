<?php

namespace App\AMS;

use Symfony\Component\HttpFoundation\StreamedResponse;

interface AMSDownloadInterface
{
    /**
     * returns url to file from storage
     * @param string|array $filePath
     * @param string $downloadedFileName
     * @param string $storageName
     * @return string|StreamedResponse
     */
    public function download(string|array $filePath, string $downloadedFileName, string $storageName = ''): string|StreamedResponse;

    /**
     * returns url to zip with packed files
     * @param array $filePaths
     * @param string $downloadedFileName
     * @param string $storageName
     * @return string
     */
    function downloadMany(array $filePaths, string $downloadedFileName, string $storageName = ''): string;
}
