import { Users } from 'lucide-react';
import { usersApi } from '../../api/endpoints';
import GenericSettingsPage from './GenericSettingsPage';

const config = {
    title: 'Users',
    icon: Users,
    columns: [
        { key: 'name', label: 'Name', sortable: true },
        { key: 'email', label: 'Email', sortable: true },
        { key: 'role', label: 'Role', width: '130px', render: (row) => {
            const colors = {
                admin: 'bg-red-100 text-red-700',
                manager: 'bg-blue-100 text-blue-700',
                user: 'bg-green-100 text-green-700',
            };
            return (
                <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${colors[row.role] || 'bg-gray-100 text-gray-700'}`}>
                    {row.role || 'user'}
                </span>
            );
        }},
        { key: 'is_active', label: 'Status', width: '100px', render: (row) => (
            <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${
                row.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'
            }`}>
                {row.is_active ? 'Active' : 'Inactive'}
            </span>
        )},
        { key: 'created_at', label: 'Created', width: '130px', render: (row) => row.created_at ? new Date(row.created_at).toLocaleDateString('id-ID') : '-' },
    ],
    formFields: [
        { name: 'name', label: 'Full Name', required: true, placeholder: 'John Doe' },
        { name: 'email', label: 'Email', type: 'email', required: true, placeholder: 'john@example.com' },
        { name: 'password', label: 'Password', type: 'password', required: true, placeholder: 'Min 8 characters' },
        { name: 'password_confirmation', label: 'Confirm Password', type: 'password', required: true },
        { name: 'role', label: 'Role', type: 'select', required: true, options: [
            { value: 'admin', label: 'Admin' },
            { value: 'manager', label: 'Manager' },
            { value: 'user', label: 'User' },
        ]},
    ],
    canCreate: true,
    canEdit: true,
    canDelete: true,
};

export default function SettingsUsersPage() {
    return <GenericSettingsPage config={config} apiModule={usersApi} />;
}
