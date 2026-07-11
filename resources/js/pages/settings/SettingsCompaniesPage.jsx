import { Building2 } from 'lucide-react';
import { companiesApi } from '../../api/endpoints';
import GenericSettingsPage from './GenericSettingsPage';

const config = {
    title: 'Companies',
    icon: Building2,
    columns: [
        { key: 'name', label: 'Name', sortable: true },
        { key: 'code', label: 'Code', width: '100px', className: 'font-mono text-xs' },
        { key: 'email', label: 'Email' },
        { key: 'phone', label: 'Phone', width: '140px' },
        { key: 'city', label: 'City', width: '120px' },
        { key: 'country', label: 'Country', width: '120px' },
    ],
    formFields: [
        { name: 'name', label: 'Company Name', required: true, placeholder: 'PT Contoh Sejahtera' },
        { name: 'code', label: 'Code', required: true, placeholder: 'CSE' },
        { name: 'email', label: 'Email', type: 'email', placeholder: 'info@company.com' },
        { name: 'phone', label: 'Phone', placeholder: '+62 21 1234567' },
        { name: 'address', label: 'Address', type: 'textarea', placeholder: 'Street address' },
        { name: 'city', label: 'City', placeholder: 'Jakarta' },
        { name: 'country', label: 'Country', placeholder: 'Indonesia' },
        { name: 'website', label: 'Website', placeholder: 'https://company.com' },
        { name: 'tax_id', label: 'Tax ID (NPWP)', placeholder: '00.000.000.0-000.000' },
    ],
    canCreate: true,
    canEdit: true,
    canDelete: true,
};

export default function SettingsCompaniesPage() {
    return <GenericSettingsPage config={config} apiModule={companiesApi} />;
}
