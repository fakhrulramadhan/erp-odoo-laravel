import { Coins } from 'lucide-react';
import { settingsCurrenciesApi } from '../../api/endpoints';
import GenericSettingsPage from './GenericSettingsPage';

const config = {
    title: 'Currencies',
    icon: Coins,
    columns: [
        { key: 'code', label: 'Code', sortable: true, width: '80px', className: 'font-mono text-xs font-bold' },
        { key: 'name', label: 'Name', sortable: true },
        { key: 'symbol', label: 'Symbol', width: '80px', className: 'text-center text-lg' },
        { key: 'decimal_places', label: 'Decimals', width: '90px', className: 'text-center' },
        { key: 'exchange_rate', label: 'Exchange Rate', width: '130px', render: (row) => Number(row.exchange_rate || 1).toFixed(4) },
        { key: 'is_default', label: 'Default', width: '90px', render: (row) => row.is_default ? (
            <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-700">Default</span>
        ) : '-' },
    ],
    formFields: [
        { name: 'code', label: 'Currency Code', required: true, placeholder: 'USD, IDR, EUR' },
        { name: 'name', label: 'Currency Name', required: true, placeholder: 'US Dollar' },
        { name: 'symbol', label: 'Symbol', required: true, placeholder: '$, Rp, €' },
        { name: 'decimal_places', label: 'Decimal Places', type: 'number', placeholder: '2' },
        { name: 'exchange_rate', label: 'Exchange Rate', type: 'number', placeholder: '1.0000' },
        { name: 'is_default', label: 'Set as Default', type: 'checkbox' },
    ],
    canCreate: true,
    canEdit: false,
    canDelete: false,
};

export default function SettingsCurrenciesPage() {
    return <GenericSettingsPage config={config} apiModule={settingsCurrenciesApi} />;
}
