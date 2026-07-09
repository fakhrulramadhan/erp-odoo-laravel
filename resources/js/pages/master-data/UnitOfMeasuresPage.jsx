import { Ruler } from 'lucide-react';
import MasterDataPage from '../master-data/MasterDataPage';

const config = {
    title: 'Unit of Measures',
    moduleName: 'unit-of-measures',
    icon: Ruler,
    columns: [
        { key: 'code', label: 'Code', sortable: true, width: '120px' },
        { key: 'name', label: 'Name', sortable: true },
        { key: 'type', label: 'Type', sortable: true, width: '120px', render: (row) => (
            <span className="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700 capitalize">
                {row.type}
            </span>
        )},
        { key: 'description', label: 'Description' },
    ],
    filters: [
        { name: 'type', label: 'All Types', options: [
            { value: 'unit', label: 'Unit' },
            { value: 'weight', label: 'Weight' },
            { value: 'volume', label: 'Volume' },
            { value: 'length', label: 'Length' },
            { value: 'area', label: 'Area' },
        ]},
    ],
    formFields: [
        { name: 'code', label: 'Code', required: true, placeholder: 'e.g. KG, PCS, M' },
        { name: 'name', label: 'Name', required: true },
        { name: 'type', label: 'Type', type: 'select', required: true, options: [
            { value: 'unit', label: 'Unit' },
            { value: 'weight', label: 'Weight' },
            { value: 'volume', label: 'Volume' },
            { value: 'length', label: 'Length' },
            { value: 'area', label: 'Area' },
        ]},
        { name: 'description', label: 'Description', type: 'textarea' },
    ],
};

export default function UnitOfMeasuresPage() {
    return <MasterDataPage config={config} />;
}
