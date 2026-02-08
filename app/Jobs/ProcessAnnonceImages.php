<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProcessAnnonceImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array<int, string>
     */
    public array $paths;

    /**
     * Create a new job instance.
     */
    public function __construct(array $paths)
    {
        $this->paths = $paths;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $disk = config('filesystems.default', 'public');
        $watermark = null;
        $watermarkPath = public_path('watermark.png');
        if (file_exists($watermarkPath)) {
            $watermark = Image::make($watermarkPath)->opacity(45);
        }

        foreach ($this->paths as $path) {
            if (!$path || !Storage::disk($disk)->exists($path)) {
                continue;
            }

            $stream = Storage::disk($disk)->get($path);
            $image = Image::make($stream)->orientate();

            // Redimensionner à 1280px (garde aspect ratio)
            $image->resize(1280, null, function ($c) {
                $c->aspectRatio();
                $c->upsize();
            });

            // Ajouter le watermark si disponible
            if ($watermark) {
                $wm = clone $watermark;
                $wm->resize((int) ($image->width() * 0.18), null, function ($c) {
                    $c->aspectRatio();
                });
                $image->insert($wm, 'center');
            }

            // ✅ Sauvegarder SANS réduire la qualité (garder qualité originale)
            Storage::disk($disk)->put($path, (string) $image->encode('jpg', 95));
        }
    }
}
