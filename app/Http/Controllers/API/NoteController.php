<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Note\StoreNoteRequest;
use App\Http\Requests\Note\UpdateNoteRequest;
use App\Http\Resources\Note\NoteResource;
use App\Models\Animal;
use App\Models\Note;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Annotations as OA;

class NoteController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of notes for a specific animal.
     *
     * @OA\Get(
     *     path="/api/animals/{animal}/notes",
     *     tags={"Notes"},
     *     summary="Get a list of notes for a specific animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of notes per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=10, minimum=1, maximum=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of notes retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/NoteResource")
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string", example="http://api.example.com/api/animals/550e8400-e29b-41d4-a716-446655440000/notes?page=1"),
     *                 @OA\Property(property="last", type="string", example="http://api.example.com/api/animals/550e8400-e29b-41d4-a716-446655440000/notes?page=10"),
     *                 @OA\Property(property="prev", type="string", nullable=true, example=null),
     *                 @OA\Property(property="next", type="string", nullable=true, example="http://api.example.com/api/animals/550e8400-e29b-41d4-a716-446655440000/notes?page=2")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=10),
     *                 @OA\Property(property="path", type="string", example="http://api.example.com/api/animals/550e8400-e29b-41d4-a716-446655440000/notes"),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="to", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=100)
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

        $notes = $animal->notes()
            ->latest()
            ->paginate(10);

        return $this->successResponse(
            NoteResource::collection($notes),
            'Tasks retrieved successfully'
        );
    }

    /**
     * Store a newly created note for a specific animal.
     *
     * @OA\Post(
     *     path="/api/animals/{animal}/notes",
     *     tags={"Notes"},
     *     summary="Create a new note for a specific animal",
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
     *             @OA\Property(property="title", type="string", example="Feeding Schedule", description="Title of the note"),
     *             @OA\Property(property="description", type="string", example="Feed twice daily", description="Description of the note"),
     *             @OA\Property(property="priority", type="string", enum={"low", "medium", "high"}, example="medium", description="Priority level")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Note created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/NoteResource"),
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
    public function store(StoreNoteRequest $request, Animal $animal): JsonResponse
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

            $note = Note::create($validated);

            return $this->successResponse(
                new NoteResource($note),
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
     * Display the specified note.
     *
     * @OA\Get(
     *     path="/api/animals/{animal}/notes/{note}",
     *     tags={"Notes"},
     *     summary="Get a specific note for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="note",
     *         in="path",
     *         required=true,
     *         description="UUID of the note",
     *         @OA\Schema(type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Note retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/NoteResource"),
     *             @OA\Property(property="message", type="string", example="Note retrieved successfully"),
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
     *         description="Note not found for this animal",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task not found for this animal")
     *         )
     *     )
     * )
     */
    public function show(Animal $animal, Note $note): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        if ($note->animal_id !== $animal->id) {
            return $this->errorResponse(
                'Task not found for this animal',
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->successResponse(
            new NoteResource($note),
            'Note retrieved successfully'
        );
    }

    /**
     * Update the specified note.
     *
     * @OA\Put(
     *     path="/api/animals/{animal}/notes/{note}",
     *     tags={"Notes"},
     *     summary="Update a specific note for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="note",
     *         in="path",
     *         required=true,
     *         description="UUID of the note",
     *         @OA\Schema(type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Feeding Schedule", description="Updated title of the note"),
     *             @OA\Property(property="description", type="string", example="Feed thrice daily", description="Updated description of the note"),
     *             @OA\Property(property="priority", type="string", enum={"low", "medium", "high"}, example="high", description="Updated priority level")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Note updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/NoteResource"),
     *             @OA\Property(property="message", type="string", example="Note updated successfully"),
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
     *         description="Note not found for this animal",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Note not found for this animal")
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
     *             @OA\Property(property="message", type="string", example="Failed to update note"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function update(UpdateNoteRequest $request, Animal $animal, Note $note): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        if ($note->animal_id !== $animal->id) {
            return $this->errorResponse(
                'Note not found for this animal',
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $validated = $request->validated();
            // Prevent modification of animal_id and user_id
            unset($validated['animal_id'], $validated['user_id']);

            $note->update($validated);

            return $this->successResponse(
                new NoteResource($note),
                'Note updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to update note',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Remove the specified note.
     *
     * @OA\Delete(
     *     path="/api/animals/{animal}/notes/{note}",
     *     tags={"Notes"},
     *     summary="Delete a specific note for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="note",
     *         in="path",
     *         required=true,
     *         description="UUID of the note",
     *         @OA\Schema(type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Note deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Note deleted successfully"),
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
     *         description="Note not found for this animal",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Note not found for this animal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to delete note"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function destroy(Animal $animal, Note $note): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        if ($note->animal_id !== $animal->id) {
            return $this->errorResponse(
                'Note not found for this animal',
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $note->delete();
            return $this->successResponse(
                null,
                'Note deleted successfully',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to delete note',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }
}