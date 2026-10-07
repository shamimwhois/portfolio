<?php

namespace App\Http\Controllers;

use App\Models\ClientProject;
use App\Models\ProjectFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProjectFileController extends Controller
{
    public function __invoke(ClientProject $project, ProjectFile $file)
    {
        abort_unless($project->user_id === auth()->id(), 403);
        abort_unless($file->client_project_id === $project->id, 404);

        $path = Storage::disk('local')->path($file->path);

        abort_unless(File::exists($path), 404);

        return response()->download($path, $file->name);
    }
}