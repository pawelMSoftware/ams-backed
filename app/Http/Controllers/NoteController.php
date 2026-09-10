<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoteController extends Controller
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
     */
    public function store(Request $request): \Illuminate\Http\Response
    {
        // @todo FINISH THAT -> tests before -> do some refactor in testUpdateNote
        // @todo consider checking if user is logged-in by moving to separate middleware
        if ($request->user()) {
            $this->validate($request, [
                'resourceId' => ['required', 'exists:App\Models\Resource,id'],
                'text' => ['required', 'min:'.NOTE::NOTE_MIN_LENGTH, 'max:'.NOTE::NOTE_MAX_LENGTH], // max length in db
            ]);
            $note = Note::create(['res_id' => $request['resourceId'], 'author' => $request->user()->login, 'text' => $request['text']]);

            return response($note, Response::HTTP_CREATED);
        }

        return response('Unauthorized', Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     */
    public function update(Request $request, Note $note): \Illuminate\Http\Response
    {
        if ($request->user()) {
            $this->validate($request, [
                'text' => ['required', 'min:'.NOTE::NOTE_MIN_LENGTH, 'max:'.NOTE::NOTE_MAX_LENGTH], // max length in db
            ]);
            $user = $request->user();
            if ($note->author !== $user->login) {
                return response('Unauthorized', Response::HTTP_UNAUTHORIZED);
            }
            $note->text = $request['text'];
            if ($note->save()) {
                return response($note, Response::HTTP_OK);
            }
        } else {
            return response('', Response::HTTP_UNAUTHORIZED);
        }

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        //
    }
}
