<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateInfographicRequest;
use App\Models\ActivityLog;
use App\Models\Infographic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InfographicController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.infographics', [
            'infographics' => Infographic::query()->ordered()->get(),
        ]);
    }

    public function edit(Infographic $infographic): View
    {
        return view('pages.admin.infographics-edit', compact('infographic'));
    }

    public function update(
        UpdateInfographicRequest $request,
        Infographic $infographic,
    ): RedirectResponse {
        $attributes = $request->safe()->only(['caption', 'alt']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = 'images/infografis/uploads/'.Str::uuid().'.'.$image->extension();
            $dimensions = getimagesize($image->getRealPath());

            $image->move(public_path('images/infografis/uploads'), basename($path));

            if (str_starts_with($infographic->image_path, 'images/infografis/uploads/')) {
                File::delete(public_path($infographic->image_path));
            }

            $attributes['image_path'] = $path;
            $attributes['image_width'] = $dimensions[0];
            $attributes['image_height'] = $dimensions[1];
        }

        $infographic->update($attributes);

        ActivityLog::record('Update', 'Infografis', "Memperbarui \"{$infographic->caption}\"");

        return redirect()->route('admin.infographics')
            ->with('status', 'Infografis berhasil diperbarui.');
    }
}
