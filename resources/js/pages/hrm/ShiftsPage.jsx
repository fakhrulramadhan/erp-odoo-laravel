import HrmSimplePage from './HrmSimplePage';
import { shiftsApi } from '../../api/endpoints';

export default function ShiftsPage() {
    return (
        <HrmSimplePage
            title="Shifts"
            api={shiftsApi}
            columns={[
                { key: 'shift_number', label: 'Shift #', fontMedium: true },
                { key: 'name', label: 'Name', fontMedium: true },
                { key: 'start_time', label: 'Start Time' },
                { key: 'end_time', label: 'End Time' },
                { key: 'grace_period_minutes', label: 'Grace (min)', align: 'center' },
                { key: 'is_active', label: 'Active', type: 'status' },
            ]}
            formFields={[
                { key: 'name', label: 'Name', required: true },
                { key: 'code', label: 'Code' },
                { key: 'start_time', label: 'Start Time', type: 'text', required: true },
                { key: 'end_time', label: 'End Time', type: 'text', required: true },
                { key: 'grace_period_minutes', label: 'Grace Period (minutes)', type: 'number', default: 0 },
                { key: 'break_minutes', label: 'Break (minutes)', type: 'number', default: 60 },
                { key: 'description', label: 'Description', type: 'textarea' },
            ]}
        />
    );
}
