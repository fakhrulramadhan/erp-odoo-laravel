import HrmSimplePage from './HrmSimplePage';
import { salaryStructuresApi } from '../../api/endpoints';

export default function SalaryStructuresPage() {
    return (
        <HrmSimplePage
            title="Salary Structures"
            api={salaryStructuresApi}
            columns={[
                { key: 'structure_number', label: 'Structure #', fontMedium: true },
                { key: 'name', label: 'Name', fontMedium: true },
                { key: 'code', label: 'Code' },
                { key: 'description', label: 'Description' },
                { key: 'is_active', label: 'Active', type: 'status' },
            ]}
            formFields={[
                { key: 'name', label: 'Name', required: true },
                { key: 'code', label: 'Code', required: true },
                { key: 'description', label: 'Description', type: 'textarea' },
            ]}
        />
    );
}
