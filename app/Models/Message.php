<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Message extends Model
{
    use HasUuids;
    protected $table = 'messages';
    protected $fillable = ['sender_id', 'receiver_id', 'body', 'read', 'read_at'];
    protected $casts = ['read' => 'boolean', 'read_at' => 'datetime'];

    public function sender()   { return $this->belongsTo(User::class, 'sender_id'); }
    public function receiver() { return $this->belongsTo(User::class, 'receiver_id'); }
}