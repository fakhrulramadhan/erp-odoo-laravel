<?php

namespace App\Http\Controllers\Api\V1\Recruitment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Recruitment\InterviewResource;
use App\Services\Recruitment\InterviewService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterviewController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected InterviewService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'applicant_id', 'interviewer_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($i) => (new InterviewResource($i))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'applicant_id' => 'required|exists:applicants,id',
            'interviewer_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:15',
            'location' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);
        $interview = $this->service->create($validated);
        return $this->created(new InterviewResource($interview), 'Interview scheduled successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $interview = $this->service->find($id);
        return $this->success(new InterviewResource($interview));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'scheduled_at' => 'sometimes|required|date',
            'duration_minutes' => 'nullable|integer|min:15',
            'location' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);
        $interview = $this->service->update($id, $validated);
        return $this->success(new InterviewResource($interview), 'Interview updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Interview deleted successfully.');
    }

    public function submitFeedback(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'required|string',
            'recommendation' => 'nullable|string|max:50',
        ]);
        $interview = $this->service->submitFeedback($id, $validated);
        return $this->success(new InterviewResource($interview), 'Feedback submitted.');
    }
}
