<?php

namespace App\Http\Controllers\Api\V1\MultiCompany;

use App\Http\Controllers\Controller;
use App\Services\MultiCompany\CompanyGroupService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyGroupController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CompanyGroupService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->all(), $request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $v = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'companies' => 'nullable|array',
            'companies.*' => 'exists:companies,id',
        ]);
        return $this->created($this->service->create($v), 'Company group created.');
    }

    public function show(int $id): JsonResponse { return $this->success($this->service->find($id)); }

    public function update(Request $request, int $id): JsonResponse
    {
        $v = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'companies' => 'nullable|array',
            'companies.*' => 'exists:companies,id',
        ]);
        return $this->success($this->service->update($id, $v), 'Company group updated.');
    }

    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->deleted('Company group deleted.'); }
}
