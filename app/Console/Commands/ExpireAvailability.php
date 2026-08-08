<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireAvailability extends Command
{
    protected $signature   = 'availability:expire';
    protected $description = 'Elimina registros de disponibilidad expirados';

    public function handle(): int
    {
        $deleted = DB::table('availability')
            ->where('expires_at', '<=', now())
            ->delete();

        $this->info("Eliminados {$deleted} registros de disponibilidad expirados.");
        return Command::SUCCESS;
    }
}