<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProfileView extends Model
{
    use HasUuids;
    public $timestamps = false;
    protected $table = 'profile_views';
    protected $fillable = ['viewer_id', 'viewed_id', 'viewed_at'];
    protected $casts = ['viewed_at' => 'datetime'];

    public function viewer() { return $this->belongsTo(User::class, 'viewer_id'); }
    public function viewed() { return $this->belongsTo(User::class, 'viewed_id'); }
}