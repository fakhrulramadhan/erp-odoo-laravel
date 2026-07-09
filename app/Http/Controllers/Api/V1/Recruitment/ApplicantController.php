<?php

namespace App\Http\Controllers\Api\V1\Recruitment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Recruitment\ApplicantResource;
use App\Services\Recruitment\ApplicantService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicantController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ApplicantService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'stage', 'vacancy_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($a) => (new ApplicantResource($a))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vacancy_id' => 'required|exists:job_vacancies,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'resume_path' => 'nullable|string|max:500',
            'cover_letter' => 'nullable|string',
            'source' => 'nullable|string|max:50',
        ]);
        $applicant = $this->service->create($validated);
        return $this->created(new ApplicantResource($applicant), 'Applicant created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $applicant = $this->service->find($id);
        return $this->success(new ApplicantResource($applicant));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'resume_path' => 'nullable|string|max:500',
            'cover_letter' => 'nullable|string',
        ]);
        $applicant = $this->service->update($id, $validated);
        return $this->success(new ApplicantResource($applicant), 'Applicant updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Applicant deleted successfully.');
    }

    public function updateStage(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['stage' => 'required|string|max:50']);
        $applicant = $this->service->updateStage($id, $validated['stage']);
        return $this->success(new ApplicantResource($applicant), 'Applicant stage updated.');
    }
}
