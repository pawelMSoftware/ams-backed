<?php

namespace Tests\Feature;

use App\Models\AMSUser;
use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\AMSTestCase;
use App\Models\Note;
use Illuminate\Support\Facades\Log;

class NoteApiTest extends AMSTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testUpdateNote()
    {
        $user = AMSUser::inRandomOrder()->take(1)->get()->first();
        $userNotAllowed = AMSUser::where('user_id', '<>', $user->user_id)->inRandomOrder()->take(1)->get()->first();
        $resource = Resource::inRandomOrder()->take(1)->get()->first();
        $text = md5(now());
        $textUpdated = $text . '__@';

        $note = Note::create(['res_id' => $resource->id, 'author' => $user->login, 'text' => $text]);
        $url = route('note.update', ['note' => $note->note_id], false);

        $response = $this->runApi('', $url, 'put', ['text' => $textUpdated]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

        $response = $this->runApi($user->login, $url, 'put', ['text' => $textUpdated]);
        $response->assertStatus(Response::HTTP_OK);

        $data = $response->json();
        $this->assertArrayHasKey('text', $data);
        $this->assertArrayHasKey('author', $data);
        $this->assertEquals($textUpdated, $data['text']);
        $this->assertEquals($user->login, $data['author']);

        $url = route('note.update', ['note' => $note->note_id], false);
        $response = $this->runApi($user->login, $url, 'put', ['text' => str_repeat('@', Note::NOTE_MIN_LENGTH - 1)]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $url = route('note.update', ['note' => $note->note_id], false);
        $response = $this->runApi($user->login, $url, 'put', ['text' => str_repeat('@', Note::NOTE_MAX_LENGTH + 1)]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response = $this->runApi($userNotAllowed->login, $url, 'put', ['text' => $textUpdated]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

        $url = route('note.update', ['note' => 99999999999], false);
        $response = $this->runApi($user->login, $url, 'put', ['text' => $textUpdated]);
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testCreateNote()
    {
        $user = AMSUser::inRandomOrder()->take(1)->get()->first();
        $resource = Resource::inRandomOrder()->take(1)->get()->first();
        $text = md5(now());

        $url = route('note.store', ['text' => $text], false);

        $response = $this->runApi('', $url, 'post', ['resourceId' => $resource->id, 'text' => $text]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

        $response = $this->runApi($user->login, $url, 'post', ['resourceId' => 99999999999, 'text' => $text]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response = $this->runApi($user->login, $url, 'post', ['resourceId' => $resource->id, 'text' => '']);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response = $this->runApi($user->login, $url, 'post', ['resourceId' => $resource->id, 'text' => str_repeat('@', Note::NOTE_MAX_LENGTH + 1)]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response = $this->runApi($user->login, $url, 'post', ['resourceId' => $resource->id, 'text' => $text]);
        $response->assertStatus(Response::HTTP_CREATED);
        $data = $response->json();
        $this->assertArrayHasKey('text', $data);
        $this->assertArrayHasKey('author', $data);
        $this->assertEquals($text, $data['text']);
        $this->assertEquals($user->login, $data['author']);
        $this->assertDatabaseHas('notes', ['res_id' => $resource->id, 'author' => $user->login]);
    }
}
