<?php

namespace App\Http\Controllers;

use App\AMS\AMSDownload;
use App\AMS\AMSDownloadPHP;
use App\Models\RelationDefinition;
use App\Models\Resource;
use App\Models\ResourceFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ResourceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  resource  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Resource $resource)
    {
        $relationsIDs = $resource->relations()->pluck('dest_id')->toArray();
        $relationsInverseIDs = $resource->relations_inverse()->pluck('source_id')->toArray();
        $relationsResources = Resource::whereIn('id', array_merge($relationsIDs, $relationsInverseIDs))
            ->without(['categories', 'files', 'versions', 'relations', 'relations_inverse', 'resourceKeywords', 'notes', 'discs'])
            ->get()->keyBy('id');
        /**
         * @todo move assembling categories into separate method
         */
        $resourceCategoriesTree = [];
        foreach ($resource->categories as $category) {
            $categoryData = \AMSRedis::get('category', $category->cat_id);
            if (! empty($categoryData->parents)) {
                $mainCategoryId = array_shift($categoryData->parents);
                $mainCategory = \AMSRedis::get('category', 'main', true)[$mainCategoryId];
                unset($mainCategory['children']);
                $parentIds = $categoryData->parents;
                $parentIds[] = $category->cat_id;
                $categoryTree = \AMSRedis::get('category', $parentIds[0], true);
                $resourceCategoriesTree[$mainCategoryId] = [$mainCategoryId => $mainCategory]
                    + \AMSHelper::getFlatCategoriesArray($categoryTree, $parentIds);
            }
        }
        $data = [
            'resource' => $resource,
            'relation_definition' => RelationDefinition::get()->keyBy('id'),
            'relations_resources' => $relationsResources,
            'categories_tree' => $resourceCategoriesTree,
            'settings' => ['AMS_ASSETS_URL' => config('app.AMS_ASSETS_URL')],
        ];

        return response($data, Response::HTTP_OK);
    }

    public function download(ResourceFile $resourceFile, Request $request)
    {
        // Log::info($resourceFile::with('resource'));
        $resource = Resource::where('id', $resourceFile->res_id)->get()->first();
        if (is_null($resource)) {
            return response('resource not exists', Response::HTTP_NOT_FOUND);
        }
        $storageName = \AMSHelper::getStorageNameForDiscAlias($resource->discs->files_alias);
        $filePath = $resource->file_path.$resourceFile->source_version.'/'.$resourceFile->filename;
        if (Storage::disk($storageName)->exists($filePath)) {
            $amsDownload = new AMSDownload(new AMSDownloadPHP);

            return $amsDownload->download($filePath, pathinfo($filePath, PATHINFO_BASENAME), $storageName);
        } else {
            return response('resource not exists', Response::HTTP_NOT_FOUND);
        }

        return response('no content', Response::HTTP_NO_CONTENT);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
