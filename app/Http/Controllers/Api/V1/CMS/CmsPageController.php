<?php

namespace App\Http\Controllers\Api\V1\CMS;

use App\Http\Controllers\Controller;
use App\Http\Resources\CMS\CmsPageResource;
use App\Services\CMS\CmsPageService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CmsPageController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CmsPageService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($p) => (new CmsPageResource($p))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|string|max:500',
            'template' => 'nullable|string|max:100',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'company_id' => 'nullable|exists:companies,id',
        ]);
        $page = $this->service->create($validated);
        return $this->created(new CmsPageResource($page), 'Page created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $page = $this->service->find($id);
        return $this->success(new CmsPageResource($page));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|string|max:500',
            'template' => 'nullable|string|max:100',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);
        $page = $this->service->update($id, $validated);
        return $this->success(new CmsPageResource($page), 'Page updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Page deleted successfully.');
    }

    public function publish(int $id): JsonResponse
    {
        $page = $this->service->publish($id);
        return $this->success(new CmsPageResource($page), 'Page published.');
    }

    public function unpublish(int $id): JsonResponse
    {
        $page = $this->service->unpublish($id);
        return $this->success(new CmsPageResource($page), 'Page unpublished.');
    }
}
