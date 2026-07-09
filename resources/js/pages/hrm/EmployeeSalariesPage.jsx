import HrmSimplePage from './HrmSimplePage';
import { employeeSalariesApi } from '../../api/endpoints';

export default function EmployeeSalariesPage() {
    return (
        <HrmSimplePage
            title="Employee Salaries"
            api={employeeSalariesApi}
            columns={[
                { key: 'salary_number', label: 'Salary #', fontMedium: true },
                { key: 'employee', label: 'Employee', fontMedium: true, render: item => item.employee?.full_name || '-' },
                { key: 'salary_structure', label: 'Structure', render: item => item.salary_structure?.name || '-' },
                { key: 'basic_salary', label: 'Basic', type: 'currency' },
                { key: 'gross_salary', label: 'Gross', type: 'currency' },
                { key: 'net_salary', label: 'Net', type: 'currency' },
                { key: 'effective_date', label: 'Effective', type: 'date' },
                { key: 'end_date', label: 'End Date', type: 'date' },
            ]}
            formFields={[
                { key: 'employee_id', label: 'Employee ID', required: true },
                { key: 'salary_structure_id', label: 'Salary Structure ID' },
                { key: 'basic_salary', label: 'Basic Salary', type: 'number', step: '0.01', required: true },
                { key: 'gross_salary', label: 'Gross Salary', type: 'number', step: '0.01' },
                { key: 'net_salary', label: 'Net Salary', type: 'number', step: '0.01' },
                { key: 'effective_date', label: 'Effective Date', type: 'date', required: true },
                { key: 'end_date', label: 'End Date', type: 'date' },
            ]}
        />
    );
}
