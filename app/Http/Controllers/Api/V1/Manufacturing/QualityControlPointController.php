<?php

namespace App\Http\Controllers\Api\V1\Manufacturing;

use App\Http\Controllers\Controller;
use App\Http\Resources\QualityControlPointResource;
use App\Models\QualityControlPoint;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QualityControlPointController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = QualityControlPoint::query();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = $request->input('per_page', 15);
        $paginator = $query->orderBy('sequence')->paginate($perPage);
        $paginator->getCollection()->transform(fn($qcp) => (new QualityControlPointResource($qcp))->resolve($request));

        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'control_type' => 'required|string|max:50',
            'method' => 'nullable|string',
            'sequence' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'checklist' => 'nullable|array',
        ]);
        $validated['created_by'] = auth()->id();

        $qcp = QualityControlPoint::create($validated);
        return $this->created(new QualityControlPointResource($qcp), 'Quality control point created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $qcp = QualityControlPoint::findOrFail($id);
        return $this->success(new QualityControlPointResource($qcp));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $qcp = QualityControlPoint::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'control_type' => 'sometimes|required|string|max:50',
            'method' => 'nullable|string',
            'sequence' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'checklist' => 'nullable|array',
        ]);
        $validated['updated_by'] = auth()->id();

        $qcp->update($validated);
        return $this->success(new QualityControlPointResource($qcp), 'Quality control point updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        QualityControlPoint::findOrFail($id)->delete();
        return $this->deleted('Quality control point deleted successfully.');
    }
}
