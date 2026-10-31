<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhotoLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PhotoLocationController extends Controller
{
    public function index(): View
    {
        return view('admin.locations', ['locations' => PhotoLocation::query()->withCount('photos')->orderBy('name')->get(), 'location' => new PhotoLocation]);
    }

    public function edit(PhotoLocation $location): View
    {
        return view('admin.locations', ['locations' => PhotoLocation::query()->withCount('photos')->orderBy('name')->get(), 'location' => $location]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->save($request, new PhotoLocation);
    }

    public function update(Request $request, PhotoLocation $location): RedirectResponse
    {
        return $this->save($request, $location);
    }

    private function save(Request $request, PhotoLocation $location): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_-]*$/', Rule::unique('photo_locations', 'code')->ignore($location->id)],
            'name' => ['required', 'string', 'max:255'],
        ]);
        $location->code = $data['code'];
        $location->name = $data['name'];
        $location->save();

        return redirect()->route('admin.locations')->with('status', __('photos.location_saved'));
    }

    public function destroy(PhotoLocation $location): RedirectResponse
    {
        $location->delete();

        return redirect()->route('admin.locations')->with('status', __('photos.location_deleted'));
    }
}
