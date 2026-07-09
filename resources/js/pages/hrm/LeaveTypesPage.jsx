import HrmSimplePage from './HrmSimplePage';
import { leaveTypesApi } from '../../api/endpoints';

export default function LeaveTypesPage() {
    return (
        <HrmSimplePage
            title="Leave Types"
            api={leaveTypesApi}
            columns={[
                { key: 'leave_type_number', label: 'Type #', fontMedium: true },
                { key: 'name', label: 'Name', fontMedium: true },
                { key: 'code', label: 'Code' },
                { key: 'type', label: 'Type', render: item => <span className="capitalize">{item.type}</span> },
                { key: 'default_days', label: 'Default Days', align: 'center' },
                { key: 'is_paid', label: 'Paid', align: 'center', render: item => item.is_paid ? <span className="text-green-600">Yes</span> : <span className="text-gray-400">No</span> },
                { key: 'requires_approval', label: 'Approval', align: 'center', render: item => item.requires_approval ? <span className="text-yellow-600">Required</span> : <span className="text-gray-400">Auto</span> },
                { key: 'is_active', label: 'Active', type: 'status' },
            ]}
            formFields={[
                { key: 'name', label: 'Name', required: true },
                { key: 'code', label: 'Code', required: true },
                { key: 'type', label: 'Type', type: 'select', required: true, options: [
                    { value: 'annual', label: 'Annual' }, { value: 'sick', label: 'Sick' },
                    { value: 'maternity', label: 'Maternity' }, { value: 'paternity', label: 'Paternity' },
                    { value: 'unpaid', label: 'Unpaid' }, { value: 'personal', label: 'Personal' },
                    { value: 'marriage', label: 'Marriage' }, { value: 'bereavement', label: 'Bereavement' },
                    { value: 'hajj_umrah', label: 'Hajj/Umrah' }, { value: 'other', label: 'Other' },
                ]},
                { key: 'default_days', label: 'Default Days', type: 'number', min: 0, default: 0 },
                { key: 'max_days', label: 'Max Days', type: 'number', min: 0 },
                { key: 'is_paid', label: 'Paid Leave', type: 'checkbox' },
                { key: 'requires_approval', label: 'Requires Approval', type: 'checkbox' },
                { key: 'description', label: 'Description', type: 'textarea' },
            ]}
        />
    );
}
