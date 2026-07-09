<?php

namespace App\Http\Controllers\Api\V1\HRM;

use App\Http\Controllers\Controller;
use App\Http\Resources\HRM\EmployeeDocumentResource;
use App\Services\HRM\EmployeeDocumentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeDocumentController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected EmployeeDocumentService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'employee_id', 'document_type']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($d) => (new EmployeeDocumentResource($d))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'document_type' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'file_path' => 'nullable|string|max:500',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        $doc = $this->service->create($validated);
        return $this->created(new EmployeeDocumentResource($doc), 'Document created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $doc = $this->service->find($id);
        return $this->success(new EmployeeDocumentResource($doc));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'document_type' => 'sometimes|string|max:50',
            'title' => 'sometimes|string|max:255',
            'file_path' => 'nullable|string|max:500',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        $doc = $this->service->update($id, $validated);
        return $this->success(new EmployeeDocumentResource($doc), 'Document updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Document deleted successfully.');
    }
}
