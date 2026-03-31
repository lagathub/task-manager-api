<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'due_date',
        'priority',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];


    public static array $priorityOrder = [
        'high'   => 1,
        'medium' => 2,
        'low'    => 3,
    ];


    public static array $statusChain = [
        'pending'     => 'in_progress',
        'in_progress' => 'done',
    ];

 
    public function nextStatus(): ?string
    {
        return self::$statusChain[$this->status] ?? null;
    }

  
    public function scopeOfStatus($query, ?string $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

 
    public function scopeSorted($query)
    {
        return $query->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
                     ->orderBy('due_date', 'asc');
    }
}
