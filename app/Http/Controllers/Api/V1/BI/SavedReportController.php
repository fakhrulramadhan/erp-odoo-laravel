<?php

namespace App\Http\Controllers\Api\V1\BI;

use App\Http\Controllers\Controller;
use App\Services\BI\SavedReportService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavedReportController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected SavedReportService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->all(), $request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $v = $request->validate([
            'name' => 'required|string|max:255',
            'module' => 'required|string|max:100',
            'config' => 'nullable|array',
            'description' => 'nullable|string',
            'company_id' => 'nullable|exists:companies,id',
        ]);
        return $this->created($this->service->create($v), 'Report saved.');
    }

    public function show(int $id): JsonResponse { return $this->success($this->service->find($id)); }

    public function update(Request $request, int $id): JsonResponse
    {
        $v = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'config' => 'nullable|array',
            'description' => 'nullable|string',
        ]);
        return $this->success($this->service->update($id, $v), 'Report updated.');
    }

    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->deleted('Report deleted.'); }

    public function execute(int $id): JsonResponse
    {
        $result = $this->service->execute($id);
        return $this->success($result, 'Report executed.');
    }
}
