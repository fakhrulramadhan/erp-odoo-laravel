import { Building } from 'lucide-react';
import { settingsDepartmentsApi } from '../../api/endpoints';
import GenericSettingsPage from './GenericSettingsPage';

const config = {
    title: 'Departments',
    icon: Building,
    columns: [
        { key: 'name', label: 'Name', sortable: true },
        { key: 'code', label: 'Code', width: '120px', className: 'font-mono text-xs' },
        { key: 'description', label: 'Description' },
        { key: 'manager', label: 'Manager', width: '150px', render: (row) => row.manager?.name || '-' },
        { key: 'is_active', label: 'Status', width: '100px', render: (row) => (
            <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${
                row.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'
            }`}>
                {row.is_active ? 'Active' : 'Inactive'}
            </span>
        )},
    ],
    canCreate: false,
    canEdit: false,
    canDelete: false,
};

export default function SettingsDepartmentsPage() {
    return <GenericSettingsPage config={config} apiModule={settingsDepartmentsApi} />;
}
