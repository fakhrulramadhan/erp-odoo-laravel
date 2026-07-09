import { useEffect, useState } from 'react';
import { Card, Button, Input, Select, Textarea } from '../../components/ui';
import api from '../../api/client';

export default function FinancePaymentsPage() {
    const [payments, setPayments] = useState([]);
    const [form, setForm] = useState({ payment_type: 'customer', payment_date: '', payment_method: 'cash', amount: '', notes: '', allocations: [{ invoice_id: '', amount: '' }] });

    useEffect(() => {
        reload();
    }, []);

    const reload = async () => {
        const res = await api.get('/v1/finance/payments');
        setPayments(res.data.data?.data || []);
    };

    const handleAllocationChange = (index, field, value) => {
        const next = [...form.allocations];
        next[index][field] = value;
        setForm({ ...form, allocations: next });
    };

    const addAllocation = () => setForm({ ...form, allocations: [...form.allocations, { invoice_id: '', amount: '' }] });

    const submit = async (e) => {
        e.preventDefault();
        const payload = { ...form, amount: Number(form.amount || 0), allocations: form.allocations.map((allocation) => ({ ...allocation, amount: Number(allocation.amount || 0) })) };
        const res = await api.post('/v1/finance/payments', payload);
        if (res.data.success) {
            alert('Payment created successfully');
            reload();
        }
    };

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-2xl font-bold text-gray-900">Payments</h1>
                <p className="text-sm text-gray-500">Record customer and vendor payments and allocate them to invoices.</p>
            </div>
            <Card title="New Payment" subtitle="Partial or full settlement">
                <form onSubmit={submit} className="space-y-4">
                    <Select label="Payment Type" value={form.payment_type} onChange={(e) => setForm({ ...form, payment_type: e.target.value })}>
                        <option value="customer">Customer Payment</option>
                        <option value="vendor">Vendor Payment</option>
                    </Select>
                    <Input label="Payment Date" type="date" value={form.payment_date} onChange={(e) => setForm({ ...form, payment_date: e.target.value })} required />
                    <Select label="Payment Method" value={form.payment_method} onChange={(e) => setForm({ ...form, payment_method: e.target.value })}>
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="e_wallet">E-Wallet</option>
                    </Select>
                    <Input label="Amount" type="number" step="0.01" value={form.amount} onChange={(e) => setForm({ ...form, amount: e.target.value })} required />
                    <Textarea label="Notes" value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })} />
                    {form.allocations.map((allocation, index) => (
                        <div key={index} className="grid gap-4 rounded-lg border p-4 md:grid-cols-2">
                            <Input label="Invoice ID" type="number" value={allocation.invoice_id} onChange={(e) => handleAllocationChange(index, 'invoice_id', e.target.value)} required />
                            <Input label="Amount" type="number" step="0.01" value={allocation.amount} onChange={(e) => handleAllocationChange(index, 'amount', e.target.value)} required />
                        </div>
                    ))}
                    <div className="flex gap-3">
                        <Button type="button" variant="secondary" onClick={addAllocation}>Add Allocation</Button>
                        <Button type="submit">Create Payment</Button>
                    </div>
                </form>
            </Card>
            <Card title="Payments" subtitle="Latest payments">
                <div className="space-y-3">
                    {payments.map((payment) => (
                        <div key={payment.id} className="rounded-lg border p-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="font-semibold text-gray-900">{payment.payment_number}</p>
                                    <p className="text-sm text-gray-500">{payment.payment_type} • {payment.status}</p>
                                </div>
                                <p className="text-sm font-semibold text-gray-900">{payment.amount}</p>
                            </div>
                        </div>
                    ))}
                </div>
            </Card>
        </div>
    );
}
