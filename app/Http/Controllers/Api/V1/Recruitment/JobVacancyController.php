<?php

namespace App\Http\Controllers\Api\V1\Recruitment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Recruitment\JobVacancyResource;
use App\Services\Recruitment\JobVacancyService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobVacancyController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected JobVacancyService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'department_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($jv) => (new JobVacancyResource($jv))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'employment_type' => 'nullable|string|max:50',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'currency_id' => 'nullable|exists:currencies,id',
            'openings' => 'nullable|integer|min:1',
            'closing_date' => 'nullable|date',
        ]);
        $jv = $this->service->create($validated);
        return $this->created(new JobVacancyResource($jv), 'Job vacancy created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $jv = $this->service->find($id);
        return $this->success(new JobVacancyResource($jv));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'employment_type' => 'nullable|string|max:50',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'openings' => 'nullable|integer|min:1',
            'closing_date' => 'nullable|date',
        ]);
        $jv = $this->service->update($id, $validated);
        return $this->success(new JobVacancyResource($jv), 'Job vacancy updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Job vacancy deleted successfully.');
    }

    public function publish(int $id): JsonResponse
    {
        $jv = $this->service->publish($id);
        return $this->success(new JobVacancyResource($jv), 'Job vacancy published.');
    }

    public function close(int $id): JsonResponse
    {
        $jv = $this->service->close($id);
        return $this->success(new JobVacancyResource($jv), 'Job vacancy closed.');
    }
}
