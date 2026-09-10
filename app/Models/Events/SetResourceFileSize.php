<?php

namespace App\Models\Events;

use App\Models\Resource;
use App\Models\ResourceFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * calculates resourceSize if it's not already calculated
 * and image resolution if it's image
 */
class SetResourceFileSize
{
    public function __construct(ResourceFile $resourceFile)
    {
        if ($resourceFile->disksize > 0) {
            return null;
        }

        /**
         * @todo CHECK IF THIS CACHE WORKS
         */
        $resource = Cache::get('resourceFile_resource_'.$resourceFile->res_id, function () use ($resourceFile) {
            return Resource::where('id', $resourceFile->res_id)
                ->without('files')
                ->get()->first();
        });

        if (is_null($resource)) {
            return null;
        }
        $storageName = \AMSHelper::getStorageNameForDiscAlias($resource->discs->files_alias);
        $filePath = $resource->file_path.$resourceFile->source_version.'/'.$resourceFile->filename;
        if (Storage::disk($storageName)->exists($filePath)) {
            $resourceFile->disksize = Storage::disk($storageName)->size($filePath);
            try {
                $imagesize = getimagesize(Storage::disk($storageName)->path($filePath));
            } catch (\Exception $error) {
                $imagesize = null;
            }
            if (is_array($imagesize)) {
                $resourceFile->resolution = $imagesize[0].'x'.$imagesize[1];
            }
            $resourceFile->save();
        }
    }
}
