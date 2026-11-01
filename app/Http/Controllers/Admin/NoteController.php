<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveNoteRequest;
use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function index(): View
    {
        return view('admin.notes', ['notes' => Note::query()->with('author')->latest('id')->paginate(12)]);
    }

    public function create(): View
    {
        return view('admin.notes.form', ['note' => new Note]);
    }

    public function edit(Note $note): View
    {
        return view('admin.notes.form', ['note' => $note]);
    }

    public function store(SaveNoteRequest $request): RedirectResponse
    {
        return $this->save($request, new Note);
    }

    public function update(SaveNoteRequest $request, Note $note): RedirectResponse
    {
        return $this->save($request, $note);
    }

    private function save(SaveNoteRequest $request, Note $note): RedirectResponse
    {
        $data = $request->validated();
        if (! $note->exists) {
            $note->author()->associate($request->user());
        }
        $note->title = $data['title'];
        $note->description = $data['description'] ?? null;
        $note->save();

        return redirect()->route('admin.notes')->with('status', __('notes.saved'));
    }

    public function destroy(Note $note): RedirectResponse
    {
        $note->delete();

        return redirect()->route('admin.notes')->with('status', __('notes.deleted'));
    }
}
