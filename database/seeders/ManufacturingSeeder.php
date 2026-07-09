<?php

namespace Database\Seeders;

use App\Enums\BomStatus;
use App\Enums\ManufacturingOrderStatus;
use App\Enums\MaintenanceStatus;
use App\Models\BillOfMaterial;
use App\Models\BomLine;
use App\Models\Equipment;
use App\Models\MaintenanceOrder;
use App\Models\ManufacturingOrder;
use App\Models\ManufacturingOrderLine;
use App\Models\MaintenanceOrder;
use App\Models\ProductionCost;
use App\Models\QualityCheck;
use App\Models\QualityControlPoint;
use App\Models\Routing;
use App\Models\RoutingOperation;
use App\Models\ScrapOrder;
use App\Models\WorkCenter;
use Illuminate\Database\Seeder;

class ManufacturingSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Work Centers ──────────────────────────
        $wcAssembly = WorkCenter::create([
            'code' => 'WC-00001',
            'name' => 'Assembly Line A',
            'description' => 'Main assembly line for finished goods',
            'capacity_per_hour' => 50,
            'cost_per_hour' => 75.00,
            'efficiency' => 95.00,
            'is_active' => true,
            'created_by' => 1,
        ]);

        $wcMachining = WorkCenter::create([
            'code' => 'WC-00002',
            'name' => 'Machining Center B',
            'description' => 'CNC machining center for precision parts',
            'capacity_per_hour' => 30,
            'cost_per_hour' => 120.00,
            'efficiency' => 90.00,
            'is_active' => true,
            'created_by' => 1,
        ]);

        $wcPackaging = WorkCenter::create([
            'code' => 'WC-00003',
            'name' => 'Packaging Station',
            'description' => 'Final packaging and labeling',
            'capacity_per_hour' => 100,
            'cost_per_hour' => 45.00,
            'efficiency' => 98.00,
            'is_active' => true,
            'created_by' => 1,
        ]);

        // ─── Equipment ─────────────────────────────
        Equipment::create([
            'code' => 'EQ-00001',
            'name' => 'CNC Machine #1',
            'description' => '5-axis CNC milling machine',
            'work_center_id' => $wcMachining->id,
            'serial_number' => 'CNC-2024-001',
            'manufacturer' => 'Haas Automation',
            'model' => 'VF-2SS',
            'purchase_date' => '2023-01-15',
            'warranty_expiry' => '2026-01-15',
            'maintenance_interval_days' => 90,
            'status' => 'active',
            'created_by' => 1,
        ]);

        Equipment::create([
            'code' => 'EQ-00002',
            'name' => 'Assembly Robot Arm',
            'description' => '6-axis robotic assembly arm',
            'work_center_id' => $wcAssembly->id,
            'serial_number' => 'ROB-2024-001',
            'manufacturer' => 'Fanuc',
            'model' => 'M-20iD/25',
            'purchase_date' => '2023-06-01',
            'warranty_expiry' => '2026-06-01',
            'maintenance_interval_days' => 180,
            'status' => 'active',
            'created_by' => 1,
        ]);

        // ─── Routings ──────────────────────────────
        $routing = Routing::create([
            'routing_number' => 'RT-000001',
            'name' => 'Standard Assembly Routing',
            'description' => 'Default routing for assembly products',
            'is_active' => true,
            'created_by' => 1,
        ]);

        RoutingOperation::create([
            'routing_id' => $routing->id,
            'sequence' => 1,
            'name' => 'Machining',
            'work_center_id' => $wcMachining->id,
            'duration_minutes' => 30,
            'setup_time_minutes' => 15,
            'cost_per_hour' => 120.00,
        ]);

        RoutingOperation::create([
            'routing_id' => $routing->id,
            'sequence' => 2,
            'name' => 'Assembly',
            'work_center_id' => $wcAssembly->id,
            'duration_minutes' => 20,
            'setup_time_minutes' => 5,
            'cost_per_hour' => 75.00,
        ]);

        RoutingOperation::create([
            'routing_id' => $routing->id,
            'sequence' => 3,
            'name' => 'Packaging',
            'work_center_id' => $wcPackaging->id,
            'duration_minutes' => 10,
            'setup_time_minutes' => 2,
            'cost_per_hour' => 45.00,
        ]);

        // ─── Quality Control Points ────────────────
        QualityControlPoint::create([
            'name' => 'Visual Inspection',
            'description' => 'Check for visual defects',
            'control_type' => 'visual',
            'method' => 'Visual inspection under bright light',
            'sequence' => 1,
            'is_active' => true,
            'created_by' => 1,
        ]);

        QualityControlPoint::create([
            'name' => 'Dimension Check',
            'description' => 'Measure critical dimensions',
            'control_type' => 'measurement',
            'method' => 'Use calipers to measure dimensions',
            'sequence' => 2,
            'is_active' => true,
            'created_by' => 1,
        ]);

        QualityControlPoint::create([
            'name' => 'Weight Check',
            'description' => 'Verify product weight',
            'control_type' => 'measurement',
            'method' => 'Weigh on calibrated scale',
            'sequence' => 3,
            'is_active' => true,
            'created_by' => 1,
        ]);

        $this->command->info('Manufacturing seed data created successfully.');
    }
}
