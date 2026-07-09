import { Truck } from 'lucide-react';
import MasterDataPage from '../master-data/MasterDataPage';

const config = {
    title: 'Vendors',
    moduleName: 'vendors',
    icon: Truck,
    columns: [
        { key: 'code', label: 'Code', sortable: true, width: '120px' },
        { key: 'name', label: 'Name', sortable: true },
        { key: 'email', label: 'Email' },
        { key: 'phone', label: 'Phone', width: '130px' },
        { key: 'city', label: 'City', width: '120px' },
        { key: 'contact_person', label: 'Contact', width: '140px' },
        { key: 'current_balance', label: 'Balance', type: 'currency', sortable: true, width: '120px' },
    ],
    formFields: [
        { name: 'code', label: 'Code', required: true, placeholder: 'e.g. VND-001' },
        { name: 'name', label: 'Name', required: true },
        { name: 'email', label: 'Email', type: 'email' },
        { name: 'phone', label: 'Phone' },
        { name: 'address', label: 'Address', type: 'textarea' },
        { name: 'city', label: 'City' },
        { name: 'state', label: 'State' },
        { name: 'zip_code', label: 'Zip Code' },
        { name: 'country', label: 'Country', placeholder: '2-letter code' },
        { name: 'tax_number', label: 'Tax Number (NPWP)' },
        { name: 'contact_person', label: 'Contact Person' },
        { name: 'payment_term', label: 'Payment Term', placeholder: 'e.g. Net 30' },
        { name: 'bank_name', label: 'Bank Name' },
        { name: 'bank_account_number', label: 'Bank Account Number' },
        { name: 'bank_account_name', label: 'Bank Account Name' },
        { name: 'notes', label: 'Notes', type: 'textarea' },
    ],
};

export default function VendorsPage() {
    return <MasterDataPage config={config} />;
}
