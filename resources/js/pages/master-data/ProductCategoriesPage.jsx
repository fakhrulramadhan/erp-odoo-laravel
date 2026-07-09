import { Package } from 'lucide-react';
import MasterDataPage from '../master-data/MasterDataPage';

const config = {
    title: 'Product Categories',
    moduleName: 'product-categories',
    icon: Package,
    columns: [
        { key: 'code', label: 'Code', sortable: true, width: '120px' },
        { key: 'name', label: 'Name', sortable: true },
        { key: 'description', label: 'Description' },
        { key: 'parent', label: 'Parent', render: (row) => row.parent?.name || '-' },
        { key: 'level', label: 'Level', type: 'number', sortable: true, width: '80px' },
    ],
    formFields: [
        { name: 'code', label: 'Code', required: true, placeholder: 'e.g. CAT-001' },
        { name: 'name', label: 'Name', required: true },
        { name: 'description', label: 'Description', type: 'textarea' },
        { name: 'parent_id', label: 'Parent Category', type: 'number', placeholder: 'Parent ID (optional)' },
    ],
};

export default function ProductCategoriesPage() {
    return <MasterDataPage config={config} />;
}
