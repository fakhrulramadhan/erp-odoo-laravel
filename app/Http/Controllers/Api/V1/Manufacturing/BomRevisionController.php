<?php

namespace App\Http\Controllers\Api\V1\Manufacturing;

use App\Http\Controllers\Controller;
use App\Http\Resources\BomRevisionResource;
use App\Models\BillOfMaterial;
use App\Models\BomRevision;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BomRevisionController extends Controller
{
    use ApiResponse;

    public function index(Request $request, int $bomId): JsonResponse
    {
        $bom = BillOfMaterial::findOrFail($bomId);
        $revisions = $bom->revisions()->orderBy('revision_number', 'desc')->get();
        return $this->success(BomRevisionResource::collection($revisions));
    }

    public function show(int $bomId, int $id): JsonResponse
    {
        $revision = BomRevision::where('bom_id', $bomId)->findOrFail($id);
        return $this->success(new BomRevisionResource($revision));
    }
}
