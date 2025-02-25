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

class NoteController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of notes for a specific animal.
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
