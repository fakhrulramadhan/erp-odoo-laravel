<?php

namespace App\Http\Controllers\Api\V1\CMS;

use App\Http\Controllers\Controller;
use App\Services\CMS\CmsService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CmsController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CmsService $service
    ) {}

    // ── Menus ──────────────────────────────────────────────
    public function indexMenus(Request $request): JsonResponse
    {
        return $this->paginated($this->service->listMenus($request->all(), $request->input('per_page', 15)));
    }
    public function storeMenu(Request $request): JsonResponse
    {
        $v = $request->validate(['name' => 'required|string|max:255', 'url' => 'nullable|string|max:500', 'parent_id' => 'nullable|exists:cms_menus,id', 'sequence' => 'nullable|integer', 'is_active' => 'nullable|boolean']);
        return $this->created($this->service->createMenu($v), 'Menu created.');
    }
    public function showMenu(int $id): JsonResponse { return $this->success($this->service->findMenu($id)); }
    public function updateMenu(Request $request, int $id): JsonResponse
    {
        $v = $request->validate(['name' => 'sometimes|required|string|max:255', 'url' => 'nullable|string|max:500', 'parent_id' => 'nullable|exists:cms_menus,id', 'sequence' => 'nullable|integer', 'is_active' => 'nullable|boolean']);
        return $this->success($this->service->updateMenu($id, $v), 'Menu updated.');
    }
    public function destroyMenu(int $id): JsonResponse { $this->service->deleteMenu($id); return $this->deleted('Menu deleted.'); }

    // ── Categories ─────────────────────────────────────────
    public function indexCategories(Request $request): JsonResponse
    {
        return $this->paginated($this->service->listCategories($request->all(), $request->input('per_page', 15)));
    }
    public function storeCategory(Request $request): JsonResponse
    {
        $v = $request->validate(['name' => 'required|string|max:255', 'slug' => 'nullable|string|max:255', 'description' => 'nullable|string', 'parent_id' => 'nullable|exists:cms_categories,id']);
        return $this->created($this->service->createCategory($v), 'Category created.');
    }
    public function showCategory(int $id): JsonResponse { return $this->success($this->service->findCategory($id)); }
    public function updateCategory(Request $request, int $id): JsonResponse
    {
        $v = $request->validate(['name' => 'sometimes|required|string|max:255', 'slug' => 'nullable|string|max:255', 'description' => 'nullable|string']);
        return $this->success($this->service->updateCategory($id, $v), 'Category updated.');
    }
    public function destroyCategory(int $id): JsonResponse { $this->service->deleteCategory($id); return $this->deleted('Category deleted.'); }

    // ── Tags ───────────────────────────────────────────────
    public function indexTags(Request $request): JsonResponse
    {
        return $this->paginated($this->service->listTags($request->all(), $request->input('per_page', 15)));
    }
    public function storeTag(Request $request): JsonResponse
    {
        $v = $request->validate(['name' => 'required|string|max:255', 'slug' => 'nullable|string|max:255']);
        return $this->created($this->service->createTag($v), 'Tag created.');
    }
    public function showTag(int $id): JsonResponse { return $this->success($this->service->findTag($id)); }
    public function updateTag(Request $request, int $id): JsonResponse
    {
        $v = $request->validate(['name' => 'sometimes|required|string|max:255', 'slug' => 'nullable|string|max:255']);
        return $this->success($this->service->updateTag($id, $v), 'Tag updated.');
    }
    public function destroyTag(int $id): JsonResponse { $this->service->deleteTag($id); return $this->deleted('Tag deleted.'); }

    // ── Banners ────────────────────────────────────────────
    public function indexBanners(Request $request): JsonResponse
    {
        return $this->paginated($this->service->listBanners($request->all(), $request->input('per_page', 15)));
    }
    public function storeBanner(Request $request): JsonResponse
    {
        $v = $request->validate(['title' => 'required|string|max:255', 'image' => 'nullable|string|max:500', 'url' => 'nullable|string|max:500', 'position' => 'nullable|integer', 'is_active' => 'nullable|boolean']);
        return $this->created($this->service->createBanner($v), 'Banner created.');
    }
    public function showBanner(int $id): JsonResponse { return $this->success($this->service->findBanner($id)); }
    public function updateBanner(Request $request, int $id): JsonResponse
    {
        $v = $request->validate(['title' => 'sometimes|required|string|max:255', 'image' => 'nullable|string|max:500', 'url' => 'nullable|string|max:500', 'position' => 'nullable|integer', 'is_active' => 'nullable|boolean']);
        return $this->success($this->service->updateBanner($id, $v), 'Banner updated.');
    }
    public function destroyBanner(int $id): JsonResponse { $this->service->deleteBanner($id); return $this->deleted('Banner deleted.'); }

    // ── Media ──────────────────────────────────────────────
    public function indexMedia(Request $request): JsonResponse
    {
        return $this->paginated($this->service->listMedia($request->all(), $request->input('per_page', 15)));
    }
    public function storeMedia(Request $request): JsonResponse
    {
        $v = $request->validate(['name' => 'required|string|max:255', 'file_path' => 'required|string|max:500', 'file_type' => 'nullable|string|max:50', 'file_size' => 'nullable|integer|min:0']);
        return $this->created($this->service->createMedia($v), 'Media uploaded.');
    }
    public function showMedia(int $id): JsonResponse { return $this->success($this->service->findMedia($id)); }
    public function destroyMedia(int $id): JsonResponse { $this->service->deleteMedia($id); return $this->deleted('Media deleted.'); }
}
