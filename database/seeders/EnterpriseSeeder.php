<?php

namespace Database\Seeders;

use App\Models\{
    Lead,
    Employee,
    Shift,
    LeaveType,
    PayrollComponent,
    TicketCategory,
    Sla,
    ShippingMethod,
    DocumentFolder,
    Workflow,
    Dashboard,
    NotificationTemplate,
    CompanyGroup,
};
use Illuminate\Database\Seeder;

class EnterpriseSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 1;

        $this->seedCRM($companyId);
        $this->seedHRM($companyId);
        $this->seedAttendance($companyId);
        $this->seedLeave($companyId);
        $this->seedPayroll($companyId);
        $this->seedHelpdesk($companyId);
        $this->seedEcommerce($companyId);
        $this->seedDocuments($companyId);
        $this->seedWorkflow($companyId);
        $this->seedBI($companyId);
        $this->seedNotifications($companyId);
        $this->seedMultiCompany();
    }

    // ─── CRM ────────────────────────────────────────

    private function seedCRM(int $companyId): void
    {
        $leads = [
            [
                'lead_number' => 'LEAD-0001',
                'name' => 'PT Maju Bersama - ERP Implementation',
                'contact_name' => 'Budi Santoso',
                'email' => 'budi@majubersama.co.id',
                'phone' => '+62812345678',
                'company_name' => 'PT Maju Bersama',
                'source' => 'website',
                'status' => 'new',
                'score' => 70,
                'expected_revenue' => 150000000,
                'company_id' => $companyId,
            ],
            [
                'lead_number' => 'LEAD-0002',
                'name' => 'CV Sejahtera - Inventory System',
                'contact_name' => 'Rina Wati',
                'email' => 'rina@sejahtera.co.id',
                'phone' => '+62823456789',
                'company_name' => 'CV Sejahtera',
                'source' => 'referral',
                'status' => 'contacted',
                'score' => 85,
                'expected_revenue' => 75000000,
                'company_id' => $companyId,
            ],
            [
                'lead_number' => 'LEAD-0003',
                'name' => 'PT Teknologi Nusantara - Full Suite',
                'contact_name' => 'Andi Pratama',
                'email' => 'andi@teknusa.co.id',
                'phone' => '+62834567890',
                'company_name' => 'PT Teknologi Nusantara',
                'source' => 'exhibition',
                'status' => 'qualified',
                'score' => 90,
                'expected_revenue' => 300000000,
                'company_id' => $companyId,
            ],
        ];

        foreach ($leads as $lead) {
            Lead::firstOrCreate(['lead_number' => $lead['lead_number']], $lead);
        }
    }

    // ─── HRM ────────────────────────────────────────

    private function seedHRM(int $companyId): void
    {
        // Shifts
        $shifts = [
            ['name' => 'Morning Shift', 'start_time' => '08:00', 'end_time' => '17:00', 'grace_period_minutes' => 15, 'company_id' => $companyId],
            ['name' => 'Night Shift', 'start_time' => '22:00', 'end_time' => '06:00', 'grace_period_minutes' => 10, 'company_id' => $companyId],
            ['name' => 'Flexible', 'start_time' => '09:00', 'end_time' => '18:00', 'grace_period_minutes' => 30, 'company_id' => $companyId],
        ];

        foreach ($shifts as $shift) {
            Shift::firstOrCreate(['name' => $shift['name'], 'company_id' => $companyId], $shift);
        }

        // Employees (link to existing admin user)
        $adminUser = \App\Models\User::where('email', 'admin@demo-erp.com')->first();
        if ($adminUser) {
            Employee::firstOrCreate(['employee_number' => 'EMP-0001'], [
                'user_id' => $adminUser->id,
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'email' => 'admin@demo-erp.com',
                'phone' => '+62812345678',
                'gender' => 'male',
                'employment_type' => 'full_time',
                'status' => 'active',
                'hire_date' => '2025-01-01',
                'basic_salary' => 15000000,
                'company_id' => $companyId,
            ]);
        }
    }

    // ─── Attendance ─────────────────────────────────

    private function seedAttendance(int $companyId): void
    {
        // Attendance seed is data-driven (check-in/check-out records), no master data needed
    }

    // ─── Leave ──────────────────────────────────────

    private function seedLeave(int $companyId): void
    {
        $leaveTypes = [
            ['name' => 'Annual Leave', 'code' => 'AL', 'type' => 'annual', 'default_days_per_year' => 12, 'is_paid' => true, 'requires_approval' => true, 'is_active' => true, 'company_id' => $companyId],
            ['name' => 'Sick Leave', 'code' => 'SL', 'type' => 'sick', 'default_days_per_year' => 12, 'is_paid' => true, 'requires_approval' => false, 'is_active' => true, 'company_id' => $companyId],
            ['name' => 'Personal Leave', 'code' => 'PL', 'type' => 'personal', 'default_days_per_year' => 3, 'is_paid' => false, 'requires_approval' => true, 'is_active' => true, 'company_id' => $companyId],
            ['name' => 'Maternity Leave', 'code' => 'ML', 'type' => 'maternity', 'default_days_per_year' => 90, 'is_paid' => true, 'requires_approval' => true, 'is_active' => true, 'company_id' => $companyId],
            ['name' => 'Paternity Leave', 'code' => 'PTL', 'type' => 'paternity', 'default_days_per_year' => 5, 'is_paid' => true, 'requires_approval' => true, 'is_active' => true, 'company_id' => $companyId],
        ];

        foreach ($leaveTypes as $lt) {
            LeaveType::firstOrCreate(['code' => $lt['code'], 'company_id' => $companyId], $lt);
        }
    }

    // ─── Payroll ────────────────────────────────────

    private function seedPayroll(int $companyId): void
    {
        $components = [
            // Earnings
            ['name' => 'Basic Salary', 'code' => 'BASIC', 'type' => 'earning', 'calculation_type' => 'fixed', 'default_amount' => 0, 'is_taxable' => true, 'is_active' => true, 'company_id' => $companyId],
            ['name' => 'Transport Allowance', 'code' => 'TRANSPORT', 'type' => 'earning', 'calculation_type' => 'fixed', 'default_amount' => 500000, 'is_taxable' => true, 'is_active' => true, 'company_id' => $companyId],
            ['name' => 'Meal Allowance', 'code' => 'MEAL', 'type' => 'earning', 'calculation_type' => 'fixed', 'default_amount' => 500000, 'is_taxable' => false, 'is_active' => true, 'company_id' => $companyId],
            ['name' => 'Overtime', 'code' => 'OT', 'type' => 'earning', 'calculation_type' => 'formula', 'is_taxable' => true, 'is_active' => true, 'company_id' => $companyId],
            ['name' => 'THR (Holiday Bonus)', 'code' => 'THR', 'type' => 'earning', 'calculation_type' => 'fixed', 'is_taxable' => true, 'is_active' => true, 'company_id' => $companyId],

            // Deductions
            ['name' => 'BPJS Kesehatan', 'code' => 'BPJS_KES', 'type' => 'deduction', 'calculation_type' => 'percentage', 'percentage' => 1.0, 'based_on' => 'basic_salary', 'is_taxable' => false, 'is_active' => true, 'company_id' => $companyId],
            ['name' => 'BPJS Ketenagakerjaan', 'code' => 'BPJS_TK', 'type' => 'deduction', 'calculation_type' => 'percentage', 'percentage' => 2.0, 'based_on' => 'basic_salary', 'is_taxable' => false, 'is_active' => true, 'company_id' => $companyId],
            ['name' => 'PPH 21', 'code' => 'PPH21', 'type' => 'deduction', 'calculation_type' => 'formula', 'is_taxable' => false, 'is_active' => true, 'company_id' => $companyId],
            ['name' => 'Late Deduction', 'code' => 'LATE', 'type' => 'deduction', 'calculation_type' => 'formula', 'is_taxable' => false, 'is_active' => true, 'company_id' => $companyId],
        ];

        foreach ($components as $comp) {
            PayrollComponent::firstOrCreate(['code' => $comp['code'], 'company_id' => $companyId], $comp);
        }
    }

    // ─── Helpdesk ──────────────────────────────────

    private function seedHelpdesk(int $companyId): void
    {
        // Categories
        $categories = [
            ['name' => 'Technical Support', 'code' => 'TECH', 'description' => 'Technical issues and bugs', 'is_active' => true, 'company_id' => $companyId],
            ['name' => 'Billing', 'code' => 'BILL', 'description' => 'Billing and payment issues', 'is_active' => true, 'company_id' => $companyId],
            ['name' => 'General Inquiry', 'code' => 'GENERAL', 'description' => 'General questions', 'is_active' => true, 'company_id' => $companyId],
            ['name' => 'Feature Request', 'code' => 'FEATURE', 'description' => 'New feature requests', 'is_active' => true, 'company_id' => $companyId],
        ];

        foreach ($categories as $cat) {
            TicketCategory::firstOrCreate(['code' => $cat['code'], 'company_id' => $companyId], $cat);
        }

        // SLAs
        $slas = [
            ['name' => 'Critical', 'code' => 'SLA-CRIT', 'response_time_hours' => 1, 'resolution_time_hours' => 4, 'priority' => 'critical', 'is_active' => true, 'company_id' => $companyId],
            ['name' => 'High', 'code' => 'SLA-HIGH', 'response_time_hours' => 4, 'resolution_time_hours' => 8, 'priority' => 'high', 'is_active' => true, 'company_id' => $companyId],
            ['name' => 'Medium', 'code' => 'SLA-MED', 'response_time_hours' => 8, 'resolution_time_hours' => 24, 'priority' => 'medium', 'is_active' => true, 'company_id' => $companyId],
            ['name' => 'Low', 'code' => 'SLA-LOW', 'response_time_hours' => 24, 'resolution_time_hours' => 72, 'priority' => 'low', 'is_active' => true, 'company_id' => $companyId],
        ];

        foreach ($slas as $sla) {
            Sla::firstOrCreate(['code' => $sla['code'], 'company_id' => $companyId], $sla);
        }
    }

    // ─── E-Commerce ────────────────────────────────

    private function seedEcommerce(int $companyId): void
    {
        $methods = [
            ['name' => 'Regular Shipping', 'code' => 'REG', 'description' => 'Standard delivery 3-5 days', 'flat_rate' => 15000, 'calculation_type' => 'flat', 'is_active' => true, 'estimated_days_min' => 3, 'estimated_days_max' => 5, 'company_id' => $companyId],
            ['name' => 'Express Shipping', 'code' => 'EXP', 'description' => 'Express delivery 1-2 days', 'flat_rate' => 30000, 'calculation_type' => 'flat', 'is_active' => true, 'estimated_days_min' => 1, 'estimated_days_max' => 2, 'company_id' => $companyId],
            ['name' => 'Same Day', 'code' => 'SAMEDAY', 'description' => 'Same day delivery', 'flat_rate' => 50000, 'calculation_type' => 'flat', 'is_active' => true, 'estimated_days_min' => 0, 'estimated_days_max' => 0, 'company_id' => $companyId],
            ['name' => 'Free Shipping', 'code' => 'FREE', 'description' => 'Free for orders above Rp 500.000', 'flat_rate' => 0, 'calculation_type' => 'flat', 'is_active' => true, 'estimated_days_min' => 5, 'estimated_days_max' => 7, 'company_id' => $companyId],
        ];

        foreach ($methods as $method) {
            ShippingMethod::firstOrCreate(['code' => $method['code'], 'company_id' => $companyId], $method);
        }
    }

    // ─── Documents ─────────────────────────────────

    private function seedDocuments(int $companyId): void
    {
        $folders = [
            ['name' => 'Contracts', 'color' => '#3B82F6', 'company_id' => $companyId],
            ['name' => 'Invoices', 'color' => '#10B981', 'company_id' => $companyId],
            ['name' => 'HR Documents', 'color' => '#8B5CF6', 'company_id' => $companyId],
            ['name' => 'Policies', 'color' => '#F59E0B', 'company_id' => $companyId],
            ['name' => 'Meeting Minutes', 'color' => '#EF4444', 'company_id' => $companyId],
        ];

        foreach ($folders as $folder) {
            DocumentFolder::firstOrCreate(['name' => $folder['name'], 'company_id' => $companyId], $folder);
        }
    }

    // ─── Workflow ──────────────────────────────────

    private function seedWorkflow(int $companyId): void
    {
        $workflows = [
            [
                'name' => 'Purchase Order Approval',
                'code' => 'WF-PO-APPROVE',
                'description' => 'Approval workflow for purchase orders exceeding threshold',
                'trigger_model' => 'PurchaseOrder',
                'trigger_event' => 'submitted',
                'status' => 'active',
                'is_active' => true,
                'company_id' => $companyId,
            ],
            [
                'name' => 'Leave Request Approval',
                'code' => 'WF-LR-APPROVE',
                'description' => 'Approval workflow for employee leave requests',
                'trigger_model' => 'LeaveRequest',
                'trigger_event' => 'submitted',
                'status' => 'active',
                'is_active' => true,
                'company_id' => $companyId,
            ],
            [
                'name' => 'Expense Claim Approval',
                'code' => 'WF-EC-APPROVE',
                'description' => 'Approval workflow for expense claims',
                'trigger_model' => 'ExpenseClaim',
                'trigger_event' => 'submitted',
                'status' => 'active',
                'is_active' => true,
                'company_id' => $companyId,
            ],
        ];

        foreach ($workflows as $wf) {
            Workflow::firstOrCreate(['code' => $wf['code'], 'company_id' => $companyId], $wf);
        }
    }

    // ─── BI Dashboard ─────────────────────────────

    private function seedBI(int $companyId): void
    {
        $adminUser = \App\Models\User::where('email', 'admin@demo-erp.com')->first();
        if (!$adminUser) return;

        $dashboards = [
            [
                'name' => 'Sales Overview',
                'description' => 'Key sales metrics and trends',
                'type' => 'standard',
                'user_id' => $adminUser->id,
                'is_default' => true,
                'is_public' => true,
                'layout' => json_encode(['columns' => 3, 'rows' => 2]),
                'company_id' => $companyId,
            ],
            [
                'name' => 'Financial Summary',
                'description' => 'Revenue, expenses and profit overview',
                'type' => 'standard',
                'user_id' => $adminUser->id,
                'is_default' => false,
                'is_public' => true,
                'layout' => json_encode(['columns' => 2, 'rows' => 3]),
                'company_id' => $companyId,
            ],
            [
                'name' => 'HR Analytics',
                'description' => 'Employee stats, attendance, and leave overview',
                'type' => 'standard',
                'user_id' => $adminUser->id,
                'is_default' => false,
                'is_public' => false,
                'layout' => json_encode(['columns' => 3, 'rows' => 2]),
                'company_id' => $companyId,
            ],
        ];

        foreach ($dashboards as $dash) {
            Dashboard::firstOrCreate(['name' => $dash['name'], 'company_id' => $companyId], $dash);
        }
    }

    // ─── Notifications ────────────────────────────

    private function seedNotifications(int $companyId): void
    {
        $templates = [
            [
                'name' => 'Leave Request Submitted',
                'code' => 'LEAVE_SUBMITTED',
                'channel' => 'email',
                'subject' => 'New Leave Request from {{employee_name}}',
                'body' => 'A new leave request has been submitted by {{employee_name}} for {{days}} days ({{start_date}} to {{end_date}}). Please review and approve.',
                'variables' => json_encode(['employee_name', 'days', 'start_date', 'end_date']),
                'is_active' => true,
                'company_id' => $companyId,
            ],
            [
                'name' => 'Leave Request Approved',
                'code' => 'LEAVE_APPROVED',
                'channel' => 'email',
                'subject' => 'Your Leave Request has been Approved',
                'body' => 'Dear {{employee_name}}, your leave request from {{start_date}} to {{end_date}} has been approved by {{approver_name}}.',
                'variables' => json_encode(['employee_name', 'start_date', 'end_date', 'approver_name']),
                'is_active' => true,
                'company_id' => $companyId,
            ],
            [
                'name' => 'Payroll Processed',
                'code' => 'PAYROLL_PROCESSED',
                'channel' => 'email',
                'subject' => 'Payslip for {{period}} is Ready',
                'body' => 'Dear {{employee_name}}, your payslip for {{period}} has been processed. Net pay: {{net_pay}}. Please review your payslip.',
                'variables' => json_encode(['employee_name', 'period', 'net_pay']),
                'is_active' => true,
                'company_id' => $companyId,
            ],
            [
                'name' => 'Ticket Created',
                'code' => 'TICKET_CREATED',
                'channel' => 'email',
                'subject' => 'Support Ticket #{{ticket_number}} Created',
                'body' => 'Your support ticket #{{ticket_number}} has been created. Subject: {{subject}}. We will respond within {{sla_hours}} hours.',
                'variables' => json_encode(['ticket_number', 'subject', 'sla_hours']),
                'is_active' => true,
                'company_id' => $companyId,
            ],
            [
                'name' => 'Expense Claim Status',
                'code' => 'EXPENSE_STATUS',
                'channel' => 'email',
                'subject' => 'Expense Claim #{{expense_number}} - {{status}}',
                'body' => 'Dear {{employee_name}}, your expense claim #{{expense_number}} for {{total_amount}} has been {{status}}.',
                'variables' => json_encode(['expense_number', 'employee_name', 'total_amount', 'status']),
                'is_active' => true,
                'company_id' => $companyId,
            ],
        ];

        foreach ($templates as $tpl) {
            NotificationTemplate::firstOrCreate(['code' => $tpl['code'], 'company_id' => $companyId], $tpl);
        }
    }

    // ─── Multi-Company ────────────────────────────

    private function seedMultiCompany(): void
    {
        CompanyGroup::firstOrCreate(['code' => 'MAIN-GROUP'], [
            'name' => 'Main Corporate Group',
            'code' => 'MAIN-GROUP',
            'description' => 'Primary corporate entity group',
        ]);
    }
}
