<?php

namespace Tests\Feature;

use App\Models\AMSUser;
use App\Models\Disc;
use App\Models\Resource;
use App\Models\ResourceFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Tests\AMSTestCase;

class ResourceApiTest extends AMSTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_resource_data()
    {
        $user = AMSUser::inRandomOrder()->take(1)->get()->first();
        $url = route('resource.show', ['resource' => 99999999], false);
        $response = $this->runApi($user->login, $url);
        $response->assertStatus(Response::HTTP_NOT_FOUND);

        $resource = Resource::inRandomOrder()->take(1)->get()->first();
        $url = route('resource.show', ['resource' => $resource->id], false);
        $response = $this->runApi('admin', $url);
        $response->assertStatus(Response::HTTP_OK);

        $data = $response->json();
        $this->assertArrayHasKey('resource', $data);
        $this->assertArrayHasKey('relation_definition', $data);
        $this->assertCount(3, $data['relation_definition']);
        $this->assertArrayHasKey('relations_resources', $data);
        $this->assertArrayHasKey('relations', $data);
        $this->assertArrayHasKey('categories_tree', $data);
        $this->assertNotEmpty($data['categories_tree']);
        $this->assertArrayHasKey('settings', $data);
        $this->assertArrayHasKey('AMS_ASSETS_URL', $data['settings']);
        $this->assertEquals('is derived from', $data['relation_definition'][1]);
        $resourceData = $data['resource'];
        $this->assertEquals($resource->id, $resourceData['id']);
        $this->assertEquals($resource->name, $resourceData['name']);
        $this->assertEquals(config('app.AMS_ASSETS_URL', ''), $data['settings']['AMS_ASSETS_URL']);
        // TEST RESOURCE STRUCTURE
        $this->assertArrayHasKey('categories', $resourceData);
        $this->assertArrayHasKey('files', $resourceData);
        $this->assertArrayHasKey('versions', $resourceData);
        $this->assertArrayHasKey('relations', $resourceData);
        $this->assertArrayHasKey('relations_inverse', $resourceData);
        $this->assertArrayHasKey('resource_keywords', $resourceData);
        $this->assertArrayHasKey('notes', $resourceData);
        $this->assertArrayHasKey('discs', $resourceData);
    }

    public function test_resource_file_download()
    {
        $user = AMSUser::inRandomOrder()->take(1)->get()->first();

        $url = route('resource.download', 999999999, false);
        $response = $this->runApi($user->login, $url);
        $response->assertStatus(Response::HTTP_NOT_FOUND);

        $resourceFile = $this->prepareSampleResourceFile();
        $url = route('resource.download', $resourceFile->v_id, false);

        $response = $this->runApi($user->login, $url);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Disposition', 'attachment; filename=file.txt');
        $response->assertHeader('Content-Type', 'application/octet-stream');
        $fileContent = Storage::disk('testing')->get('a/b/1/file.txt');
        $this->assertEquals($fileContent, $response->streamedContent());
    }

    /**
     * tests filling up / returning resource file disksize and imagesize if it's picture
     *
     * @return void
     */
    public function test_resource_file_extra_props()
    {
        // image
        $resourceFile = $this->prepareSampleResourceFile('image.jpg');
        $this->assertEquals(-1, $resourceFile->disksize);
        $this->assertEquals('?', $resourceFile->resolution);
        // additional attributes should be calculated and updated during getting from db
        $resourceFile = ResourceFile::where('v_id', $resourceFile->v_id)->get()->first();
        $this->assertEquals(1257358, $resourceFile->disksize);
        $this->assertEquals('1920x1080', $resourceFile->resolution);

        // not image
        $resourceFile = $this->prepareSampleResourceFile('file.txt');
        $this->assertEquals(-1, $resourceFile->disksize);
        $this->assertEquals('?', $resourceFile->resolution);
        // additional attributes should be calculated and updated during getting from db
        $resourceFile = ResourceFile::where('v_id', $resourceFile->v_id)->get()->first();
        $this->assertEquals(7, $resourceFile->disksize);
        $this->assertEquals('?', $resourceFile->resolution);
    }

    /**
     * @return mixed
     */
    private function prepareSampleResourceFile(string $filename = 'file.txt'): ResourceFile
    {
        Disc::firstOrCreate(['disc_id' => 4, 'files_path' => storage_path('testing'), 'files_alias' => '/testing/', 'active' => 'Y']);
        $resourceFile = ResourceFile::inRandomOrder()->limit(1)->get()->first();
        $resourceFile->filename = $filename;
        $resourceFile->save();
        $resource = Resource::where('id', $resourceFile->res_id)->get()->first();
        $resource->disc_id = 4;
        $resource->file_path = 'a/b/';
        $resource->save();

        return $resourceFile;
    }
}
