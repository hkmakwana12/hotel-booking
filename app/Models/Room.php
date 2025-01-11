<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'room_number'];

    public function branch(): HasOneThrough
    {
        return $this->hasOneThrough(
            Branch::class, // Final model (destination)
            Category::class, // Intermediate model
            'id', // Foreign key on categories table (category.id)
            'id', // Foreign key on branches table (branch.id)
            'category_id', // Foreign key on rooms table (room.category_id)
            'branch_id'  // Foreign key on categories table (category.branch_id)
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
