<?php

namespace App\Http\Controllers\Api\V1\CMS;

use App\Http\Controllers\Controller;
use App\Http\Resources\CMS\CmsBlogResource;
use App\Services\CMS\CmsBlogService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CmsBlogController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CmsBlogService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'category_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($b) => (new CmsBlogResource($b))->resolve($request));
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
            'category_id' => 'nullable|exists:cms_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:cms_tags,id',
            'company_id' => 'nullable|exists:companies,id',
        ]);
        $blog = $this->service->create($validated);
        return $this->created(new CmsBlogResource($blog), 'Blog post created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $blog = $this->service->find($id);
        return $this->success(new CmsBlogResource($blog));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:cms_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:cms_tags,id',
        ]);
        $blog = $this->service->update($id, $validated);
        return $this->success(new CmsBlogResource($blog), 'Blog post updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Blog post deleted successfully.');
    }

    public function publish(int $id): JsonResponse
    {
        $blog = $this->service->publish($id);
        return $this->success(new CmsBlogResource($blog), 'Blog post published.');
    }
}
