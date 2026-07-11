import { Hash } from 'lucide-react';
import { settingsNumberingApi } from '../../api/endpoints';
import GenericSettingsPage from './GenericSettingsPage';

const config = {
    title: 'Numbering Sequences',
    icon: Hash,
    columns: [
        { key: 'name', label: 'Name', sortable: true },
        { key: 'code', label: 'Code', width: '120px', className: 'font-mono text-xs' },
        { key: 'prefix', label: 'Prefix', width: '100px', className: 'font-mono text-xs' },
        { key: 'suffix', label: 'Suffix', width: '100px', className: 'font-mono text-xs' },
        { key: 'padding', label: 'Padding', width: '80px', className: 'text-center' },
        { key: 'next_number', label: 'Next #', width: '100px', className: 'font-mono text-xs', render: (row) => {
            const p = row.prefix || '';
            const s = row.suffix || '';
            const n = String(row.next_number || 1).padStart(row.padding || 5, '0');
            return `${p}${n}${s}`;
        }},
        { key: 'reset_yearly', label: 'Reset Yearly', width: '110px', render: (row) => row.reset_yearly ? (
            <span className="text-green-600 font-medium text-xs">Yes</span>
        ) : <span className="text-gray-400 text-xs">No</span> },
    ],
    formFields: [
        { name: 'name', label: 'Sequence Name', required: true, placeholder: 'Purchase Order Sequence' },
        { name: 'code', label: 'Code', required: true, placeholder: 'PO' },
        { name: 'prefix', label: 'Prefix', placeholder: 'PO-' },
        { name: 'suffix', label: 'Suffix', placeholder: '' },
        { name: 'padding', label: 'Padding (digits)', type: 'number', placeholder: '5' },
        { name: 'reset_yearly', label: 'Reset Yearly', type: 'checkbox' },
    ],
    canCreate: true,
    canEdit: false,
    canDelete: false,
};

export default function SettingsNumberingPage() {
    return <GenericSettingsPage config={config} apiModule={settingsNumberingApi} />;
}
