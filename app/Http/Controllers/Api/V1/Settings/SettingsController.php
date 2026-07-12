<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Settings\{
    StoreUserRequest,
    UpdateUserRequest,
    StoreCompanyRequest,
    UpdateCompanyRequest
};
use App\Http\Resources\V1\{UserResource, CompanyResource};
use App\Services\User\UserService;
use App\Services\Company\CompanyService;
use App\Services\Setting\SettingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected UserService $userService,
        protected CompanyService $companyService,
        protected SettingService $settingService
    ) {}

    /**
     * Get company_id from authenticated user, with fallback to first company.
     */
    private function getCompanyId(Request $request): int
    {
        $companyId = $request->user()?->company_id;

        if (!$companyId) {
            $companyId = \App\Models\Company::query()->value('id');
        }

        if (!$companyId) {
            abort(422, 'No company found. Please create a company first.');
        }

        return (int) $companyId;
    }

    // ─── Users ──────────────────────────────────────

    public function users(Request $request): JsonResponse
    {
        $filters = $request->except(['page', 'per_page']);
        $perPage = $request->get('per_page', 15);
        $users = $this->userService->list($filters, $perPage);

        return $this->paginated(UserResource::collection($users));
    }

    public function storeUser(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return $this->created(new UserResource($user));
    }

    public function showUser(int $id): JsonResponse
    {
        $user = $this->userService->find($id);

        return $this->success(new UserResource($user));
    }

    public function updateUser(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = $this->userService->update($id, $request->validated());

        return $this->success(new UserResource($user), 'User updated');
    }

    public function destroyUser(int $id): JsonResponse
    {
        $this->userService->delete($id);

        return $this->deleted();
    }

    // ─── Companies ──────────────────────────────────

    public function companies(Request $request): JsonResponse
    {
        $filters = $request->except(['page', 'per_page']);
        $perPage = $request->get('per_page', 15);
        $companies = $this->companyService->list($filters, $perPage);

        return $this->paginated(CompanyResource::collection($companies));
    }

    public function storeCompany(StoreCompanyRequest $request): JsonResponse
    {
        $company = $this->companyService->create($request->validated());

        return $this->created(new CompanyResource($company));
    }

    public function showCompany(int $id): JsonResponse
    {
        $company = $this->companyService->find($id);

        return $this->success(new CompanyResource($company));
    }

    public function updateCompany(UpdateCompanyRequest $request, int $id): JsonResponse
    {
        $company = $this->companyService->update($id, $request->validated());

        return $this->success(new CompanyResource($company), 'Company updated');
    }

    public function destroyCompany(int $id): JsonResponse
    {
        $this->companyService->delete($id);

        return $this->deleted();
    }

    // ─── Currencies ─────────────────────────────────

    public function currencies(Request $request): JsonResponse
    {
        return $this->paginated($this->settingService->currencies($request->get('per_page', 15)));
    }

    public function storeCurrency(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:3|unique:currencies,code',
            'symbol' => 'required|string|max:10',
            'decimal_places' => 'integer|min:0|max:4',
            'exchange_rate' => 'numeric|min:0',
            'is_default' => 'boolean',
        ]);

        return $this->created($this->settingService->createCurrency($validated));
    }

    // ─── Tax Settings ───────────────────────────────

    public function taxSettings(Request $request): JsonResponse
    {
        return $this->paginated(
            $this->settingService->taxSettings($this->getCompanyId($request), $request->get('per_page', 15))
        );
    }

    public function storeTaxSetting(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'in:percentage,fixed',
            'is_inclusive' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $validated['company_id'] = $this->getCompanyId($request);

        return $this->created($this->settingService->createTaxSetting($validated));
    }

    // ─── Numbering Sequences ────────────────────────

    public function numberingSequences(Request $request): JsonResponse
    {
        return $this->paginated(
            $this->settingService->numberingSequences($this->getCompanyId($request), $request->get('per_page', 15))
        );
    }

    public function storeNumberingSequence(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:numbering_sequences,code',
            'prefix' => 'nullable|string|max:20',
            'suffix' => 'nullable|string|max:20',
            'padding' => 'integer|min:1|max:20',
            'reset_yearly' => 'boolean',
        ]);

        $validated['company_id'] = $this->getCompanyId($request);

        return $this->created($this->settingService->createNumberingSequence($validated));
    }

    public function generateNumber(int $id): JsonResponse
    {
        return $this->success([
            'number' => $this->settingService->generateNumber($id),
        ]);
    }

    // ─── Branches, Departments, Positions ───────────

    public function branches(Request $request): JsonResponse
    {
        return $this->paginated(
            $this->settingService->branches($this->getCompanyId($request), $request->get('per_page', 15))
        );
    }

    public function departments(Request $request): JsonResponse
    {
        return $this->paginated(
            $this->settingService->departments($this->getCompanyId($request), $request->get('per_page', 15))
        );
    }

    public function positions(Request $request): JsonResponse
    {
        return $this->paginated(
            $this->settingService->positions($this->getCompanyId($request), $request->get('per_page', 15))
        );
    }
}
