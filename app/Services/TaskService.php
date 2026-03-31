<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;


class TaskService
{
 
    public function createTask(array $data): Task
    {
        return Task::create($data);
    }

 
    public function listTasks(?string $status): Collection
    {
        return Task::ofStatus($status)->sorted()->get();
    }

 
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

  
    public function deleteTask(Task $task): void
    {
        if ($task->status !== 'done') {
            $this->fail(403, "Only tasks with status 'done' can be deleted.");
        }

        $task->delete();
    }


    public function dailyReport(string $date): array
    {
        $priorities = ['high', 'medium', 'low'];
        $statuses   = ['pending', 'in_progress', 'done'];

        
        $summary = [];
        foreach ($priorities as $priority) {
            foreach ($statuses as $status) {
                $summary[$priority][$status] = 0;
            }
        }

        
        $results = DB::table('tasks')
            ->select('priority', 'status', DB::raw('COUNT(*) as count'))
            ->whereDate('due_date', $date)
            ->groupBy('priority', 'status')
            ->get();

        
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

 
    private function fail(int $statusCode, string $message): never
    {
        throw new HttpResponseException(
            response()->json(['error' => $message], $statusCode)
        );
    }
}
