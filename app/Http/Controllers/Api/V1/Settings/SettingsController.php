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

    // ─── Users ──────────────────────────────────────

    public function users(Request $request): JsonResponse
    {
        $users = $this->userService->list($request->all());

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
        $companies = $this->companyService->list($request->all());

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
        return $this->success($this->settingService->currencies());
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
        $request->validate(['company_id' => 'required|exists:companies,id']);

        return $this->success(
            $this->settingService->taxSettings($request->company_id)
        );
    }

    public function storeTaxSetting(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'in:percentage,fixed',
            'is_inclusive' => 'boolean',
            'description' => 'nullable|string',
        ]);

        return $this->created($this->settingService->createTaxSetting($validated));
    }

    // ─── Numbering Sequences ────────────────────────

    public function numberingSequences(Request $request): JsonResponse
    {
        $request->validate(['company_id' => 'required|exists:companies,id']);

        return $this->success(
            $this->settingService->numberingSequences($request->company_id)
        );
    }

    public function storeNumberingSequence(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:numbering_sequences,code',
            'prefix' => 'nullable|string|max:20',
            'suffix' => 'nullable|string|max:20',
            'padding' => 'integer|min:1|max:20',
            'reset_yearly' => 'boolean',
        ]);

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
        $request->validate(['company_id' => 'required|exists:companies,id']);

        return $this->success(
            $this->settingService->branches($request->company_id)
        );
    }

    public function departments(Request $request): JsonResponse
    {
        $request->validate(['company_id' => 'required|exists:companies,id']);

        return $this->success(
            $this->settingService->departments($request->company_id)
        );
    }

    public function positions(Request $request): JsonResponse
    {
        $request->validate(['company_id' => 'required|exists:companies,id']);

        return $this->success(
            $this->settingService->positions($request->company_id)
        );
    }
}
