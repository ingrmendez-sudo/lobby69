<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Friendship extends Model
{
    use HasUuids;
    protected $table = 'friendships';
    protected $fillable = ['sender_id', 'receiver_id', 'status'];

    public function sender()   { return $this->belongsTo(User::class, 'sender_id'); }
    public function receiver() { return $this->belongsTo(User::class, 'receiver_id'); }

    public static function statusBetween(string $userA, string $userB): ?string {
        $f = static::where(function($q) use ($userA, $userB) {
            $q->where('sender_id', $userA)->where('receiver_id', $userB);
        })->orWhere(function($q) use ($userA, $userB) {
            $q->where('sender_id', $userB)->where('receiver_id', $userA);
        })->first();
        return $f?->status;
    }
}