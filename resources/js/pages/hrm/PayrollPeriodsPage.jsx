import HrmSimplePage from './HrmSimplePage';
import { payrollPeriodsApi } from '../../api/endpoints';

export default function PayrollPeriodsPage() {
    return (
        <HrmSimplePage
            title="Payroll Periods"
            api={payrollPeriodsApi}
            columns={[
                { key: 'period_number', label: 'Period #', fontMedium: true },
                { key: 'name', label: 'Name', fontMedium: true },
                { key: 'start_date', label: 'Start Date', type: 'date' },
                { key: 'end_date', label: 'End Date', type: 'date' },
                { key: 'status', label: 'Status', type: 'status' },
            ]}
            formFields={[
                { key: 'name', label: 'Name', required: true },
                { key: 'start_date', label: 'Start Date', type: 'date', required: true },
                { key: 'end_date', label: 'End Date', type: 'date', required: true },
            ]}
            filterOptions={{ key: 'status', options: [
                { value: 'draft', label: 'Draft' }, { value: 'open', label: 'Open' },
                { value: 'closed', label: 'Closed' }, { value: 'locked', label: 'Locked' },
            ]}}
        />
    );
}
