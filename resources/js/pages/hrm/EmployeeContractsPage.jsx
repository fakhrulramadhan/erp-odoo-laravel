import HrmSimplePage from './HrmSimplePage';
import { employeeContractsApi } from '../../api/endpoints';

export default function EmployeeContractsPage() {
    return (
        <HrmSimplePage
            title="Employee Contracts"
            api={employeeContractsApi}
            columns={[
                { key: 'contract_number', label: 'Contract #', fontMedium: true },
                { key: 'employee', label: 'Employee', fontMedium: true, render: item => item.employee?.full_name || '-' },
                { key: 'contract_type', label: 'Type', render: item => <span className="capitalize">{item.contract_type?.replace(/_/g, ' ')}</span> },
                { key: 'start_date', label: 'Start Date', type: 'date' },
                { key: 'end_date', label: 'End Date', type: 'date' },
                { key: 'salary', label: 'Salary', type: 'currency' },
                { key: 'status', label: 'Status', type: 'status' },
            ]}
            formFields={[
                { key: 'employee_id', label: 'Employee ID', required: true },
                { key: 'contract_type', label: 'Contract Type', type: 'select', required: true, options: [
                    { value: 'permanent', label: 'Permanent' }, { value: 'contract', label: 'Contract' },
                    { value: 'probation', label: 'Probation' }, { value: 'freelance', label: 'Freelance' },
                    { value: 'internship', label: 'Internship' },
                ]},
                { key: 'start_date', label: 'Start Date', type: 'date', required: true },
                { key: 'end_date', label: 'End Date', type: 'date' },
                { key: 'salary', label: 'Salary', type: 'number', step: '0.01' },
                { key: 'notes', label: 'Notes', type: 'textarea' },
            ]}
            filterOptions={{ key: 'status', options: [
                { value: 'active', label: 'Active' }, { value: 'expired', label: 'Expired' },
                { value: 'terminated', label: 'Terminated' }, { value: 'renewed', label: 'Renewed' },
            ]}}
        />
    );
}
