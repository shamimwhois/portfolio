<?php

namespace App\Http\Controllers;

use App\Models\ClientProject;
use App\Models\ProjectMedia;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProjectMediaController extends Controller
{
    public function __invoke(ClientProject $project, ProjectMedia $media)
    {
        abort_unless($project->user_id === auth()->id() || auth()->user()?->isAdmin(), 403);
        abort_unless($media->client_project_id === $project->id, 404);

        $path = Storage::disk('local')->path($media->path);

        abort_unless(File::exists($path), 404);

        return response()->file($path);
    }
}