<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SendFollowNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        private string $userId,
        private string $type,
        private array  $data
    ) {}

    public function handle(): void
    {
        DB::table('notifications')->insert([
            'id'         => (string) Str::uuid(),
            'user_id'    => $this->userId,
            'type'       => $this->type,
            'data'       => json_encode($this->data),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}