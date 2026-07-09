<?php

namespace App\Http\Controllers\Api\V1\Document;

use App\Http\Controllers\Controller;
use App\Http\Resources\Document\DocumentResource;
use App\Services\Document\DocumentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected DocumentService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'folder_id', 'owner_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($d) => (new DocumentResource($d))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'folder_id' => 'nullable|exists:document_folders,id',
            'file_path' => 'nullable|string|max:500',
            'file_type' => 'nullable|string|max:50',
            'file_size' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);
        $doc = $this->service->create($validated);
        return $this->created(new DocumentResource($doc), 'Document created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $doc = $this->service->find($id);
        return $this->success(new DocumentResource($doc));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'folder_id' => 'nullable|exists:document_folders,id',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);
        $doc = $this->service->update($id, $validated);
        return $this->success(new DocumentResource($doc), 'Document updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Document deleted successfully.');
    }

    public function addVersion(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'file_path' => 'required|string|max:500',
            'notes' => 'nullable|string',
        ]);
        $doc = $this->service->addVersion($id, $validated);
        return $this->success(new DocumentResource($doc), 'Document version added.');
    }
}
