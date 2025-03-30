<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Supplier;
use App\Models\SupplierContact;
use App\Http\Requests\Suppliers\StoreSupplierRequest;
use App\Http\Resources\Suppliers\SupplierResource;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Exception;
use OpenApi\Annotations as OA;

class SupplierController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/api/animals/{animal}/suppliers",
     *     tags={"Suppliers"},
     *     summary="Get a list of suppliers for a specific animal",
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
     *         description="Number of suppliers per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=10, minimum=1, maximum=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of suppliers retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/SupplierResource")
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string", example="http://api.example.com/api/animals/550e8400-e29b-41d4-a716-446655440000/suppliers?page=1"),
     *                 @OA\Property(property="last", type="string", example="http://api.example.com/api/animals/550e8400-e29b-41d4-a716-446655440000/suppliers?page=10"),
     *                 @OA\Property(property="prev", type="string", nullable=true, example=null),
     *                 @OA\Property(property="next", type="string", nullable=true, example="http://api.example.com/api/animals/550e8400-e29b-41d4-a716-446655440000/suppliers?page=2")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=10),
     *                 @OA\Property(property="path", type="string", example="http://api.example.com/api/animals/550e8400-e29b-41d4-a716-446655440000/suppliers"),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="to", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=100)
     *             ),
     *             @OA\Property(property="message", type="string", example="Suppliers retrieved successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid animal ID format",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid animal ID format")
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
    public function index(Animal $animal)
    {
        if (!Str::isUuid($animal->id)) {
            return $this->errorResponse(
                'Invalid animal ID format',
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        $suppliers = $animal->suppliers()
            ->with(['contacts', 'category'])
            ->paginate(10);

        return $this->successResponse(
            SupplierResource::collection($suppliers),
            'Suppliers retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/animals/{animal}/suppliers",
     *     tags={"Suppliers"},
     *     summary="Create a new supplier for a specific animal",
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
     *             @OA\Property(property="name", type="string", example="Supplier Name", description="Supplier name"),
     *             @OA\Property(property="email", type="string", format="email", example="supplier29@example.com", description="Supplier email"),
     *             @OA\Property(property="phone", type="string", example="9223-456-7890", description="Supplier phone"),
     *             @OA\Property(property="tax_number", type="string", example="TNr8123456", description="Tax number"),
     *             @OA\Property(property="address", type="string", example="123 Main St", description="Address"),
     *             @OA\Property(property="city", type="string", example="Anytown", description="City"),
     *             @OA\Property(property="state", type="string", example="CA", description="State"),
     *             @OA\Property(property="postal_code", type="string", example="90210", description="Postal code"),
     *             @OA\Property(property="country", type="string", example="USA", description="Country"),
     *             @OA\Property(property="latitude", type="number", format="float", example=34.0522, description="Latitude"),
     *             @OA\Property(property="longitude", type="number", format="float", example=-118.2437, description="Longitude"),
     *             @OA\Property(property="type", type="string", example="feed", description="Supplier type"),
     *             @OA\Property(property="product_type", type="string", example="Animal Feed", description="Product type"),
     *             @OA\Property(property="shop_name", type="string", example="Example Feed Supply", description="Shop name"),
     *             @OA\Property(property="business_registration_number", type="string", example="BRN17293456", description="Business registration number"),
     *             @OA\Property(property="contract_start_date", type="string", format="date", example="2024-01-01", description="Contract start date"),
     *             @OA\Property(property="contract_end_date", type="string", format="date", example="2025-01-01", description="Contract end date"),
     *             @OA\Property(property="account_holder", type="string", example="John Doe", description="Account holder"),
     *             @OA\Property(property="account_number", type="string", example="1234567890", description="Account number"),
     *             @OA\Property(property="bank_name", type="string", example="Example Bank", description="Bank name"),
     *             @OA\Property(property="bank_branch", type="string", example="Main Branch", description="Bank branch"),
     *             @OA\Property(property="swift_code", type="string", example="EXMPL123", description="SWIFT code"),
     *             @OA\Property(property="iban", type="string", example="IBAN1234567890", description="IBAN"),
     *             @OA\Property(property="supplier_importance", type="string", example="medium", description="Supplier importance"),
     *             @OA\Property(property="inventory_level", type="integer", example=100, description="Inventory level"),
     *             @OA\Property(property="reorder_point", type="integer", example=50, description="Reorder point"),
     *             @OA\Property(property="minimum_order_quantity", type="integer", example=20, description="Minimum order quantity"),
     *             @OA\Property(property="lead_time_days", type="integer", example=7, description="Lead time in days"),
     *             @OA\Property(property="payment_terms", type="string", example="net30", description="Payment terms"),
     *             @OA\Property(property="credit_limit", type="number", format="float", example=10000.00, description="Credit limit"),
     *             @OA\Property(property="currency", type="string", example="USD", description="Currency"),
     *             @OA\Property(property="tax_rate", type="number", format="float", example=10.00, description="Tax rate"),
     *             @OA\Property(property="supplier_rating", type="number", format="float", example=4.5, description="Supplier rating"),
     *             @OA\Property(property="status", type="string", example="active", description="Status"),
     *             @OA\Property(property="notes", type="string", example="Some notes about the supplier.", description="Notes"),
     *             @OA\Property(
     *                 property="meta_data",
     *                 type="object",
     *                 description="Additional metadata for the supplier",
     *                 @OA\Property(property="key1", type="string", example="value1", description="Custom key-value pair"),
     *                 @OA\Property(property="key2", type="string", example="value2", description="Custom key-value pair")
     *             ),
     *             @OA\Property(property="contact_name", type="string", example="Jane Smith", description="Primary contact name"),
     *             @OA\Property(property="contact_position", type="string", example="Sales Manager", description="Primary contact position"),
     *             @OA\Property(property="contact_email", type="string", format="email", example="jane.smith@example.com", description="Primary contact email"),
     *             @OA\Property(property="contact_phone", type="string", example="987-654-3210", description="Primary contact phone")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Supplier created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/SupplierResource"),
     *             @OA\Property(property="message", type="string", example="Supplier created successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid animal ID format",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid animal ID format")
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
     *             @OA\Property(property="message", type="string", example="Failed to create supplier"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function store(StoreSupplierRequest $request, Animal $animal)
    {
        if (!Str::isUuid($animal->id)) {
            return $this->errorResponse(
                'Invalid animal ID format',
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        try {
            $supplier = DB::transaction(function () use ($request, $animal) {
                $validated = $request->validated();
                $supplierId = Str::uuid();

                $supplier = Supplier::create([
                    'id' => $supplierId,
                    ...$validated,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);

                if (!empty($validated['contact_name'])) {
                    $supplier->contacts()->create([
                        'id' => Str::uuid(),
                        'name' => $validated['contact_name'],
                        'position' => $validated['contact_position'] ?? null,
                        'email' => $validated['contact_email'] ?? null,
                        'phone' => $validated['contact_phone'] ?? null,
                        'is_primary' => true,
                    ]);
                }

                if (!empty($validated['additional_contacts'])) {
                    foreach ($validated['additional_contacts'] as $contact) {
                        $supplier->contacts()->create([
                            'id' => Str::uuid(),
                            ...$contact,
                            'is_primary' => false,
                        ]);
                    }
                }

                $animal->suppliers()->attach($supplier->id, [
                    'id' => Str::uuid(),
                    'relationship_type' => $validated['relationship_type'] ?? 'primary',
                    'start_date' => $validated['start_date'] ?? now(),
                    'end_date' => $validated['end_date'] ?? null,
                    'notes' => $validated['relationship_notes'] ?? null
                ]);

                $supplier->load(['contacts', 'category', 'animals']);
                return $supplier;
            });

            return $this->successResponse(
                new SupplierResource($supplier),
                'Supplier created successfully',
                Response::HTTP_CREATED
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'Failed to create supplier',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/animals/{animal}/suppliers/{supplier}",
     *     tags={"Suppliers"},
     *     summary="Get a specific supplier for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="supplier",
     *         in="path",
     *         required=true,
     *         description="UUID of the supplier",
     *         @OA\Schema(type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Supplier retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/SupplierResource"),
     *             @OA\Property(property="message", type="string", example="Supplier retrieved successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID format",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid ID format")
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
     *         description="Supplier not associated with this animal",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="This supplier is not associated with the specified animal")
     *         )
     *     )
     * )
     */
    public function show(Animal $animal, Supplier $supplier)
    {
        if (!Str::isUuid($animal->id) || !Str::isUuid($supplier->id)) {
            return $this->errorResponse(
                'Invalid ID format',
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        if (!$animal->suppliers()->where('suppliers.id', $supplier->id)->exists()) {
            return $this->errorResponse(
                'This supplier is not associated with the specified animal',
                Response::HTTP_NOT_FOUND
            );
        }

        $supplier->load(['contacts', 'category', 'animals']);
        return $this->successResponse(
            new SupplierResource($supplier),
            'Supplier retrieved successfully'
        );
    }

    /**
     * @OA\Put(
     *     path="/api/animals/{animal}/suppliers/{supplier}",
     *     tags={"Suppliers"},
     *     summary="Update a specific supplier for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="supplier",
     *         in="path",
     *         required=true,
     *         description="UUID of the supplier",
     *         @OA\Schema(type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Farm Supplies", description="Updated supplier name"),
     *             @OA\Property(property="email", type="string", format="email", example="newcontact@farmsupplies.com", description="Updated supplier email"),
     *             @OA\Property(property="phone", type="string", example="+1234567890", description="Updated supplier phone"),
     *             @OA\Property(property="website", type="string", nullable=true, example="https://newfarmsupplies.com", description="Updated supplier website"),
     *             @OA\Property(property="tax_number", type="string", nullable=true, example="TAX654321", description="Updated tax number"),
     *             @OA\Property(property="address", type="string", example="456 Farm Road", description="Updated address"),
     *             @OA\Property(property="city", type="string", example="Springfield", description="Updated city"),
     *             @OA\Property(property="state", type="string", example="IL", description="Updated state"),
     *             @OA\Property(property="postal_code", type="string", example="62702", description="Updated postal code"),
     *             @OA\Property(property="country", type="string", example="USA", description="Updated country"),
     *             @OA\Property(property="latitude", type="number", format="float", nullable=true, example=39.7817, description="Updated latitude"),
     *             @OA\Property(property="longitude", type="number", format="float", nullable=true, example=-89.6501, description="Updated longitude"),
     *             @OA\Property(property="type", type="string", example="vendor", description="Updated supplier type"),
     *             @OA\Property(property="product_type", type="string", example="feed", description="Updated product type"),
     *             @OA\Property(property="shop_name", type="string", nullable=true, example="New Farm Store", description="Updated shop name"),
     *             @OA\Property(property="business_registration_number", type="string", nullable=true, example="BRN123456", description="Updated business registration number"),
     *             @OA\Property(property="contract_start_date", type="string", format="date", nullable=true, example="2025-02-01", description="Updated contract start date"),
     *             @OA\Property(property="contract_end_date", type="string", format="date", nullable=true, example="2026-01-31", description="Updated contract end date"),
     *             @OA\Property(property="account_holder", type="string", nullable=true, example="Updated Farm Supplies", description="Updated account holder"),
     *             @OA\Property(property="account_number", type="string", nullable=true, example="0987654321", description="Updated account number"),
     *             @OA\Property(property="bank_name", type="string", nullable=true, example="Second National Bank", description="Updated bank name"),
     *             @OA\Property(property="bank_branch", type="string", nullable=true, example="Downtown Branch", description="Updated bank branch"),
     *             @OA\Property(property="swift_code", type="string", nullable=true, example="SNUS33XXX", description="Updated SWIFT code"),
     *             @OA\Property(property="iban", type="string", nullable=true, example="US09876543210987654321", description="Updated IBAN"),
     *             @OA\Property(property="supplier_importance", type="string", example="medium", description="Updated supplier importance"),
     *             @OA\Property(property="inventory_level", type="integer", example=150, description="Updated inventory level"),
     *             @OA\Property(property="reorder_point", type="integer", example=30, description="Updated reorder point"),
     *             @OA\Property(property="minimum_order_quantity", type="integer", example=75, description="Updated minimum order quantity"),
     *             @OA\Property(property="lead_time_days", type="integer", example=7, description="Updated lead time in days"),
     *             @OA\Property(property="payment_terms", type="string", example="Net 60", description="Updated payment terms"),
     *             @OA\Property(property="credit_limit", type="number", format="float", example=7500.00, description="Updated credit limit"),
     *             @OA\Property(property="currency", type="string", example="USD", description="Updated currency"),
     *             @OA\Property(property="tax_rate", type="number", format="float", example=0.09, description="Updated tax rate"),
     *             @OA\Property(property="supplier_rating", type="number", format="float", example=4.8, description="Updated supplier rating"),
     *             @OA\Property(property="status", type="string", example="active", description="Updated status"),
     *             @OA\Property(property="notes", type="string", nullable=true, example="Updated reliable supplier", description="Updated notes"),
     *             @OA\Property(property="contact_name", type="string", example="John Doe", description="Updated primary contact name"),
     *             @OA\Property(property="contact_position", type="string", nullable=true, example="New Sales Manager", description="Updated primary contact position"),
     *             @OA\Property(property="contact_email", type="string", format="email", nullable=true, example="john@farmsupplies.com", description="Updated primary contact email"),
     *             @OA\Property(property="contact_phone", type="string", nullable=true, example="+1234567892", description="Updated primary contact phone"),
     *             @OA\Property(
     *                 property="additional_contacts",
     *                 type="array",
     *                 nullable=true,
     *                 description="Updated additional supplier contacts",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string", example="Jane Smith", description="Contact name"),
     *                     @OA\Property(property="position", type="string", nullable=true, example="Updated Assistant", description="Contact position"),
     *                     @OA\Property(property="email", type="string", format="email", nullable=true, example="jane.smith@farmsupplies.com", description="Contact email"),
     *                     @OA\Property(property="phone", type="string", nullable=true, example="+1234567893", description="Contact phone")
     *                 )
     *             ),
     *             @OA\Property(property="relationship_type", type="string", example="secondary", description="Updated relationship type"),
     *             @OA\Property(property="start_date", type="string", format="date", nullable=true, example="2025-02-01", description="Updated relationship start date"),
     *             @OA\Property(property="end_date", type="string", format="date", nullable=true, example="2026-01-31", description="Updated relationship end date"),
     *             @OA\Property(property="relationship_notes", type="string", nullable=true, example="Updated feed supplier", description="Updated relationship notes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Supplier updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/SupplierResource"),
     *             @OA\Property(property="message", type="string", example="Supplier updated successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID format",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid ID format")
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
     *         description="Supplier not associated with this animal",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="This supplier is not associated with the specified animal")
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
     *             @OA\Property(property="message", type="string", example="Failed to update supplier"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function update(StoreSupplierRequest $request, Animal $animal, Supplier $supplier)
    {
        if (!Str::isUuid($animal->id) || !Str::isUuid($supplier->id)) {
            return $this->errorResponse(
                'Invalid ID format',
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        if (!$animal->suppliers()->where('suppliers.id', $supplier->id)->exists()) {
            return $this->errorResponse(
                'This supplier is not associated with the specified animal',
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $updatedSupplier = DB::transaction(function () use ($request, $animal, $supplier) {
                $validated = $request->validated();

                $supplier->update([
                    ...$validated,
                    'updated_by' => Auth::id(),
                ]);

                if (!empty($validated['contact_name'])) {
                    $primaryContact = $supplier->contacts()
                        ->where('is_primary', true)
                        ->first();

                    if ($primaryContact) {
                        $primaryContact->update([
                            'name' => $validated['contact_name'],
                            'position' => $validated['contact_position'] ?? $primaryContact->position,
                            'email' => $validated['contact_email'] ?? $primaryContact->email,
                            'phone' => $validated['contact_phone'] ?? $primaryContact->phone,
                        ]);
                    } else {
                        $supplier->contacts()->create([
                            'id' => Str::uuid(),
                            'name' => $validated['contact_name'],
                            'position' => $validated['contact_position'] ?? null,
                            'email' => $validated['contact_email'] ?? null,
                            'phone' => $validated['contact_phone'] ?? null,
                            'is_primary' => true,
                        ]);
                    }
                }

                if (!empty($validated['additional_contacts'])) {
                    $supplier->contacts()
                        ->where('is_primary', false)
                        ->delete();

                    foreach ($validated['additional_contacts'] as $contact) {
                        $supplier->contacts()->create([
                            'id' => Str::uuid(),
                            ...$contact,
                            'is_primary' => false,
                        ]);
                    }
                }

                if (isset($validated['relationship_type']) ||
                    isset($validated['start_date']) ||
                    isset($validated['end_date']) ||
                    isset($validated['relationship_notes'])) {
                    $animal->suppliers()->updateExistingPivot($supplier->id, [
                        'relationship_type' => $validated['relationship_type'] ?? null,
                        'start_date' => $validated['start_date'] ?? null,
                        'end_date' => $validated['end_date'] ?? null,
                        'notes' => $validated['relationship_notes'] ?? null
                    ]);
                }

                $supplier->load(['contacts', 'category', 'animals']);
                return $supplier;
            });

            return $this->successResponse(
                new SupplierResource($updatedSupplier),
                'Supplier updated successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'Failed to update supplier',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/animals/{animal}/suppliers/{supplier}",
     *     tags={"Suppliers"},
     *     summary="Delete a supplier association or the supplier itself for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="supplier",
     *         in="path",
     *         required=true,
     *         description="UUID of the supplier",
     *         @OA\Schema(type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8")
     *     ),
     *     @OA\Parameter(
     *         name="force_delete",
     *         in="query",
     *         required=false,
     *         description="Force delete the supplier and its contacts if true",
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Supplier successfully removed",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Supplier successfully removed"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID format",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid ID format")
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
     *         description="Supplier not associated with this animal",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="This supplier is not associated with the specified animal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to delete supplier"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function destroy(Animal $animal, Supplier $supplier, Request $request)
    {
        if (!Str::isUuid($animal->id) || !Str::isUuid($supplier->id)) {
            return $this->errorResponse(
                'Invalid ID format',
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        if (!$animal->suppliers()->where('suppliers.id', $supplier->id)->exists()) {
            return $this->errorResponse(
                'This supplier is not associated with the specified animal',
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $animalCount = $supplier->animals()->count();

            DB::transaction(function () use ($animal, $supplier, $request, $animalCount) {
                // Always remove the relationship
                $animal->suppliers()->detach($supplier->id);

                // If force delete is requested or this is the only animal, delete everything
                if ($request->query('force_delete', false) || $animalCount <= 1) {
                    $supplier->contacts()->delete();
                    $supplier->delete();
                }
            });

            return $this->successResponse(
                null,
                'Supplier successfully removed'
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'Failed to delete supplier',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }
}