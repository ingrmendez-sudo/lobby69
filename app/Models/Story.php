<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'cover_image',
        'category',
        'status',
        'views',
        'reading_time',
    ];

    protected $casts = [
        'views'        => 'integer',
        'reading_time' => 'integer',
        'user_id'      => 'string',   // ← uuid se trata como string
    ];


    // ─── Relaciones ────────────────────────────────────────────────
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ─── Scopes ────────────────────────────────────────────────────
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ─── Helpers ───────────────────────────────────────────────────
    public static function generateSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i    = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public static function calculateReadingTime(string $htmlContent): int
    {
        $text  = strip_tags($htmlContent);
        $words = str_word_count($text);
        return max(1, (int) ceil($words / 200)); // 200 palabras/minuto
    }

    public function getExcerptAttribute(): string
    {
        return Str::limit(strip_tags($this->content), 160);
    }

    public function incrementViews(): void
    {
        $this->increment('views');
    }
}
