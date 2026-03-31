<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController
{
    /**
     * Inject TaskService via constructor (Dependency Injection)
     */
    public function __construct(private readonly TaskService $taskService)
    {
    }

    /**
     * POST /api/tasks
     * Create a new task.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->createTask($request->validated());

        return response()->json([
            'message' => 'Task created successfully.',
            'data'    => $task,
        ], 201);
    }

    /**
     * GET /api/tasks
     * List all tasks sorted by priority then due_date.
     * Optional: ?status=pending|in_progress|done
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $tasks  = $this->taskService->listTasks($status);

        if ($tasks->isEmpty()) {
            return response()->json([
                'message' => 'No tasks found.',
                'data'    => [],
            ]);
        }

        return response()->json([
            'message' => 'Tasks retrieved successfully.',
            'data'    => $tasks,
        ]);
    }

    /**
     * PATCH /api/tasks/{id}/status
     * Advance a task's status (pending → in_progress → done only).
     */
    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): JsonResponse
    {
        $updated = $this->taskService->advanceStatus($task, $request->validated('status'));

        return response()->json([
            'message' => 'Task status updated successfully.',
            'data'    => $updated,
        ]);
    }

    /**
     * DELETE /api/tasks/{id}
     * Delete a task — only allowed if status is 'done'.
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->taskService->deleteTask($task);

        return response()->json([
            'message' => 'Task deleted successfully.',
        ]);
    }

    /**
     * GET /api/tasks/report?date=YYYY-MM-DD
     * Daily report: counts per priority and status for a given date.
     */
    public function report(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date|date_format:Y-m-d',
        ]);

        $report = $this->taskService->dailyReport($request->query('date'));

        return response()->json($report);
    }
}
