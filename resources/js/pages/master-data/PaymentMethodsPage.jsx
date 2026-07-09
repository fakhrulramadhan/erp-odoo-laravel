import { CreditCard } from 'lucide-react';
import MasterDataPage from '../master-data/MasterDataPage';

const config = {
    title: 'Payment Methods',
    moduleName: 'payment-methods',
    icon: CreditCard,
    columns: [
        { key: 'code', label: 'Code', sortable: true, width: '120px' },
        { key: 'name', label: 'Name', sortable: true },
        { key: 'type', label: 'Type', sortable: true, width: '130px', render: (row) => {
            const colors = {
                cash: 'bg-green-100 text-green-700',
                bank_transfer: 'bg-blue-100 text-blue-700',
                qris: 'bg-purple-100 text-purple-700',
                debit: 'bg-cyan-100 text-cyan-700',
                credit_card: 'bg-orange-100 text-orange-700',
                ewallet: 'bg-pink-100 text-pink-700',
            };
            return (
                <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${colors[row.type] || 'bg-gray-100 text-gray-700'}`}>
                    {row.type?.replace('_', ' ')}
                </span>
            );
        }},
        { key: 'bank_account', label: 'Bank Account', render: (row) => row.bankAccount?.name || '-', width: '140px' },
        { key: 'description', label: 'Description' },
    ],
    filters: [
        { name: 'type', label: 'All Types', options: [
            { value: 'cash', label: 'Cash' },
            { value: 'bank_transfer', label: 'Bank Transfer' },
            { value: 'qris', label: 'QRIS' },
            { value: 'debit', label: 'Debit' },
            { value: 'credit_card', label: 'Credit Card' },
            { value: 'ewallet', label: 'E-Wallet' },
        ]},
    ],
    formFields: [
        { name: 'code', label: 'Code', required: true, placeholder: 'e.g. CASH, BCA-TF' },
        { name: 'name', label: 'Name', required: true },
        { name: 'type', label: 'Type', type: 'select', required: true, options: [
            { value: 'cash', label: 'Cash' },
            { value: 'bank_transfer', label: 'Bank Transfer' },
            { value: 'qris', label: 'QRIS' },
            { value: 'debit', label: 'Debit' },
            { value: 'credit_card', label: 'Credit Card' },
            { value: 'ewallet', label: 'E-Wallet' },
        ]},
        { name: 'bank_account_id', label: 'Bank Account ID', type: 'number', placeholder: 'Bank Account ID (optional)' },
        { name: 'description', label: 'Description', type: 'textarea' },
    ],
};

export default function PaymentMethodsPage() {
    return <MasterDataPage config={config} />;
}
