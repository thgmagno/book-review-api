<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function scopeTitle(Builder $query, string $title): Builder
    {
        return $query->where('title', 'ILIKE', "%{$title}%");
    }

    public function scopeAuthor(Builder $query, string $author): Builder
    {
        return $query->where('author', 'ILIKE', "%{$author}%");
    }

    public function scopePopular(Builder $query, $from = null, $to = null): Builder
    {
        return $query->withCount([
            'reviews' => fn (Builder $q) => $this->dateRangeFilter($q, $from, $to),
        ])
            ->orderBy('reviews_count', 'desc');
    }

    public function scopeHighestRated(Builder $query): Builder
    {
        return $query->withAvg('reviews', 'rating')
            ->orderBy('reviews_avg_rating', 'desc');
    }

    private function dateRangeFilter(Builder $query, $from = null, $to = null): Builder
    {
        if ($from && ! $to) {
            return $query->where('created_at', '>=', $from);
        } elseif (! $from && $to) {
            return $query->where('created_at', '<=', $to);
        } else {
            return $query->whereBetween('created_at', [$from, $to]);
        }
    }
}
