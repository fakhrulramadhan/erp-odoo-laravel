import { Receipt } from 'lucide-react';
import { settingsTaxSettingsApi } from '../../api/endpoints';
import GenericSettingsPage from './GenericSettingsPage';

const config = {
    title: 'Tax Settings',
    icon: Receipt,
    columns: [
        { key: 'name', label: 'Name', sortable: true },
        { key: 'code', label: 'Code', width: '100px', className: 'font-mono text-xs' },
        { key: 'rate', label: 'Rate', width: '100px', render: (row) => {
            const isPercent = row.type === 'percentage' || !row.type;
            return isPercent ? `${row.rate}%` : `Rp ${Number(row.rate).toLocaleString('id-ID')}`;
        }},
        { key: 'type', label: 'Type', width: '120px', render: (row) => (
            <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${
                row.type === 'percentage' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700'
            }`}>
                {row.type || 'percentage'}
            </span>
        )},
        { key: 'is_inclusive', label: 'Inclusive', width: '90px', render: (row) => row.is_inclusive ? (
            <span className="text-green-600 font-medium text-xs">Yes</span>
        ) : <span className="text-gray-400 text-xs">No</span> },
        { key: 'description', label: 'Description' },
    ],
    formFields: [
        { name: 'name', label: 'Tax Name', required: true, placeholder: 'PPN, PPh 23, VAT' },
        { name: 'code', label: 'Code', placeholder: 'PPN-11' },
        { name: 'rate', label: 'Rate', type: 'number', required: true, placeholder: '11' },
        { name: 'type', label: 'Type', type: 'select', options: [
            { value: 'percentage', label: 'Percentage (%)' },
            { value: 'fixed', label: 'Fixed Amount' },
        ]},
        { name: 'is_inclusive', label: 'Tax Inclusive', type: 'checkbox' },
        { name: 'description', label: 'Description', type: 'textarea' },
    ],
    canCreate: true,
    canEdit: false,
    canDelete: false,
};

export default function SettingsTaxesPage() {
    return <GenericSettingsPage config={config} apiModule={settingsTaxSettingsApi} />;
}
