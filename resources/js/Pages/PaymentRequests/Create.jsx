import { Link } from '@inertiajs/react';
import { useState } from 'react';
import ProtectedRoute from '../../Components/ProtectedRoute';
import RoleGate from '../../Components/RoleGate';
import AppLayout from '../../Layouts/AppLayout';
import { api } from '../../lib/api';

export default function Create() {
    const [form, setForm] = useState({ amount_local: '', currency: '' });
    const [errors, setErrors] = useState({});
    const [message, setMessage] = useState('');
    const [created, setCreated] = useState(null);
    const [loading, setLoading] = useState(false);

    async function submit(event) {
        event.preventDefault();
        setLoading(true);
        setErrors({});
        setMessage('');
        setCreated(null);

        try {
            const response = await api.post('/payment-requests', { ...form, currency: form.currency.toUpperCase() });
            setCreated(response.data);
        } catch (error) {
            setErrors(error.response?.data?.errors ?? {});
            setMessage(error.response?.data?.message ?? 'Unable to create payment request.');
        } finally {
            setLoading(false);
        }
    }

    return (
        <ProtectedRoute>
            <AppLayout>
                <RoleGate allow="employee" fallback={<p className="rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-800">Only employees can create payment requests. Backend authorization still applies.</p>}>
                    <div className="max-w-2xl">
                        <h1 className="text-2xl font-semibold text-zinc-950">New payment request</h1>
                        <form onSubmit={submit} className="mt-6 rounded-lg border border-zinc-200 bg-white p-5">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <label className="block">
                                    <span className="text-sm font-medium text-zinc-700">Amount local</span>
                                    <input
                                        className="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        value={form.amount_local}
                                        onChange={(event) => setForm({ ...form, amount_local: event.target.value })}
                                        required
                                    />
                                    {errors.amount_local && <span className="mt-1 block text-xs text-rose-700">{errors.amount_local[0]}</span>}
                                </label>
                                <label className="block">
                                    <span className="text-sm font-medium text-zinc-700">Currency</span>
                                    <input
                                        className="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 uppercase"
                                        maxLength="3"
                                        value={form.currency}
                                        onChange={(event) => setForm({ ...form, currency: event.target.value })}
                                        required
                                    />
                                    {errors.currency && <span className="mt-1 block text-xs text-rose-700">{errors.currency[0]}</span>}
                                </label>
                            </div>
                            {message && <p className="mt-4 rounded-md bg-rose-50 px-3 py-2 text-sm text-rose-700">{message}</p>}
                            <button className="mt-5 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60" disabled={loading}>
                                {loading ? 'Creating...' : 'Create request'}
                            </button>
                        </form>

                        {created && (
                            <div className="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-5">
                                <h2 className="font-semibold text-emerald-950">Request created</h2>
                                <dl className="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                                    <div><dt className="text-emerald-700">Amount EUR</dt><dd className="font-medium">{created.amount_eur}</dd></div>
                                    <div><dt className="text-emerald-700">Rate</dt><dd className="font-medium">{created.eur_to_local_rate}</dd></div>
                                    <div><dt className="text-emerald-700">Source</dt><dd className="font-medium">{created.rate_source}</dd></div>
                                    <div><dt className="text-emerald-700">Fetched at</dt><dd className="font-medium">{created.rate_fetched_at}</dd></div>
                                </dl>
                                <Link className="mt-4 inline-flex rounded-md bg-emerald-700 px-3 py-2 text-sm font-medium text-white" href={`/payment-requests/${created.id}`}>
                                    Open detail
                                </Link>
                            </div>
                        )}
                    </div>
                </RoleGate>
            </AppLayout>
        </ProtectedRoute>
    );
}
