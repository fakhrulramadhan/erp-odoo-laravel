import { MapPin } from 'lucide-react';
import MasterDataPage from '../master-data/MasterDataPage';

const config = {
    title: 'Stock Locations',
    moduleName: 'stock-locations',
    icon: MapPin,
    columns: [
        { key: 'code', label: 'Code', sortable: true, width: '120px' },
        { key: 'name', label: 'Name', sortable: true },
        { key: 'type', label: 'Type', sortable: true, width: '110px', render: (row) => (
            <span className="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700 capitalize">
                {row.type}
            </span>
        )},
        { key: 'warehouse', label: 'Warehouse', render: (row) => row.warehouse?.name || '-', width: '140px' },
        { key: 'parent', label: 'Parent', render: (row) => row.parent?.name || '-', width: '140px' },
        { key: 'description', label: 'Description' },
    ],
    filters: [
        { name: 'type', label: 'All Types', options: [
            { value: 'internal', label: 'Internal' },
            { value: 'supplier', label: 'Supplier' },
            { value: 'customer', label: 'Customer' },
            { value: 'transit', label: 'Transit' },
            { value: 'production', label: 'Production' },
            { value: 'scrap', label: 'Scrap' },
        ]},
    ],
    formFields: [
        { name: 'warehouse_id', label: 'Warehouse ID', type: 'number', required: true, placeholder: 'Warehouse ID' },
        { name: 'code', label: 'Code', required: true, placeholder: 'e.g. LOC-001' },
        { name: 'name', label: 'Name', required: true },
        { name: 'type', label: 'Type', type: 'select', required: true, options: [
            { value: 'internal', label: 'Internal' },
            { value: 'supplier', label: 'Supplier' },
            { value: 'customer', label: 'Customer' },
            { value: 'transit', label: 'Transit' },
            { value: 'production', label: 'Production' },
            { value: 'scrap', label: 'Scrap' },
        ]},
        { name: 'parent_id', label: 'Parent Location ID', type: 'number', placeholder: 'Parent ID (optional)' },
        { name: 'description', label: 'Description', type: 'textarea' },
    ],
};

export default function StockLocationsPage() {
    return <MasterDataPage config={config} />;
}
