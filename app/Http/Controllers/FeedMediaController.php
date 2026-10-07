<?php

namespace App\Http\Controllers;

use App\Models\FeedMedia;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FeedMediaController extends Controller
{
    public function __invoke(FeedMedia $media)
    {
        // Draft / unpublished posts must not leak their media via direct URL.
        abort_unless($media->post?->isPublished() ?? false, 404);

        // Media is always stored under feed/… — anything else in the DB would
        // mean tampering, so refuse to serve it (path traversal defense).
        abort_unless(str_starts_with($media->path, 'feed/'), 404);

        $path = Storage::disk('local')->path($media->path);

        abort_unless(File::exists($path), 404);

        // Defense-in-depth: never let a stored/tampered name shape the header.
        $filename = str_replace(['/', '\\'], '', $media->filename ?: basename($media->path));
        $fallback = preg_replace('/[^\x20-\x7E]/', '', Str::ascii($filename));
        $fallback = str_replace(['"', "\r", "\n", '%'], '', $fallback);
        $fallback = trim($fallback) !== '' ? trim($fallback) : 'file';

        $response = response()->file($path, [
            // Never let the browser sniff an attacker-controlled type.
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'no-referrer',
            // Media is immutable — the path is unique per post and the file is
            // deleted with its post, so a long-lived cache is safe.
            'Cache-Control' => 'public, max-age=31536000, immutable',
            // Documents open as top-level pages: sandbox them so a malicious
            // file (even one that slipped past validation) cannot run scripts.
            'Content-Security-Policy' => $media->type === FeedMedia::TYPE_DOCUMENT
                ? "sandbox; default-src 'none'"
                : "default-src 'none'",
        ]);

        // RFC 6266: prints the ASCII fallback in filename="…" and the original
        // UTF-8 name in filename*=utf-8''… (Symfony escapes both safely).
        $response->setContentDisposition('inline', $filename, $fallback);

        return $response;
    }
}
