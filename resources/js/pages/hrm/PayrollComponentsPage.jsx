import HrmSimplePage from './HrmSimplePage';
import { payrollComponentsApi } from '../../api/endpoints';

export default function PayrollComponentsPage() {
    return (
        <HrmSimplePage
            title="Payroll Components"
            api={payrollComponentsApi}
            columns={[
                { key: 'component_number', label: 'Component #', fontMedium: true },
                { key: 'name', label: 'Name', fontMedium: true },
                { key: 'code', label: 'Code' },
                { key: 'type', label: 'Type', render: item => <span className="capitalize">{item.type}</span> },
                { key: 'calculation_type', label: 'Calc Type', render: item => <span className="capitalize">{item.calculation_type}</span> },
                { key: 'amount', label: 'Amount', type: 'currency' },
                { key: 'percentage', label: 'Percentage', align: 'center', render: item => item.percentage ? `${item.percentage}%` : '-' },
                { key: 'is_active', label: 'Active', type: 'status' },
            ]}
            formFields={[
                { key: 'name', label: 'Name', required: true },
                { key: 'code', label: 'Code', required: true },
                { key: 'type', label: 'Type', type: 'select', required: true, options: [
                    { value: 'earning', label: 'Earning' }, { value: 'deduction', label: 'Deduction' },
                    { value: 'allowance', label: 'Allowance' }, { value: 'bonus', label: 'Bonus' },
                    { value: 'overtime', label: 'Overtime' }, { value: 'tax', label: 'Tax' },
                    { value: 'insurance', label: 'Insurance' }, { value: 'reimbursement', label: 'Reimbursement' },
                ]},
                { key: 'calculation_type', label: 'Calculation Type', type: 'select', required: true, options: [
                    { value: 'fixed', label: 'Fixed' }, { value: 'percentage', label: 'Percentage' },
                    { value: 'formula', label: 'Formula' }, { value: 'manual', label: 'Manual' },
                ]},
                { key: 'amount', label: 'Amount', type: 'number', step: '0.01' },
                { key: 'percentage', label: 'Percentage (%)', type: 'number', step: '0.01' },
                { key: 'description', label: 'Description', type: 'textarea' },
            ]}
        />
    );
}
