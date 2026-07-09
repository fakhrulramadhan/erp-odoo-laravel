<?php

namespace App\Http\Controllers\Api\V1\Document;

use App\Http\Controllers\Controller;
use App\Services\Document\DocumentFolderService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentFolderController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected DocumentFolderService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->all(), $request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $v = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:document_folders,id',
            'description' => 'nullable|string',
        ]);
        return $this->created($this->service->create($v), 'Folder created.');
    }

    public function show(int $id): JsonResponse { return $this->success($this->service->find($id)); }

    public function update(Request $request, int $id): JsonResponse
    {
        $v = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'parent_id' => 'nullable|exists:document_folders,id',
            'description' => 'nullable|string',
        ]);
        return $this->success($this->service->update($id, $v), 'Folder updated.');
    }

    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->deleted('Folder deleted.'); }
}
