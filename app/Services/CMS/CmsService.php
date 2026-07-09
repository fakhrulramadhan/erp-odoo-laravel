<?php

namespace App\Services\CMS;

use App\Repositories\CmsMenuRepository;
use App\Repositories\CmsCategoryRepository;
use App\Repositories\CmsTagRepository;
use App\Repositories\CmsBannerRepository;
use App\Repositories\CmsMediaRepository;
use Illuminate\Support\Facades\DB;

class CmsService
{
    public function __construct(
        protected CmsMenuRepository $menuRepo,
        protected CmsCategoryRepository $categoryRepo,
        protected CmsTagRepository $tagRepo,
        protected CmsBannerRepository $bannerRepo,
        protected CmsMediaRepository $mediaRepo,
    ) {}

    // ── Menus ──────────────────────────────────────────────
    public function listMenus(array $f = [], ?int $p = 15) { return $this->menuRepo->getWithRelations($f, $p); }
    public function findMenu(int $id) { return $this->menuRepo->findById($id); }
    public function createMenu(array $d) { return DB::transaction(fn() => $this->menuRepo->create(array_merge($d, ['created_by' => auth()->id()]))); }
    public function updateMenu(int $id, array $d) { return $this->menuRepo->update($id, array_merge($d, ['updated_by' => auth()->id()])); }
    public function deleteMenu(int $id) { return $this->menuRepo->delete($id); }

    // ── Categories ─────────────────────────────────────────
    public function listCategories(array $f = [], ?int $p = 15) { return $this->categoryRepo->getWithRelations($f, $p); }
    public function findCategory(int $id) { return $this->categoryRepo->findById($id); }
    public function createCategory(array $d) { return DB::transaction(fn() => $this->categoryRepo->create(array_merge($d, ['created_by' => auth()->id()]))); }
    public function updateCategory(int $id, array $d) { return $this->categoryRepo->update($id, array_merge($d, ['updated_by' => auth()->id()])); }
    public function deleteCategory(int $id) { return $this->categoryRepo->delete($id); }

    // ── Tags ───────────────────────────────────────────────
    public function listTags(array $f = [], ?int $p = 15) { return $this->tagRepo->getWithRelations($f, $p); }
    public function findTag(int $id) { return $this->tagRepo->findById($id); }
    public function createTag(array $d) { return DB::transaction(fn() => $this->tagRepo->create(array_merge($d, ['created_by' => auth()->id()]))); }
    public function updateTag(int $id, array $d) { return $this->tagRepo->update($id, array_merge($d, ['updated_by' => auth()->id()])); }
    public function deleteTag(int $id) { return $this->tagRepo->delete($id); }

    // ── Banners ────────────────────────────────────────────
    public function listBanners(array $f = [], ?int $p = 15) { return $this->bannerRepo->getWithRelations($f, $p); }
    public function findBanner(int $id) { return $this->bannerRepo->findById($id); }
    public function createBanner(array $d) { return DB::transaction(fn() => $this->bannerRepo->create(array_merge($d, ['created_by' => auth()->id()]))); }
    public function updateBanner(int $id, array $d) { return $this->bannerRepo->update($id, array_merge($d, ['updated_by' => auth()->id()])); }
    public function deleteBanner(int $id) { return $this->bannerRepo->delete($id); }

    // ── Media ──────────────────────────────────────────────
    public function listMedia(array $f = [], ?int $p = 15) { return $this->mediaRepo->getWithRelations($f, $p); }
    public function findMedia(int $id) { return $this->mediaRepo->findById($id); }
    public function createMedia(array $d) { return DB::transaction(fn() => $this->mediaRepo->create(array_merge($d, ['uploaded_by' => auth()->id(), 'created_by' => auth()->id()]))); }
    public function deleteMedia(int $id) { return $this->mediaRepo->delete($id); }
}
