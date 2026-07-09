import { Landmark } from 'lucide-react';
import MasterDataPage from '../master-data/MasterDataPage';

const config = {
    title: 'Bank Accounts',
    moduleName: 'bank-accounts',
    icon: Landmark,
    columns: [
        { key: 'code', label: 'Code', sortable: true, width: '120px' },
        { key: 'name', label: 'Name', sortable: true },
        { key: 'type', label: 'Type', sortable: true, width: '100px', render: (row) => (
            <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${
                row.type === 'cash' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'
            }`}>
                {row.type}
            </span>
        )},
        { key: 'bank_name', label: 'Bank Name', width: '140px' },
        { key: 'account_number', label: 'Account No.', width: '140px' },
        { key: 'currency', label: 'Currency', render: (row) => row.currency?.code || '-', width: '90px' },
        { key: 'current_balance', label: 'Balance', type: 'currency', sortable: true, width: '120px' },
    ],
    filters: [
        { name: 'type', label: 'All Types', options: [
            { value: 'cash', label: 'Cash' },
            { value: 'bank', label: 'Bank' },
        ]},
    ],
    formFields: [
        { name: 'code', label: 'Code', required: true, placeholder: 'e.g. BCA-001' },
        { name: 'name', label: 'Name', required: true },
        { name: 'type', label: 'Type', type: 'select', required: true, options: [
            { value: 'cash', label: 'Cash' },
            { value: 'bank', label: 'Bank' },
        ]},
        { name: 'bank_name', label: 'Bank Name', placeholder: 'e.g. Bank Central Asia' },
        { name: 'account_number', label: 'Account Number' },
        { name: 'account_name', label: 'Account Holder Name' },
        { name: 'currency_id', label: 'Currency ID', type: 'number', placeholder: 'Currency ID (optional)' },
        { name: 'opening_balance', label: 'Opening Balance', type: 'number', placeholder: '0.00' },
        { name: 'description', label: 'Description', type: 'textarea' },
    ],
};

export default function BankAccountsPage() {
    return <MasterDataPage config={config} />;
}
