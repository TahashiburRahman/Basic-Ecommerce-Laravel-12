<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LegacyImageController extends Controller
{
    public function show(string $path): StreamedResponse
    {
        abort_if(
            str_contains($path, '..')
                || str_contains($path, '\\')
                || preg_match('#\A[a-zA-Z0-9_./-]+\.(?:jpe?g|png|webp)\z#i', $path) !== 1,
            404
        );

        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path, headers: [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
