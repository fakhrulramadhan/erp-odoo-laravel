import { Boxes } from 'lucide-react';
import MasterDataPage from '../master-data/MasterDataPage';

const config = {
    title: 'Products',
    moduleName: 'products',
    icon: Boxes,
    columns: [
        { key: 'code', label: 'Code', sortable: true, width: '120px' },
        { key: 'name', label: 'Name', sortable: true },
        { key: 'type', label: 'Type', sortable: true, width: '120px', render: (row) => (
            <span className="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-700 capitalize">
                {row.type}
            </span>
        )},
        { key: 'category', label: 'Category', render: (row) => row.category?.name || '-' },
        { key: 'uom', label: 'UOM', render: (row) => row.uom?.code || '-', width: '80px' },
        { key: 'purchase_price', label: 'Purchase Price', type: 'currency', sortable: true, width: '130px' },
        { key: 'sales_price', label: 'Sales Price', type: 'currency', sortable: true, width: '130px' },
    ],
    filters: [
        { name: 'type', label: 'All Types', options: [
            { value: 'stockable', label: 'Stockable' },
            { value: 'consumable', label: 'Consumable' },
            { value: 'service', label: 'Service' },
        ]},
    ],
    formFields: [
        { name: 'code', label: 'Code', required: true, placeholder: 'e.g. PRD-001' },
        { name: 'name', label: 'Name', required: true },
        { name: 'barcode', label: 'Barcode', placeholder: 'Barcode (optional)' },
        { name: 'type', label: 'Type', type: 'select', required: true, options: [
            { value: 'stockable', label: 'Stockable' },
            { value: 'consumable', label: 'Consumable' },
            { value: 'service', label: 'Service' },
        ]},
        { name: 'category_id', label: 'Category ID', type: 'number', placeholder: 'Category ID (optional)' },
        { name: 'uom_id', label: 'UOM ID', type: 'number', placeholder: 'Unit of Measure ID (optional)' },
        { name: 'purchase_price', label: 'Purchase Price', type: 'number', required: true, placeholder: '0.00' },
        { name: 'sales_price', label: 'Sales Price', type: 'number', required: true, placeholder: '0.00' },
        { name: 'tax_id', label: 'Tax ID', type: 'number', placeholder: 'Tax ID (optional)' },
        { name: 'minimum_stock', label: 'Minimum Stock', type: 'number', placeholder: '0' },
        { name: 'description', label: 'Description', type: 'textarea' },
    ],
};

export default function ProductsPage() {
    return <MasterDataPage config={config} />;
}
