import { useEffect, useState } from 'react';
import { Card, Button, Input, Select, Textarea } from '../../components/ui';
import api from '../../api/client';

export default function FinanceInvoicesPage() {
    const [invoices, setInvoices] = useState([]);
    const [taxes, setTaxes] = useState([]);
    const [form, setForm] = useState({ invoice_type: 'customer', invoice_date: '', notes: '', items: [{ description: '', quantity: '1', unit_price: '0', discount_rate: '0', tax_id: '' }] });

    useEffect(() => {
        loadData();
    }, []);

    const loadData = async () => {
        const [invoicesRes, taxesRes] = await Promise.all([
            api.get('/v1/finance/invoices'),
            api.get('/v1/finance/taxes'),
        ]);
        setInvoices(invoicesRes.data.data?.data || []);
        setTaxes(taxesRes.data.data || []);
    };

    const handleItemChange = (index, field, value) => {
        const next = [...form.items];
        next[index][field] = value;
        setForm({ ...form, items: next });
    };

    const addItem = () => setForm({ ...form, items: [...form.items, { description: '', quantity: '1', unit_price: '0', discount_rate: '0', tax_id: '' }] });

    const submit = async (e) => {
        e.preventDefault();
        const payload = { ...form, items: form.items.map((item) => ({ ...item, quantity: Number(item.quantity || 0), unit_price: Number(item.unit_price || 0), discount_rate: Number(item.discount_rate || 0) })) };
        const res = await api.post('/v1/finance/invoices', payload);
        if (res.data.success) {
            alert('Invoice created successfully');
            loadData();
        }
    };

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-2xl font-bold text-gray-900">Invoices</h1>
                <p className="text-sm text-gray-500">Create customer invoices and vendor bills with draft and posted statuses.</p>
            </div>
            <Card title="New Invoice" subtitle="Supports draft and posting workflow">
                <form onSubmit={submit} className="space-y-4">
                    <Select label="Invoice Type" value={form.invoice_type} onChange={(e) => setForm({ ...form, invoice_type: e.target.value })}>
                        <option value="customer">Customer Invoice</option>
                        <option value="vendor">Vendor Bill</option>
                    </Select>
                    <Input label="Invoice Date" type="date" value={form.invoice_date} onChange={(e) => setForm({ ...form, invoice_date: e.target.value })} required />
                    <Textarea label="Notes" value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })} />
                    {form.items.map((item, index) => (
                        <div key={index} className="grid gap-4 rounded-lg border p-4 md:grid-cols-4">
                            <Input label="Description" value={item.description} onChange={(e) => handleItemChange(index, 'description', e.target.value)} required />
                            <Input label="Qty" type="number" step="0.01" value={item.quantity} onChange={(e) => handleItemChange(index, 'quantity', e.target.value)} />
                            <Input label="Unit Price" type="number" step="0.01" value={item.unit_price} onChange={(e) => handleItemChange(index, 'unit_price', e.target.value)} />
                            <Select label="Tax" value={item.tax_id} onChange={(e) => handleItemChange(index, 'tax_id', e.target.value)}>
                                <option value="">No tax</option>
                                {taxes.map((tax) => <option key={tax.id} value={tax.id}>{tax.name}</option>)}
                            </Select>
                        </div>
                    ))}
                    <div className="flex gap-3">
                        <Button type="button" variant="secondary" onClick={addItem}>Add Item</Button>
                        <Button type="submit">Create Invoice</Button>
                    </div>
                </form>
            </Card>
            <Card title="Invoices" subtitle="Latest invoices">
                <div className="space-y-3">
                    {invoices.map((invoice) => (
                        <div key={invoice.id} className="rounded-lg border p-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="font-semibold text-gray-900">{invoice.invoice_number}</p>
                                    <p className="text-sm text-gray-500">{invoice.invoice_type} • {invoice.status}</p>
                                </div>
                                <p className="text-sm font-semibold text-gray-900">{invoice.total_amount}</p>
                            </div>
                        </div>
                    ))}
                </div>
            </Card>
        </div>
    );
}
