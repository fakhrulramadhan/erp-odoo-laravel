import HrmSimplePage from './HrmSimplePage';
import { workSchedulesApi } from '../../api/endpoints';

export default function WorkSchedulesPage() {
    return (
        <HrmSimplePage
            title="Work Schedules"
            api={workSchedulesApi}
            columns={[
                { key: 'schedule_number', label: 'Schedule #', fontMedium: true },
                { key: 'name', label: 'Name', fontMedium: true },
                { key: 'start_time', label: 'Start Time' },
                { key: 'end_time', label: 'End Time' },
                { key: 'work_hours', label: 'Hours', align: 'center' },
                { key: 'is_flexible', label: 'Flexible', align: 'center', render: item => item.is_flexible ? <span className="text-green-600">Yes</span> : <span className="text-gray-400">No</span> },
                { key: 'is_active', label: 'Active', type: 'status' },
            ]}
            formFields={[
                { key: 'name', label: 'Name', required: true },
                { key: 'start_time', label: 'Start Time', type: 'text', required: true },
                { key: 'end_time', label: 'End Time', type: 'text', required: true },
                { key: 'work_hours', label: 'Work Hours', type: 'number', step: '0.5' },
                { key: 'is_flexible', label: 'Flexible Schedule', type: 'checkbox' },
                { key: 'description', label: 'Description', type: 'textarea' },
            ]}
        />
    );
}
