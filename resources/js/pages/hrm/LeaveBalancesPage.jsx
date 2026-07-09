import HrmSimplePage from './HrmSimplePage';
import { leaveBalancesApi } from '../../api/endpoints';
import { formatDate } from '../../components/ui/StatusBadge';

export default function LeaveBalancesPage() {
    return (
        <HrmSimplePage
            title="Leave Balances"
            api={leaveBalancesApi}
            columns={[
                { key: 'leave_balance_number', label: 'Balance #', fontMedium: true },
                { key: 'employee', label: 'Employee', fontMedium: true, render: item => item.employee?.full_name || '-' },
                { key: 'leave_type', label: 'Leave Type', render: item => item.leave_type?.name || '-' },
                { key: 'year', label: 'Year', align: 'center' },
                { key: 'total_days', label: 'Total', align: 'center' },
                { key: 'used_days', label: 'Used', align: 'center' },
                { key: 'pending_days', label: 'Pending', align: 'center' },
                { key: 'remaining_days', label: 'Remaining', align: 'center', render: item => <span className="font-medium text-indigo-600">{item.remaining_days}</span> },
            ]}
            formFields={[
                { key: 'employee_id', label: 'Employee ID', required: true },
                { key: 'leave_type_id', label: 'Leave Type ID', required: true },
                { key: 'year', label: 'Year', type: 'number', required: true, default: new Date().getFullYear() },
                { key: 'total_days', label: 'Total Days', type: 'number', required: true, min: 0 },
                { key: 'used_days', label: 'Used Days', type: 'number', default: 0 },
                { key: 'pending_days', label: 'Pending Days', type: 'number', default: 0 },
            ]}
        />
    );
}
