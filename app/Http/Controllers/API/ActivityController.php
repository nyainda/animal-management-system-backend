<?php
// app/Http/Controllers/Api/AnimalActivityController.php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\CreateActivityRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Animal;
use App\Models\AnimalActivity;
use App\Services\AnimalActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\{Auth};
use App\Traits\ApiResponse;

class ActivityController extends Controller
{
    use ApiResponse;

    protected $activityService;

    public function __construct(AnimalActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Get activities for an animal
     */
    public function index(Request $request, Animal $animal): ResourceCollection
    {
        $activities = $animal->activities()
            ->when($request->type, fn($query, $type) => $query->where('activity_type', $type))
            ->when($request->from_date, fn($query, $date) => $query->whereDate('activity_date', '>=', $date))
            ->when($request->to_date, fn($query, $date) => $query->whereDate('activity_date', '<=', $date))
            ->latest('activity_date')
            ->paginate($request->per_page ?? 15);

        return ActivityResource::collection($activities);
    }

    /**
     * Create a new activity
     */
    public function store(CreateActivityRequest $request, Animal $animal)
    {
        $validatedData = $request->validated();
        $activity = $animal->activities()->create([
            ...$validatedData,
            'user_id' => Auth::id(),
            'is_automatic' => false
        ]);

        return $this->successResponse(
            new ActivityResource($activity),
            'Activity created successfully',
            201
        );
    }

    /**
     * Get a specific activity
     */
    public function show(Animal $animal, AnimalActivity $activity)
    {
        return $this->successResponse(
            new ActivityResource($activity),
            'Activity retrieved successfully'
        );
    }

    /**
     * Delete an activity
     */
    public function destroy(Animal $animal, AnimalActivity $activity)
    {
        abort_if($activity->is_automatic, 403, 'Cannot delete automatic activities');

        $activity->delete();

        return $this->successResponse(
            null,
            'Activity deleted successfully'
        );
    }

    /**
     * Generate birthday activities manually
     */
    public function generateBirthdayActivities()
    {
        $this->activityService->generateBirthdayActivities();

        return $this->successResponse(
            null,
            'Birthday activities generated successfully'
        );
    }
}
