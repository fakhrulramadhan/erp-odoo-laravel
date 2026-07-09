import { Users } from 'lucide-react';
import MasterDataPage from '../master-data/MasterDataPage';

const config = {
    title: 'Customers',
    moduleName: 'customers',
    icon: Users,
    columns: [
        { key: 'code', label: 'Code', sortable: true, width: '120px' },
        { key: 'name', label: 'Name', sortable: true },
        { key: 'type', label: 'Type', sortable: true, width: '110px', render: (row) => (
            <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${
                row.type === 'company' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'
            }`}>
                {row.type}
            </span>
        )},
        { key: 'email', label: 'Email' },
        { key: 'phone', label: 'Phone', width: '130px' },
        { key: 'city', label: 'City', width: '120px' },
        { key: 'current_balance', label: 'Balance', type: 'currency', sortable: true, width: '120px' },
    ],
    filters: [
        { name: 'type', label: 'All Types', options: [
            { value: 'individual', label: 'Individual' },
            { value: 'company', label: 'Company' },
        ]},
    ],
    formFields: [
        { name: 'code', label: 'Code', required: true, placeholder: 'e.g. CUS-001' },
        { name: 'name', label: 'Name', required: true },
        { name: 'type', label: 'Type', type: 'select', required: true, options: [
            { value: 'individual', label: 'Individual' },
            { value: 'company', label: 'Company' },
        ]},
        { name: 'email', label: 'Email', type: 'email' },
        { name: 'phone', label: 'Phone' },
        { name: 'billing_address', label: 'Billing Address', type: 'textarea' },
        { name: 'shipping_address', label: 'Shipping Address', type: 'textarea' },
        { name: 'city', label: 'City' },
        { name: 'state', label: 'State' },
        { name: 'zip_code', label: 'Zip Code' },
        { name: 'country', label: 'Country', placeholder: '2-letter country code' },
        { name: 'tax_number', label: 'Tax Number (NPWP)' },
        { name: 'contact_person', label: 'Contact Person' },
        { name: 'payment_term', label: 'Payment Term', placeholder: 'e.g. Net 30' },
        { name: 'credit_limit', label: 'Credit Limit', type: 'number' },
        { name: 'notes', label: 'Notes', type: 'textarea' },
    ],
};

export default function CustomersPage() {
    return <MasterDataPage config={config} />;
}
