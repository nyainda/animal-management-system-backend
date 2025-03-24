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
use OpenApi\Annotations as OA;

class TaskController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the tasks for a specific animal.
     *
     * @OA\Get(
     *     path="/api/animals/{animal}/tasks",
     *     tags={"Tasks"},
     *     summary="Get a list of tasks for a specific animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of tasks retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/TaskResource")
     *             ),
     *             @OA\Property(property="message", type="string", example="Tasks retrieved successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User does not own the animal",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have access to this animal")
     *         )
     *     )
     * )
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
     *
     * @OA\Post(
     *     path="/api/animals/{animal}/tasks",
     *     tags={"Tasks"},
     *     summary="Create a new task for a specific animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Vaccination", description="Title of the task"),
     *             @OA\Property(property="task_type", type="string", example="medical", description="Type of the task"),
     *             @OA\Property(property="start_date", type="string", format="date", example="2025-03-25", description="Start date"),
     *             @OA\Property(property="start_time", type="string", format="time", example="09:00:00", description="Start time"),
     *             @OA\Property(property="end_date", type="string", format="date", nullable=true, example="2025-03-25", description="End date"),
     *             @OA\Property(property="end_time", type="string", format="time", nullable=true, example="10:00:00", description="End time"),
     *             @OA\Property(property="duration", type="integer", example=60, description="Duration in minutes"),
     *             @OA\Property(property="description", type="string", example="Administer vaccine", description="Task description"),
     *             @OA\Property(property="health_notes", type="string", nullable=true, example="Monitor side effects", description="Health notes"),
     *             @OA\Property(property="location", type="string", example="Barn A", description="Task location"),
     *             @OA\Property(property="priority", type="string", enum={"low", "medium", "high"}, example="high", description="Priority level"),
     *             @OA\Property(property="status", type="string", example="pending", description="Task status"),
     *             @OA\Property(property="repeats", type="boolean", example=false, description="Whether the task repeats"),
     *             @OA\Property(property="repeat_frequency", type="string", nullable=true, example="weekly", description="Repeat frequency"),
     *             @OA\Property(property="end_repeat_date", type="string", format="date", nullable=true, example="2025-12-31", description="End repeat date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/TaskResource"),
     *             @OA\Property(property="message", type="string", example="Task created successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User does not own the animal",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have access to this animal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to create task"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
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
     *
     * @OA\Get(
     *     path="/api/animals/{animal}/tasks/{task}",
     *     tags={"Tasks"},
     *     summary="Get a specific task for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="UUID of the task",
     *         @OA\Schema(type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/TaskResource"),
     *             @OA\Property(property="message", type="string", example="Task retrieved successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User does not own the animal",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have access to this animal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found for this animal",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task not found for this animal")
     *         )
     *     )
     * )
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
     *
     * @OA\Put(
     *     path="/api/animals/{animal}/tasks/{task}",
     *     tags={"Tasks"},
     *     summary="Update a specific task for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="UUID of the task",
     *         @OA\Schema(type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Vaccination", description="Updated title"),
     *             @OA\Property(property="task_type", type="string", example="medical", description="Updated task type"),
     *             @OA\Property(property="start_date", type="string", format="date", example="2025-03-26", description="Updated start date"),
     *             @OA\Property(property="start_time", type="string", format="time", example="10:00:00", description="Updated start time"),
     *             @OA\Property(property="end_date", type="string", format="date", nullable=true, example="2025-03-26", description="Updated end date"),
     *             @OA\Property(property="end_time", type="string", format="time", nullable=true, example="11:00:00", description="Updated end time"),
     *             @OA\Property(property="duration", type="integer", example=60, description="Updated duration in minutes"),
     *             @OA\Property(property="description", type="string", example="Updated vaccine administration", description="Updated description"),
     *             @OA\Property(property="health_notes", type="string", nullable=true, example="Check for reactions", description="Updated health notes"),
     *             @OA\Property(property="location", type="string", example="Barn B", description="Updated location"),
     *             @OA\Property(property="priority", type="string", enum={"low", "medium", "high"}, example="high", description="Updated priority"),
     *             @OA\Property(property="status", type="string", example="in_progress", description="Updated status"),
     *             @OA\Property(property="repeats", type="boolean", example=true, description="Updated repeat flag"),
     *             @OA\Property(property="repeat_frequency", type="string", nullable=true, example="monthly", description="Updated repeat frequency"),
     *             @OA\Property(property="end_repeat_date", type="string", format="date", nullable=true, example="2025-12-31", description="Updated end repeat date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/TaskResource"),
     *             @OA\Property(property="message", type="string", example="Task updated successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User does not own the animal",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have access to this animal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found for this animal",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task not found for this animal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to update task"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
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
     *
     * @OA\Delete(
     *     path="/api/animals/{animal}/tasks/{task}",
     *     tags={"Tasks"},
     *     summary="Delete a specific task for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="UUID of the task",
     *         @OA\Schema(type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Task deleted successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User does not own the animal",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have access to this animal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found for this animal",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task not found for this animal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to delete task"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
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