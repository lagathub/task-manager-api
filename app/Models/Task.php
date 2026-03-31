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

    /**
     * Priority order map used for sorting (high → medium → low)
     */
    public static array $priorityOrder = [
        'high'   => 1,
        'medium' => 2,
        'low'    => 3,
    ];

    /**
     * Valid status progression chain
     */
    public static array $statusChain = [
        'pending'     => 'in_progress',
        'in_progress' => 'done',
    ];

    /**
     * Returns the next valid status for this task, or null if already done.
     */
    public function nextStatus(): ?string
    {
        return self::$statusChain[$this->status] ?? null;
    }

    /**
     * Scope: filter by status
     */
    public function scopeOfStatus($query, ?string $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope: sort by priority (high→low) then due_date ascending
     */
    public function scopeSorted($query)
    {
        return $query->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
                     ->orderBy('due_date', 'asc');
    }
}
