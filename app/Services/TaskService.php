<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

/**
 * TaskService
 *
 * Encapsulates all business logic for Task operations.
 * Controllers remain thin — they delegate to this service.
 */
class TaskService
{
    /**
     * Create a new task.
     */
    public function createTask(array $data): Task
    {
        return Task::create($data);
    }

    /**
     * List tasks with optional status filter, sorted by priority then due_date.
     */
    public function listTasks(?string $status): Collection
    {
        return Task::ofStatus($status)->sorted()->get();
    }

    /**
     * Advance a task's status following the strict progression chain:
     * pending → in_progress → done
     *
     * Throws 422 if the requested status is invalid or out of order.
     */
    public function advanceStatus(Task $task, string $newStatus): Task
    {
        $expectedNext = $task->nextStatus();

        if ($expectedNext === null) {
            $this->fail(422, "Task is already 'done' and cannot be updated further.");
        }

        if ($newStatus !== $expectedNext) {
            $this->fail(
                422,
                "Invalid status transition. '{$task->status}' can only move to '{$expectedNext}', not '{$newStatus}'."
            );
        }

        $task->status = $newStatus;
        $task->save();

        return $task;
    }

    /**
     * Delete a task — only allowed if status is 'done'.
     * Throws 403 otherwise.
     */
    public function deleteTask(Task $task): void
    {
        if ($task->status !== 'done') {
            $this->fail(403, "Only tasks with status 'done' can be deleted.");
        }

        $task->delete();
    }

    /**
     * Generate a daily report for a given date.
     * Returns counts grouped by priority and status.
     */
    public function dailyReport(string $date): array
    {
        $priorities = ['high', 'medium', 'low'];
        $statuses   = ['pending', 'in_progress', 'done'];

        // Build a base summary structure with all zeros
        $summary = [];
        foreach ($priorities as $priority) {
            foreach ($statuses as $status) {
                $summary[$priority][$status] = 0;
            }
        }

        // Query counts grouped by priority + status for the given date
        $results = DB::table('tasks')
            ->select('priority', 'status', DB::raw('COUNT(*) as count'))
            ->whereDate('due_date', $date)
            ->groupBy('priority', 'status')
            ->get();

        // Populate summary with actual counts
        foreach ($results as $row) {
            if (isset($summary[$row->priority][$row->status])) {
                $summary[$row->priority][$row->status] = (int) $row->count;
            }
        }

        return [
            'date'    => $date,
            'summary' => $summary,
        ];
    }

    /**
     * Throw an HttpResponseException with a JSON error response.
     */
    private function fail(int $statusCode, string $message): never
    {
        throw new HttpResponseException(
            response()->json(['error' => $message], $statusCode)
        );
    }
}
