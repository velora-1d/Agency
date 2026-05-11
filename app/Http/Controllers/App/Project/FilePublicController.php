<?php

namespace App\Http\Controllers\App\Project;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class FilePublicController extends Controller
{
    public function showShared(string $token): Response
    {
        $file = File::query()
            ->where('share_token', $token)
            ->firstOrFail();

        abort_if(blank($file->share_expires_at) || $file->share_expires_at->isPast(), 404);
        abort_if(blank($file->path) || ! Storage::disk('public')->exists($file->path), 404);

        $filename = $file->original_name ?: $file->name;

        return Storage::disk('public')->response($file->path, $filename, [
            'Content-Type' => $file->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => sprintf('inline; filename="%s"', addslashes($filename)),
        ]);
    }
}
