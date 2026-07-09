<?php

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\{
    ProductCategoryResource,
    UnitOfMeasureResource,
    ProductResource,
    CustomerResource,
    VendorResource,
    WarehouseResource,
    StockLocationResource,
    PaymentMethodResource,
    BankAccountResource,
    ChartOfAccountResource,
};
use App\Models\{
    ProductCategory,
    UnitOfMeasure,
    Product,
    Customer,
    Vendor,
    Warehouse,
    StockLocation,
    PaymentMethod,
    BankAccount,
    ChartOfAccount,
};
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MasterDataController extends Controller
{
    use ApiResponse;

    // ─── Module Configurations ──────────────────────

    protected function getConfig(string $module): array
    {
        $configs = [

            'product-categories' => [
                'model'       => ProductCategory::class,
                'resource'    => ProductCategoryResource::class,
                'searchable'  => ['code', 'name', 'description'],
                'filterable'  => ['is_active', 'parent_id'],
                'sortable'    => ['code', 'name', 'level', 'created_at'],
                'relations'   => ['parent', 'creator'],
                'store_rules' => [
                    'code'        => 'required|string|max:50|unique:product_categories,code',
                    'name'        => 'required|string|max:255',
                    'description' => 'nullable|string',
                    'parent_id'   => 'nullable|integer|exists:product_categories,id',
                    'is_active'   => 'boolean',
                ],
            ],

            'unit-of-measures' => [
                'model'       => UnitOfMeasure::class,
                'resource'    => UnitOfMeasureResource::class,
                'searchable'  => ['code', 'name', 'description'],
                'filterable'  => ['is_active', 'type'],
                'sortable'    => ['code', 'name', 'type', 'created_at'],
                'relations'   => ['creator'],
                'store_rules' => [
                    'code'        => 'required|string|max:50|unique:unit_of_measures,code',
                    'name'        => 'required|string|max:255',
                    'type'        => 'required|string|in:unit,weight,volume,length,area',
                    'description' => 'nullable|string',
                    'is_active'   => 'boolean',
                ],
            ],

            'products' => [
                'model'       => Product::class,
                'resource'    => ProductResource::class,
                'searchable'  => ['code', 'barcode', 'name', 'description'],
                'filterable'  => ['is_active', 'type', 'category_id', 'uom_id'],
                'sortable'    => ['code', 'name', 'type', 'purchase_price', 'sales_price', 'created_at'],
                'relations'   => ['category', 'uom', 'tax', 'creator'],
                'store_rules' => [
                    'code'           => 'required|string|max:50|unique:products,code',
                    'barcode'        => 'nullable|string|max:100|unique:products,barcode',
                    'name'           => 'required|string|max:255',
                    'description'    => 'nullable|string',
                    'category_id'    => 'nullable|integer|exists:product_categories,id',
                    'uom_id'         => 'nullable|integer|exists:unit_of_measures,id',
                    'type'           => 'required|string|in:stockable,consumable,service',
                    'purchase_price' => 'required|numeric|min:0',
                    'sales_price'    => 'required|numeric|min:0',
                    'tax_id'         => 'nullable|integer|exists:tax_settings,id',
                    'minimum_stock'  => 'nullable|numeric|min:0',
                    'image'          => 'nullable|string|max:255',
                    'weight'         => 'nullable|string|max:50',
                    'volume'         => 'nullable|string|max:50',
                    'is_active'      => 'boolean',
                ],
            ],

            'customers' => [
                'model'       => Customer::class,
                'resource'    => CustomerResource::class,
                'searchable'  => ['code', 'name', 'email', 'phone', 'tax_number'],
                'filterable'  => ['is_active', 'type', 'country'],
                'sortable'    => ['code', 'name', 'type', 'current_balance', 'created_at'],
                'relations'   => ['creator'],
                'store_rules' => [
                    'code'             => 'required|string|max:50|unique:customers,code',
                    'name'             => 'required|string|max:255',
                    'type'             => 'required|string|in:individual,company',
                    'email'            => 'nullable|email|max:255',
                    'phone'            => 'nullable|string|max:30',
                    'billing_address'  => 'nullable|string',
                    'shipping_address' => 'nullable|string',
                    'city'             => 'nullable|string|max:100',
                    'state'            => 'nullable|string|max:100',
                    'zip_code'         => 'nullable|string|max:20',
                    'country'          => 'nullable|string|max:2',
                    'tax_number'       => 'nullable|string|max:50',
                    'contact_person'   => 'nullable|string|max:255',
                    'payment_term'     => 'nullable|string|max:50',
                    'credit_limit'     => 'nullable|numeric|min:0',
                    'notes'            => 'nullable|string',
                    'is_active'        => 'boolean',
                ],
            ],

            'vendors' => [
                'model'       => Vendor::class,
                'resource'    => VendorResource::class,
                'searchable'  => ['code', 'name', 'email', 'phone', 'tax_number'],
                'filterable'  => ['is_active', 'country'],
                'sortable'    => ['code', 'name', 'current_balance', 'created_at'],
                'relations'   => ['creator'],
                'store_rules' => [
                    'code'                => 'required|string|max:50|unique:vendors,code',
                    'name'                => 'required|string|max:255',
                    'email'               => 'nullable|email|max:255',
                    'phone'               => 'nullable|string|max:30',
                    'address'             => 'nullable|string',
                    'city'                => 'nullable|string|max:100',
                    'state'               => 'nullable|string|max:100',
                    'zip_code'            => 'nullable|string|max:20',
                    'country'             => 'nullable|string|max:2',
                    'tax_number'          => 'nullable|string|max:50',
                    'contact_person'      => 'nullable|string|max:255',
                    'payment_term'        => 'nullable|string|max:50',
                    'bank_name'           => 'nullable|string|max:255',
                    'bank_account_number' => 'nullable|string|max:50',
                    'bank_account_name'   => 'nullable|string|max:255',
                    'notes'               => 'nullable|string',
                    'is_active'           => 'boolean',
                ],
            ],

            'warehouses' => [
                'model'       => Warehouse::class,
                'resource'    => WarehouseResource::class,
                'searchable'  => ['code', 'name', 'address', 'city'],
                'filterable'  => ['is_active', 'is_main'],
                'sortable'    => ['code', 'name', 'city', 'created_at'],
                'relations'   => ['pic', 'creator'],
                'store_rules' => [
                    'code'       => 'required|string|max:50|unique:warehouses,code',
                    'name'       => 'required|string|max:255',
                    'address'    => 'nullable|string',
                    'city'       => 'nullable|string|max:100',
                    'phone'      => 'nullable|string|max:30',
                    'pic_id'     => 'nullable|integer|exists:users,id',
                    'is_main'    => 'boolean',
                    'is_active'  => 'boolean',
                ],
            ],

            'stock-locations' => [
                'model'       => StockLocation::class,
                'resource'    => StockLocationResource::class,
                'searchable'  => ['code', 'name', 'description'],
                'filterable'  => ['is_active', 'warehouse_id', 'type'],
                'sortable'    => ['code', 'name', 'type', 'created_at'],
                'relations'   => ['warehouse', 'parent', 'creator'],
                'store_rules' => [
                    'warehouse_id' => 'required|integer|exists:warehouses,id',
                    'code'         => 'required|string|max:50',
                    'name'         => 'required|string|max:255',
                    'type'         => 'required|string|in:internal,supplier,customer,transit,production,scrap',
                    'parent_id'    => 'nullable|integer|exists:stock_locations,id',
                    'description'  => 'nullable|string',
                    'is_active'    => 'boolean',
                ],
            ],

            'bank-accounts' => [
                'model'       => BankAccount::class,
                'resource'    => BankAccountResource::class,
                'searchable'  => ['code', 'name', 'bank_name', 'account_number', 'account_name'],
                'filterable'  => ['is_active', 'type', 'currency_id'],
                'sortable'    => ['code', 'name', 'type', 'current_balance', 'created_at'],
                'relations'   => ['currency', 'creator'],
                'store_rules' => [
                    'code'             => 'required|string|max:50|unique:bank_accounts,code',
                    'name'             => 'required|string|max:255',
                    'type'             => 'required|string|in:cash,bank',
                    'bank_name'        => 'nullable|string|max:255',
                    'account_number'   => 'nullable|string|max:50',
                    'account_name'     => 'nullable|string|max:255',
                    'currency_id'      => 'nullable|integer|exists:currencies,id',
                    'opening_balance'  => 'nullable|numeric',
                    'description'      => 'nullable|string',
                    'is_active'        => 'boolean',
                ],
            ],

            'payment-methods' => [
                'model'       => PaymentMethod::class,
                'resource'    => PaymentMethodResource::class,
                'searchable'  => ['code', 'name', 'description'],
                'filterable'  => ['is_active', 'type', 'bank_account_id'],
                'sortable'    => ['code', 'name', 'type', 'created_at'],
                'relations'   => ['bankAccount', 'creator'],
                'store_rules' => [
                    'code'           => 'required|string|max:50|unique:payment_methods,code',
                    'name'           => 'required|string|max:255',
                    'type'           => 'required|string|in:cash,bank_transfer,qris,debit,credit_card,ewallet',
                    'bank_account_id' => 'nullable|integer|exists:bank_accounts,id',
                    'description'    => 'nullable|string',
                    'is_active'      => 'boolean',
                ],
            ],

            'chart-of-accounts' => [
                'model'       => ChartOfAccount::class,
                'resource'    => ChartOfAccountResource::class,
                'searchable'  => ['code', 'name', 'description'],
                'filterable'  => ['is_active', 'type', 'normal_balance', 'parent_id', 'is_group'],
                'sortable'    => ['code', 'name', 'type', 'level', 'created_at'],
                'relations'   => ['parent', 'creator'],
                'store_rules' => [
                    'code'           => 'required|string|max:50|unique:chart_of_accounts,code',
                    'name'           => 'required|string|max:255',
                    'type'           => 'required|string|in:asset,liability,equity,income,expense',
                    'normal_balance' => 'required|string|in:debit,credit',
                    'parent_id'      => 'nullable|integer|exists:chart_of_accounts,id',
                    'is_group'       => 'boolean',
                    'description'    => 'nullable|string',
                    'is_active'      => 'boolean',
                ],
            ],
        ];

        if (!isset($configs[$module])) {
            abort(404, 'Module not found');
        }

        return $configs[$module];
    }

    // ─── CRUD ───────────────────────────────────────

    public function index(Request $request, string $module): JsonResponse
    {
        $config    = $this->getConfig($module);
        $modelClass = $config['model'];
        $query      = $modelClass::query();

        if (!empty($config['relations'])) {
            $query->with($config['relations']);
        }

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search, $config) {
                foreach ($config['searchable'] as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        // Filters
        foreach ($config['filterable'] as $field) {
            if ($request->has($field) && $request->input($field) !== '') {
                $query->where($field, $request->input($field));
            }
        }

        // Sort
        $sortField = in_array($request->input('sort'), $config['sortable'] ?? [])
            ? $request->input('sort')
            : 'created_at';
        $sortDir = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDir);

        $perPage = min((int) $request->input('per_page', 15), 100);
        $data    = $query->paginate($perPage);

        return $this->paginated($config['resource']::collection($data));
    }

    public function store(Request $request, string $module): JsonResponse
    {
        $config    = $this->getConfig($module);
        $validated = $request->validate($config['store_rules']);

        $validated['created_by'] = $request->user()->id;
        $model                   = $config['model']::create($validated);

        if (!empty($config['relations'])) {
            $model->load($config['relations']);
        }

        return $this->created(new $config['resource']($model));
    }

    public function show(string $module, int $id): JsonResponse
    {
        $config = $this->getConfig($module);
        $query  = $config['model']::query();

        if (!empty($config['relations'])) {
            $query->with($config['relations']);
        }

        return $this->success(new $config['resource']($query->findOrFail($id)));
    }

    public function update(Request $request, string $module, int $id): JsonResponse
    {
        $config = $this->getConfig($module);
        $model  = $config['model']::findOrFail($id);

        // Derive update rules from store rules
        $rules = [];
        foreach ($config['store_rules'] as $field => $rule) {
            if (is_string($rule)) {
                $rule = str_replace('required', 'sometimes|required', $rule);
                $table = $model->getTable();
                $rule  = preg_replace(
                    "/unique:{$field}|unique:{$table},{$field}/",
                    "unique:{$table},{$field},{$id}",
                    $rule
                );
                // Generic unique replacement: unique:table,column → unique:table,column,id
                $rule = preg_replace('/unique:([^,]+),([^,|]+)/', "unique:\\1,\\2,{$id}", $rule);
            }
            $rules[$field] = $rule;
        }

        $validated = $request->validate($rules);
        $validated['updated_by'] = $request->user()->id;

        $model->update($validated);

        if (!empty($config['relations'])) {
            $model->load($config['relations']);
        }

        return $this->success(new $config['resource']($model->fresh()));
    }

    public function destroy(Request $request, string $module, int $id): JsonResponse
    {
        $config = $this->getConfig($module);
        $model  = $config['model']::findOrFail($id);

        $model->update(['deleted_by' => $request->user()->id]);
        $model->delete();

        return $this->deleted();
    }

    // ─── Extra Actions ──────────────────────────────

    public function toggleStatus(Request $request, string $module, int $id): JsonResponse
    {
        $config = $this->getConfig($module);
        $model  = $config['model']::findOrFail($id);

        $model->update([
            'is_active'   => !$model->is_active,
            'updated_by'  => $request->user()->id,
        ]);

        return $this->success(new $config['resource']($model->fresh()));
    }

    public function export(Request $request, string $module)
    {
        $config = $this->getConfig($module);
        $query  = $config['model']::query();

        if (!empty($config['relations'])) {
            $query->with($config['relations']);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search, $config) {
                foreach ($config['searchable'] as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        foreach ($config['filterable'] as $field) {
            if ($request->has($field) && $request->input($field) !== '') {
                $query->where($field, $request->input($field));
            }
        }

        $data     = $query->get();
        $model    = new $config['model']();
        $fields   = array_filter(
            $model->getFillable(),
            fn ($f) => !in_array($f, ['created_by', 'updated_by', 'deleted_by'])
        );
        $filename = Str::slug($module) . '_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $callback = function () use ($data, $fields) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $fields);
            foreach ($data as $row) {
                $csvRow = [];
                foreach ($fields as $field) {
                    $csvRow[] = $row->$field ?? '';
                }
                fputcsv($file, $csvRow);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    public function import(Request $request, string $module): JsonResponse
    {
        $config = $this->getConfig($module);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($handle);

        if (!$header) {
            return $this->error('Invalid CSV file', 422);
        }

        $imported = 0;
        $errors   = [];

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            $data['created_by'] = $request->user()->id;

            $validator = \Validator::make($data, $config['store_rules']);
            if ($validator->fails()) {
                $errors[] = [
                    'row'    => $imported + 2,
                    'errors' => $validator->errors()->toArray(),
                ];
                continue;
            }

            try {
                $config['model']::create($validator->validated());
                $imported++;
            } catch (\Exception $e) {
                $errors[] = [
                    'row'    => $imported + 2,
                    'errors' => ['exception' => $e->getMessage()],
                ];
            }
        }

        fclose($handle);

        return $this->success([
            'imported' => $imported,
            'errors'   => $errors,
        ], "Imported {$imported} records");
    }
}
