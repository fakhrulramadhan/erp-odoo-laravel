import { BookOpen } from 'lucide-react';
import MasterDataPage from '../master-data/MasterDataPage';

const config = {
    title: 'Chart of Accounts',
    moduleName: 'chart-of-accounts',
    icon: BookOpen,
    columns: [
        { key: 'code', label: 'Code', sortable: true, width: '120px' },
        { key: 'name', label: 'Name', sortable: true },
        { key: 'type', label: 'Type', sortable: true, width: '110px', render: (row) => {
            const colors = {
                asset: 'bg-blue-100 text-blue-700',
                liability: 'bg-red-100 text-red-700',
                equity: 'bg-purple-100 text-purple-700',
                income: 'bg-green-100 text-green-700',
                expense: 'bg-orange-100 text-orange-700',
            };
            return (
                <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${colors[row.type] || 'bg-gray-100 text-gray-700'}`}>
                    {row.type}
                </span>
            );
        }},
        { key: 'normal_balance', label: 'Normal Balance', width: '130px', render: (row) => (
            <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${
                row.normal_balance === 'debit' ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700'
            }`}>
                {row.normal_balance}
            </span>
        )},
        { key: 'parent', label: 'Parent', render: (row) => row.parent?.name || '-', width: '140px' },
        { key: 'level', label: 'Level', type: 'number', sortable: true, width: '80px' },
        { key: 'is_group', label: 'Group', type: 'boolean', width: '80px' },
    ],
    filters: [
        { name: 'type', label: 'All Types', options: [
            { value: 'asset', label: 'Asset' },
            { value: 'liability', label: 'Liability' },
            { value: 'equity', label: 'Equity' },
            { value: 'income', label: 'Income' },
            { value: 'expense', label: 'Expense' },
        ]},
        { name: 'normal_balance', label: 'All Balances', options: [
            { value: 'debit', label: 'Debit' },
            { value: 'credit', label: 'Credit' },
        ]},
    ],
    formFields: [
        { name: 'code', label: 'Code', required: true, placeholder: 'e.g. 1000, 1100' },
        { name: 'name', label: 'Name', required: true },
        { name: 'type', label: 'Type', type: 'select', required: true, options: [
            { value: 'asset', label: 'Asset' },
            { value: 'liability', label: 'Liability' },
            { value: 'equity', label: 'Equity' },
            { value: 'income', label: 'Income' },
            { value: 'expense', label: 'Expense' },
        ]},
        { name: 'normal_balance', label: 'Normal Balance', type: 'select', required: true, options: [
            { value: 'debit', label: 'Debit' },
            { value: 'credit', label: 'Credit' },
        ]},
        { name: 'parent_id', label: 'Parent Account ID', type: 'number', placeholder: 'Parent ID (optional)' },
        { name: 'is_group', label: 'Group Account', type: 'checkbox' },
        { name: 'description', label: 'Description', type: 'textarea' },
    ],
};

export default function ChartOfAccountsPage() {
    return <MasterDataPage config={config} />;
}
