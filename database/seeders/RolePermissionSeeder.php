<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── Permissions ──────────────────────────────
        $permissions = [
            // Dashboard
            'view dashboard',

            // Users
            'manage users', 'create users', 'edit users', 'delete users',

            // Companies
            'manage companies', 'create companies', 'edit companies', 'delete companies',

            // Settings
            'manage settings', 'manage currencies', 'manage tax settings',
            'manage numbering sequences', 'manage branches',
            'manage departments', 'manage positions',

            // Master Data
            'manage master data', 'create master data', 'edit master data', 'delete master data',

            // Purchase Orders
            'view purchase orders', 'create purchase orders', 'edit purchase orders',
            'delete purchase orders', 'approve purchase orders', 'cancel purchase orders',

            // Purchase Requisitions
            'view purchase requisitions', 'create purchase requisitions', 'edit purchase requisitions',
            'approve purchase requisitions',

            // Supplier Quotations
            'view supplier quotations', 'create supplier quotations', 'edit supplier quotations',

            // Inventory
            'view inventory', 'manage inventory', 'delete inventory', 'validate inventory',

            // Reordering
            'manage reordering rules',

            // Reports
            'view purchase reports', 'view inventory reports',

            // Audit
            'view audit logs',

            // Notifications
            'manage notifications',

            // Finance & Accounting
            'view finance dashboard', 'manage finance', 'create finance drafts',
            'post finance entries', 'post finance invoices', 'post finance payments',
            'cancel finance documents', 'view finance reports',

            // Manufacturing
            'view manufacturing dashboard',
            'view manufacturing orders', 'create manufacturing orders', 'edit manufacturing orders',
            'delete manufacturing orders', 'confirm manufacturing orders', 'approve manufacturing orders',
            'cancel manufacturing orders', 'start production', 'finish production',
            'view bill of materials', 'create bill of materials', 'edit bill of materials',
            'delete bill of materials', 'approve bill of materials',
            'view work centers', 'create work centers', 'edit work centers', 'delete work centers',
            'view routings', 'create routings', 'edit routings', 'delete routings',
            'view quality checks', 'create quality checks', 'edit quality checks',
            'approve quality checks',
            'view quality control points', 'manage quality control points',
            'view scrap orders', 'create scrap orders', 'process scrap orders',
            'view equipment', 'create equipment', 'edit equipment', 'delete equipment',
            'view maintenance orders', 'create maintenance orders', 'edit maintenance orders',
            'schedule maintenance', 'complete maintenance',
            'view assets', 'create assets', 'edit assets', 'delete assets',
            'activate assets', 'transfer assets', 'dispose assets', 'depreciate assets',
            'view asset categories', 'manage asset categories',
            'view manufacturing reports', 'view production reports',
            'view quality reports', 'view cost reports',
            'view asset reports', 'view maintenance reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ─── Roles ────────────────────────────────────

        // Super Admin — everything
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->syncPermissions(Permission::all());

        // Admin — most things except system-level
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'view dashboard',
            'manage users', 'create users', 'edit users', 'delete users',
            'manage companies', 'create companies', 'edit companies',
            'manage settings', 'manage currencies', 'manage tax settings',
            'manage numbering sequences', 'manage branches',
            'manage departments', 'manage positions',
            'view audit logs',
            'manage notifications',
        ]);

        // Manager
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'view dashboard',
            'manage users', 'create users', 'edit users',
            'view audit logs',
            'manage notifications',
        ]);

        // Staff
        $staff = Role::firstOrCreate(['name' => 'staff']);
        $staff->syncPermissions([
            'view dashboard',
        ]);

        // ── Phase 2: Purchasing & Inventory Roles ──

        // Purchasing Staff
        $purchasingStaff = Role::firstOrCreate(['name' => 'purchasing-staff']);
        $purchasingStaff->syncPermissions([
            'view dashboard',
            'view purchase orders', 'create purchase orders', 'edit purchase orders',
            'view purchase requisitions', 'create purchase requisitions',
            'view supplier quotations', 'create supplier quotations', 'edit supplier quotations',
            'view purchase reports',
        ]);

        // Purchasing Manager
        $purchasingManager = Role::firstOrCreate(['name' => 'purchasing-manager']);
        $purchasingManager->syncPermissions([
            'view dashboard',
            'view purchase orders', 'create purchase orders', 'edit purchase orders',
            'delete purchase orders', 'approve purchase orders', 'cancel purchase orders',
            'view purchase requisitions', 'create purchase requisitions', 'edit purchase requisitions',
            'approve purchase requisitions',
            'view supplier quotations', 'create supplier quotations', 'edit supplier quotations',
            'manage reordering rules',
            'view purchase reports', 'view inventory reports',
        ]);

        // Warehouse Staff
        $warehouseStaff = Role::firstOrCreate(['name' => 'warehouse-staff']);
        $warehouseStaff->syncPermissions([
            'view dashboard',
            'view inventory', 'manage inventory', 'validate inventory',
        ]);

        // Warehouse Manager
        $warehouseManager = Role::firstOrCreate(['name' => 'warehouse-manager']);
        $warehouseManager->syncPermissions([
            'view dashboard',
            'view inventory', 'manage inventory', 'delete inventory', 'validate inventory',
            'manage reordering rules',
            'view inventory reports',
        ]);

        // Inventory Controller
        $inventoryController = Role::firstOrCreate(['name' => 'inventory-controller']);
        $inventoryController->syncPermissions([
            'view dashboard',
            'view inventory', 'manage inventory', 'delete inventory', 'validate inventory',
            'manage reordering rules',
            'view inventory reports',
        ]);

        // Finance Staff
        $finance = Role::firstOrCreate(['name' => 'finance']);
        $finance->syncPermissions([
            'view dashboard',
            'view purchase orders', 'approve purchase orders',
            'view purchase reports', 'view inventory reports',
            'view inventory',
            'view finance dashboard', 'manage finance', 'create finance drafts',
            'view finance reports',
        ]);

        // Accounting Manager
        $accountingManager = Role::firstOrCreate(['name' => 'accounting-manager']);
        $accountingManager->syncPermissions([
            'view dashboard',
            'view finance dashboard', 'manage finance', 'create finance drafts',
            'post finance entries', 'post finance invoices', 'post finance payments',
            'cancel finance documents', 'view finance reports',
        ]);

        // ── Phase 4: Manufacturing Roles ──

        // Production Operator
        $productionOperator = Role::firstOrCreate(['name' => 'production-operator']);
        $productionOperator->syncPermissions([
            'view dashboard',
            'view manufacturing orders',
            'view bill of materials',
            'view work centers',
            'view routings',
            'view equipment',
        ]);

        // Production Supervisor
        $productionSupervisor = Role::firstOrCreate(['name' => 'production-supervisor']);
        $productionSupervisor->syncPermissions([
            'view dashboard',
            'view manufacturing orders', 'create manufacturing orders', 'edit manufacturing orders',
            'confirm manufacturing orders', 'cancel manufacturing orders',
            'start production', 'finish production',
            'view bill of materials', 'create bill of materials', 'edit bill of materials',
            'view work centers', 'create work centers', 'edit work centers',
            'view routings', 'create routings', 'edit routings',
            'view scrap orders', 'create scrap orders',
            'view equipment', 'create equipment', 'edit equipment',
            'view manufacturing dashboard',
            'view production reports', 'view cost reports',
        ]);

        // QC Staff
        $qcStaff = Role::firstOrCreate(['name' => 'qc-staff']);
        $qcStaff->syncPermissions([
            'view dashboard',
            'view quality checks', 'create quality checks', 'edit quality checks',
            'view quality control points',
            'view manufacturing orders',
            'view quality reports',
        ]);

        // QC Manager
        $qcManager = Role::firstOrCreate(['name' => 'qc-manager']);
        $qcManager->syncPermissions([
            'view dashboard',
            'view quality checks', 'create quality checks', 'edit quality checks', 'approve quality checks',
            'view quality control points', 'manage quality control points',
            'view manufacturing orders',
            'view scrap orders', 'process scrap orders',
            'view manufacturing dashboard',
            'view quality reports',
        ]);

        // Maintenance Staff
        $maintenanceStaff = Role::firstOrCreate(['name' => 'maintenance-staff']);
        $maintenanceStaff->syncPermissions([
            'view dashboard',
            'view maintenance orders', 'create maintenance orders', 'edit maintenance orders',
            'complete maintenance',
            'view equipment', 'create equipment', 'edit equipment',
            'view maintenance reports',
        ]);

        // Maintenance Manager
        $maintenanceManager = Role::firstOrCreate(['name' => 'maintenance-manager']);
        $maintenanceManager->syncPermissions([
            'view dashboard',
            'view maintenance orders', 'create maintenance orders', 'edit maintenance orders',
            'schedule maintenance', 'complete maintenance',
            'view equipment', 'create equipment', 'edit equipment', 'delete equipment',
            'view manufacturing dashboard',
            'view maintenance reports',
        ]);

        // Give admin all Phase 2 permissions too
        $admin->givePermissionTo([
            'view purchase orders', 'create purchase orders', 'edit purchase orders',
            'delete purchase orders', 'approve purchase orders', 'cancel purchase orders',
            'view purchase requisitions', 'create purchase requisitions', 'edit purchase requisitions',
            'approve purchase requisitions',
            'view supplier quotations', 'create supplier quotations', 'edit supplier quotations',
            'view inventory', 'manage inventory', 'delete inventory', 'validate inventory',
            'manage reordering rules',
            'view purchase reports', 'view inventory reports',
            'manage master data', 'create master data', 'edit master data', 'delete master data',
        ]);

        // Give admin all Phase 4 manufacturing permissions too
        $admin->givePermissionTo([
            'view manufacturing dashboard',
            'view manufacturing orders', 'create manufacturing orders', 'edit manufacturing orders',
            'delete manufacturing orders', 'confirm manufacturing orders', 'approve manufacturing orders',
            'cancel manufacturing orders', 'start production', 'finish production',
            'view bill of materials', 'create bill of materials', 'edit bill of materials',
            'delete bill of materials', 'approve bill of materials',
            'view work centers', 'create work centers', 'edit work centers', 'delete work centers',
            'view routings', 'create routings', 'edit routings', 'delete routings',
            'view quality checks', 'create quality checks', 'edit quality checks', 'approve quality checks',
            'view quality control points', 'manage quality control points',
            'view scrap orders', 'create scrap orders', 'process scrap orders',
            'view equipment', 'create equipment', 'edit equipment', 'delete equipment',
            'view maintenance orders', 'create maintenance orders', 'edit maintenance orders',
            'schedule maintenance', 'complete maintenance',
            'view assets', 'create assets', 'edit assets', 'delete assets',
            'activate assets', 'transfer assets', 'dispose assets', 'depreciate assets',
            'view asset categories', 'manage asset categories',
            'view manufacturing reports', 'view production reports',
            'view quality reports', 'view cost reports',
            'view asset reports', 'view maintenance reports',
        ]);
    }
}
