import { Warehouse } from 'lucide-react';
import MasterDataPage from '../master-data/MasterDataPage';

const config = {
    title: 'Warehouses',
    moduleName: 'warehouses',
    icon: Warehouse,
    columns: [
        { key: 'code', label: 'Code', sortable: true, width: '120px' },
        { key: 'name', label: 'Name', sortable: true },
        { key: 'address', label: 'Address' },
        { key: 'city', label: 'City', sortable: true, width: '120px' },
        { key: 'phone', label: 'Phone', width: '130px' },
        { key: 'pic', label: 'PIC', render: (row) => row.pic?.name || '-', width: '140px' },
        { key: 'is_main', label: 'Main', type: 'boolean', width: '80px' },
    ],
    filters: [
        { name: 'is_main', label: 'All Warehouses', options: [
            { value: '1', label: 'Main Warehouse' },
            { value: '0', label: 'Other Warehouses' },
        ]},
    ],
    formFields: [
        { name: 'code', label: 'Code', required: true, placeholder: 'e.g. WH-001' },
        { name: 'name', label: 'Name', required: true },
        { name: 'address', label: 'Address', type: 'textarea' },
        { name: 'city', label: 'City' },
        { name: 'phone', label: 'Phone' },
        { name: 'pic_id', label: 'PIC (User ID)', type: 'number', placeholder: 'User ID (optional)' },
        { name: 'is_main', label: 'Main Warehouse', type: 'checkbox' },
    ],
};

export default function WarehousesPage() {
    return <MasterDataPage config={config} />;
}
