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

            // Ajouter le watermark texte "ELSAYARA" (style LaCentrale)
            // Config: config/app.php - watermark_opacity (défaut: 0.20 = 20%)
            //                        - watermark_width (défaut: 0.65 = 65%)
            $watermarkOpacity = config('app.watermark_opacity', 0.20);
            $watermarkWidth = config('app.watermark_width', 0.65);
            
            // Calculer la taille de police pour que le texte fasse 65% de la largeur
            // Pour "ELSAYARA" (8 chars), ratio approximatif: fontSize = targetWidth / 4.8
            $targetTextWidth = $image->width() * $watermarkWidth;
            $fontSize = (int) ($targetTextWidth / 4.8);
            
            // Utiliser Arial Bold (système Windows/Linux)
            $fontPath = null;
            $possibleFonts = [
                'C:/Windows/Fonts/arialbd.ttf',  // Windows Arial Bold
                'C:/Windows/Fonts/arial.ttf',    // Windows Arial Regular
                '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',  // Linux Liberation Sans Bold
                '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',         // Linux DejaVu Sans Bold
            ];
            
            foreach ($possibleFonts as $font) {
                if (file_exists($font)) {
                    $fontPath = $font;
                    break;
                }
            }
            
            $image->text('ELSAYARA', $image->width() / 2, $image->height() / 2, function($font) use ($fontSize, $watermarkOpacity, $fontPath) {
                if ($fontPath) {
                    $font->file($fontPath);
                }
                $font->size($fontSize);
                $font->color([255, 255, 255, $watermarkOpacity]); // Blanc avec opacité configurable
                $font->align('center');
                $font->valign('middle');
            });

            // ✅ Sauvegarder SANS réduire la qualité (garder qualité originale)
            Storage::disk($disk)->put($path, (string) $image->encode('jpg', 95));
        }
    }
}
