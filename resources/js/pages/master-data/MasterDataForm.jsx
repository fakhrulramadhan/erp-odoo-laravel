import { useState, useEffect } from 'react';
import { Input, Select, Textarea, Button } from '../../components/ui';

export default function MasterDataForm({ fields, initialData, errors, loading, onSubmit, onCancel }) {
    const [formData, setFormData] = useState({});

    useEffect(() => {
        if (initialData) {
            const data = {};
            fields.forEach((f) => {
                data[f.name] = initialData[f.name] ?? '';
            });
            setFormData(data);
        } else {
            const data = {};
            fields.forEach((f) => {
                data[f.name] = f.default ?? '';
            });
            setFormData(data);
        }
    }, [initialData, fields]);

    const handleChange = (name, value) => {
        setFormData((prev) => ({ ...prev, [name]: value }));
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        onSubmit(formData);
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            {fields.map((field) => {
                const value = formData[field.name] ?? '';
                const error = errors?.[field.name]?.[0];

                if (field.type === 'select') {
                    return (
                        <Select
                            key={field.name}
                            label={field.label}
                            value={value}
                            onChange={(e) => handleChange(field.name, e.target.value)}
                            error={error}
                            required={field.required}
                        >
                            <option value="">{field.placeholder || `Select ${field.label}`}</option>
                            {field.options?.map((opt) => (
                                <option key={opt.value} value={opt.value}>{opt.label}</option>
                            ))}
                        </Select>
                    );
                }

                if (field.type === 'textarea') {
                    return (
                        <Textarea
                            key={field.name}
                            label={field.label}
                            value={value}
                            onChange={(e) => handleChange(field.name, e.target.value)}
                            placeholder={field.placeholder}
                            error={error}
                            required={field.required}
                            rows={field.rows || 3}
                        />
                    );
                }

                if (field.type === 'checkbox') {
                    return (
                        <div key={field.name} className="flex items-center gap-2">
                            <input
                                type="checkbox"
                                id={field.name}
                                checked={!!value}
                                onChange={(e) => handleChange(field.name, e.target.checked)}
                                className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            <label htmlFor={field.name} className="text-sm font-medium text-gray-700">
                                {field.label}
                            </label>
                            {error && <p className="text-sm text-red-600">{error}</p>}
                        </div>
                    );
                }

                return (
                    <Input
                        key={field.name}
                        label={field.label}
                        type={field.type || 'text'}
                        value={value}
                        onChange={(e) => handleChange(field.name, e.target.value)}
                        placeholder={field.placeholder}
                        error={error}
                        required={field.required}
                        disabled={field.disabled}
                    />
                );
            })}

            <div className="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <Button type="button" variant="secondary" onClick={onCancel} disabled={loading}>
                    Cancel
                </Button>
                <Button type="submit" disabled={loading}>
                    {loading ? 'Saving...' : initialData ? 'Update' : 'Create'}
                </Button>
            </div>
        </form>
    );
}
