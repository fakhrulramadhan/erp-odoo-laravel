import { useEffect, useState } from 'react';
import { Card, Button, Input, Select, Textarea } from '../../components/ui';
import api from '../../api/client';

export default function FinanceJournalEntriesPage() {
    const [entries, setEntries] = useState([]);
    const [accounts, setAccounts] = useState([]);
    const [journals, setJournals] = useState([]);
    const [form, setForm] = useState({
        journal_id: '',
        entry_date: '',
        description: '',
        lines: [
            { account_id: '', description: '', debit_amount: '', credit_amount: '' },
            { account_id: '', description: '', debit_amount: '', credit_amount: '' },
        ],
    });

    useEffect(() => {
        loadData();
    }, []);

    useEffect(() => {
        if (journals.length && !form.journal_id) {
            setForm((prev) => ({ ...prev, journal_id: String(journals[0].id) }));
        }

        if (accounts.length) {
            setForm((prev) => {
                const nextLines = [...prev.lines];
                const firstAccountId = String(accounts[0].id);
                const secondAccountId = String(accounts[1]?.id || accounts[0].id);

                if (!nextLines[0]?.account_id) nextLines[0] = { ...nextLines[0], account_id: firstAccountId };
                if (!nextLines[1]?.account_id) nextLines[1] = { ...nextLines[1], account_id: secondAccountId };

                return { ...prev, lines: nextLines };
            });
        }

        if (!form.entry_date) {
            setForm((prev) => ({ ...prev, entry_date: new Date().toISOString().split('T')[0] }));
        }
    }, [journals, accounts]);

    const loadData = async () => {
        const [entriesRes, accountsRes, journalsRes] = await Promise.all([
            api.get('/v1/finance/journal-entries'),
            api.get('/v1/finance/accounts'),
            api.get('/v1/finance/journals'),
        ]);
        setEntries(entriesRes.data.data?.data || []);
        setAccounts(accountsRes.data.data || []);
        setJournals(journalsRes.data.data || []);
    };

    const handleLineChange = (index, field, value) => {
        const next = [...form.lines];
        next[index][field] = value;
        setForm({ ...form, lines: next });
    };

    const addLine = () => setForm({ ...form, lines: [...form.lines, { account_id: '', description: '', debit_amount: '', credit_amount: '' }] });

    const submit = async (e) => {
        e.preventDefault();

        if (!form.journal_id) {
            alert('Please select a journal.');
            return;
        }

        if (!form.entry_date) {
            alert('Please enter an entry date.');
            return;
        }

        const payload = {
            ...form,
            lines: form.lines.map((line) => ({
                ...line,
                account_id: line.account_id || '',
                debit_amount: Number(line.debit_amount || 0),
                credit_amount: Number(line.credit_amount || 0),
            })),
        };

        const hasValidLine = payload.lines.some((line) => line.debit_amount > 0 || line.credit_amount > 0);
        const totalDebit = payload.lines.reduce((sum, line) => sum + line.debit_amount, 0);
        const totalCredit = payload.lines.reduce((sum, line) => sum + line.credit_amount, 0);

        if (!hasValidLine) {
            alert('Please enter at least one debit or credit value.');
            return;
        }

        if (Number(totalDebit.toFixed(2)) !== Number(totalCredit.toFixed(2))) {
            alert('Debit and credit must be balanced before posting.');
            return;
        }

        try {
            const res = await api.post('/v1/finance/journal-entries', payload);
            if (res.data.success) {
                alert('Journal entry created successfully');
                loadData();
            }
        } catch (error) {
            const message = error.response?.data?.message || 'Unable to create journal entry.';
            alert(message);
        }
    };

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-2xl font-bold text-gray-900">Journal Entries</h1>
                <p className="text-sm text-gray-500">Create and post balanced manual journal entries.</p>
            </div>
            <Card title="New Journal Entry" subtitle="Debit and credit must balance">
                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-4 md:grid-cols-2">
                        <Select label="Journal" value={form.journal_id} onChange={(e) => setForm({ ...form, journal_id: e.target.value })} required>
                            <option value="">Select journal</option>
                            {journals.map((journal) => <option key={journal.id} value={journal.id}>{journal.name}</option>)}
                        </Select>
                        <Input label="Entry Date" type="date" value={form.entry_date} onChange={(e) => setForm({ ...form, entry_date: e.target.value })} required />
                    </div>
                    <Textarea label="Description" value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} />
                    {form.lines.map((line, index) => (
                        <div key={index} className="grid gap-4 rounded-lg border p-4 md:grid-cols-4">
                            <Select label="Account" value={line.account_id} onChange={(e) => handleLineChange(index, 'account_id', e.target.value)} required>
                                <option value="">Select account</option>
                                {accounts.map((account) => <option key={account.id} value={account.id}>{account.code} - {account.name}</option>)}
                            </Select>
                            <Input label="Description" value={line.description} onChange={(e) => handleLineChange(index, 'description', e.target.value)} />
                            <Input label="Debit" type="number" step="0.01" value={line.debit_amount} onChange={(e) => handleLineChange(index, 'debit_amount', e.target.value)} />
                            <Input label="Credit" type="number" step="0.01" value={line.credit_amount} onChange={(e) => handleLineChange(index, 'credit_amount', e.target.value)} />
                        </div>
                    ))}
                    <div className="flex gap-3">
                        <Button type="button" variant="secondary" onClick={addLine}>Add Line</Button>
                        <Button type="submit">Create Entry</Button>
                    </div>
                </form>
            </Card>
            <Card title="Recent Journal Entries" subtitle="Latest transactions">
                <div className="space-y-3">
                    {entries.map((entry) => (
                        <div key={entry.id} className="rounded-lg border border-gray-200 overflow-hidden">
                            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 p-4">
                                <div>
                                    <p className="font-semibold text-gray-900">{entry.entry_number}</p>
                                    <p className="text-sm text-gray-500">{entry.description || 'No description'}</p>
                                    <p className="text-xs text-gray-400 mt-0.5">
                                        {entry.entry_date}{entry.reference ? ` • Ref: ${entry.reference}` : ''}{entry.journal?.name ? ` • ${entry.journal.name}` : ''}
                                    </p>
                                </div>
                                <div className="flex items-center gap-3">
                                    <span className={`rounded-full px-3 py-1 text-xs font-medium ${entry.status === 'posted' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'}`}>
                                        {entry.status}
                                    </span>
                                    {entry.status === 'draft' && (
                                        <Button
                                            variant="secondary"
                                            onClick={async () => {
                                                try {
                                                    await api.post(`/v1/finance/journal-entries/${entry.id}/post`);
                                                    loadData();
                                                } catch (error) {
                                                    alert(error.response?.data?.message || 'Unable to post journal entry.');
                                                }
                                            }}
                                        >
                                            Post
                                        </Button>
                                    )}
                                </div>
                            </div>
                            {(entry.lines || []).length > 0 && (
                                <div className="overflow-x-auto border-t border-gray-100">
                                    <table className="w-full text-sm">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-4 py-2 text-left font-medium text-gray-600">Account</th>
                                                <th className="px-4 py-2 text-left font-medium text-gray-600">Description</th>
                                                <th className="px-4 py-2 text-right font-medium text-gray-600">Debit</th>
                                                <th className="px-4 py-2 text-right font-medium text-gray-600">Credit</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-100">
                                            {entry.lines.map((line) => (
                                                <tr key={line.id} className="hover:bg-gray-50">
                                                    <td className="px-4 py-2 text-gray-800">
                                                        {line.account ? `${line.account.code} - ${line.account.name}` : `Account #${line.account_id}`}
                                                    </td>
                                                    <td className="px-4 py-2 text-gray-600">{line.description || '-'}</td>
                                                    <td className="px-4 py-2 text-right font-medium text-gray-900">
                                                        {Number(line.debit_amount) > 0 ? Number(line.debit_amount).toLocaleString('id-ID', { minimumFractionDigits: 2 }) : '-'}
                                                    </td>
                                                    <td className="px-4 py-2 text-right font-medium text-gray-900">
                                                        {Number(line.credit_amount) > 0 ? Number(line.credit_amount).toLocaleString('id-ID', { minimumFractionDigits: 2 }) : '-'}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                        <tfoot className="bg-gray-50">
                                            <tr>
                                                <td colSpan={2} className="px-4 py-2 text-right font-medium text-gray-600">Total</td>
                                                <td className="px-4 py-2 text-right font-semibold text-gray-900">
                                                    {entry.lines.reduce((sum, l) => sum + Number(l.debit_amount || 0), 0).toLocaleString('id-ID', { minimumFractionDigits: 2 })}
                                                </td>
                                                <td className="px-4 py-2 text-right font-semibold text-gray-900">
                                                    {entry.lines.reduce((sum, l) => sum + Number(l.credit_amount || 0), 0).toLocaleString('id-ID', { minimumFractionDigits: 2 })}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            )}
                        </div>
                    ))}
                    {entries.length === 0 && <p className="py-8 text-center text-gray-400">No journal entries yet.</p>}
                </div>
            </Card>
        </div>
    );
}
