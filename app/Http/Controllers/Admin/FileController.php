<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveFileRequest;
use App\Models\File;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class FileController extends Controller
{
    public function index(): View
    {
        return view('admin.files', ['files' => File::query()->latest('id')->paginate(12)]);
    }

    public function create(): View
    {
        return $this->form(new File);
    }

    public function edit(File $file): View
    {
        return $this->form($file);
    }

    private function form(File $file): View
    {
        return view('admin.files.form', [
            'file' => $file,
        ]);
    }

    public function store(SaveFileRequest $request): RedirectResponse
    {
        return $this->save($request, new File);
    }

    public function update(SaveFileRequest $request, File $file): RedirectResponse
    {
        return $this->save($request, $file);
    }

    private function save(SaveFileRequest $request, File $file): RedirectResponse
    {
        $data = $request->validated();
        $oldFilename = $file->filename;
        $newFilename = null;
        if ($request->hasFile('upload')) {
            $newFilename = $request->file('upload')->store('file', 'local');
            abort_if($newFilename === false, 500, __('files.upload_failed'));
        }
        try {
            DB::transaction(function () use ($data, $file, $newFilename): void {
                if ($newFilename) {
                    $file->filename = $newFilename;
                }
                $file->title = $data['title'];
                $file->description = $data['description'] ?? null;
                $file->locale = $data['locale'];
                $file->purpose = ! empty($data['welcome_guide']) ? File::WELCOME_GUIDE : null;
                if ($file->purpose === File::WELCOME_GUIDE) {
                    File::query()
                        ->where('locale', $file->locale)
                        ->where('purpose', File::WELCOME_GUIDE)
                        ->when($file->exists, fn (Builder $query): Builder => $query->whereKeyNot($file->id))
                        ->update(['purpose' => null]);
                }
                $file->save();
            });
        } catch (Throwable $exception) {
            if ($newFilename) {
                Storage::disk('local')->delete($newFilename);
            }
            throw $exception;
        }
        if ($newFilename && $oldFilename) {
            Storage::disk('local')->delete($oldFilename);
        }

        return redirect()->route('admin.files')->with('status', __('files.saved'));
    }

    public function download(File $file): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($file->filename), 404);

        return Storage::disk('local')->download($file->filename, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(File $file): RedirectResponse
    {
        $filename = $file->filename;
        $file->delete();
        Storage::disk('local')->delete($filename);

        return redirect()->route('admin.files')->with('status', __('files.deleted'));
    }
}
