import HrmSimplePage from './HrmSimplePage';
import { employeeDocumentsApi } from '../../api/endpoints';

export default function EmployeeDocumentsPage() {
    return (
        <HrmSimplePage
            title="Employee Documents"
            api={employeeDocumentsApi}
            columns={[
                { key: 'document_number', label: 'Document #', fontMedium: true },
                { key: 'employee', label: 'Employee', fontMedium: true, render: item => item.employee?.full_name || '-' },
                { key: 'document_type', label: 'Type', render: item => <span className="capitalize">{item.document_type?.replace(/_/g, ' ')}</span> },
                { key: 'title', label: 'Title' },
                { key: 'issue_date', label: 'Issue Date', type: 'date' },
                { key: 'expiry_date', label: 'Expiry Date', type: 'date' },
                { key: 'status', label: 'Status', type: 'status' },
            ]}
            formFields={[
                { key: 'employee_id', label: 'Employee ID', required: true },
                { key: 'document_type', label: 'Document Type', type: 'select', required: true, options: [
                    { value: 'ktp', label: 'KTP' }, { value: 'passport', label: 'Passport' },
                    { value: 'sim', label: 'SIM' }, { value: 'npwp', label: 'NPWP' },
                    { value: 'bpjs_kesehatan', label: 'BPJS Kesehatan' }, { value: 'bpjs_ketenagakerjaan', label: 'BPJS Ketenagakerjaan' },
                    { value: 'ijazah', label: 'Ijazah' }, { value: 'sertifikat', label: 'Sertifikat' },
                    { value: 'cv', label: 'CV' }, { value: 'other', label: 'Other' },
                ]},
                { key: 'title', label: 'Title', required: true },
                { key: 'document_number', label: 'Document Number' },
                { key: 'issue_date', label: 'Issue Date', type: 'date' },
                { key: 'expiry_date', label: 'Expiry Date', type: 'date' },
                { key: 'notes', label: 'Notes', type: 'textarea' },
            ]}
        />
    );
}
