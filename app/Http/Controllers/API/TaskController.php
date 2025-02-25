<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Animal;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\Task\TaskResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\{Auth};

class TaskController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the tasks for a specific animal.
     */
    public function index(Animal $animal): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        $tasks = $animal->tasks()
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            TaskResource::collection($tasks),
            'Tasks retrieved successfully'
        );
    }

    /**
     * Store a newly created task for the specified animal.
     */
    public function store(StoreTaskRequest $request, Animal $animal): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        try {
            $validated = $request->validated();
            $validated['animal_id'] = $animal->id;
            $validated['user_id'] = Auth::id();

            $task = Task::create($validated);

            return $this->successResponse(
                new TaskResource($task),
                'Task created successfully',
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create task',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Display the specified task.
     */
    public function show(Animal $animal, Task $task): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        if ($task->animal_id !== $animal->id) {
            return $this->errorResponse(
                'Task not found for this animal',
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->successResponse(
            new TaskResource($task),
            'Task retrieved successfully'
        );
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateTaskRequest $request, Animal $animal, Task $task): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        if ($task->animal_id !== $animal->id) {
            return $this->errorResponse(
                'Task not found for this animal',
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $validated = $request->validated();
            // Prevent modification of animal_id and user_id
            unset($validated['animal_id'], $validated['user_id']);

            $task->update($validated);

            return $this->successResponse(
                new TaskResource($task),
                'Task updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to update task',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Animal $animal, Task $task): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        if ($task->animal_id !== $animal->id) {
            return $this->errorResponse(
                'Task not found for this animal',
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $task->delete();
            return $this->successResponse(
                null,
                'Task deleted successfully',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to delete task',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }
}
