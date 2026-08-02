<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MigrateVideosToSupabase extends Command
{
    protected $signature   = 'videos:migrate-to-supabase';
    protected $description = 'Migra videos de storage/app/private a Supabase Storage';

    public function handle(): void
    {
        $videos = DB::table('videos')->whereNotNull('file_path')->get();
        $this->info("Videos a migrar: {$videos->count()}");

        $bar = $this->output->createProgressBar($videos->count());
        $bar->start();

        $ok = 0; $fail = 0;

        foreach ($videos as $video) {
            $localPath = storage_path('app/private/' . ltrim($video->file_path, '/'));

            if (!file_exists($localPath)) {
                $this->newLine();
                $this->warn("NO EXISTE local: {$video->file_path}");
                $fail++;
                $bar->advance();
                continue;
            }

            try {
                $stream = fopen($localPath, 'rb');
                Storage::disk('supabase')->put(
                    $video->file_path,
                    $stream,
                    ['ContentType' => 'video/mp4', 'visibility' => 'private']
                );
                if (is_resource($stream)) fclose($stream);

                $ok++;
            } catch (\Throwable $e) {
                $this->newLine();
                $this->error("FALLO id={$video->id}: " . $e->getMessage());
                $fail++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Completado: {$ok} OK, {$fail} fallidos.");
        $this->info("Siguiente paso: php artisan videos:migrate-thumbnails-to-supabase");
    }
}