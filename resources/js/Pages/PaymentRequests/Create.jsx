import { Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import ProtectedRoute from '../../Components/ProtectedRoute';
import RoleGate from '../../Components/RoleGate';
import AppLayout from '../../Layouts/AppLayout';
import { api } from '../../lib/api';
import { getStoredUser } from '../../lib/auth';
import { formatDateTime, formatMoneyInput, normalizeAmountForApi } from '../../lib/formatters';

export default function Create() {
    const [form, setForm] = useState({ amount_local: '', currency: '' });
    const [currencies, setCurrencies] = useState([]);
    const [currenciesLoading, setCurrenciesLoading] = useState(true);
    const [errors, setErrors] = useState({});
    const [message, setMessage] = useState('');
    const [created, setCreated] = useState(null);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        let active = true;
        const userCurrency = getStoredUser()?.currency;

        api.get('/currencies')
            .then((response) => {
                if (!active) return;

                const options = response.data.data ?? [];
                const defaultCurrency = options.some((currency) => currency.code === userCurrency)
                    ? userCurrency
                    : options[0]?.code ?? '';

                setCurrencies(options);
                setForm((current) => ({
                    ...current,
                    currency: current.currency || defaultCurrency,
                }));
            })
            .catch((error) => {
                if (active) {
                    setMessage(error.response?.data?.message ?? 'Unable to load currencies.');
                }
            })
            .finally(() => {
                if (active) setCurrenciesLoading(false);
            });

        return () => {
            active = false;
        };
    }, []);

    async function submit(event) {
        event.preventDefault();
        setLoading(true);
        setErrors({});
        setMessage('');
        setCreated(null);

        try {
            const response = await api.post('/payment-requests', {
                amount_local: normalizeAmountForApi(form.amount_local),
                currency: form.currency,
            });
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
                            <div className="grid gap-4 md:grid-cols-[minmax(0,1fr)_280px] md:items-start">
                                <label className="flex min-h-[92px] flex-col">
                                    <span className="text-sm font-medium text-zinc-700">Amount local</span>
                                    <input
                                        className="mt-1 h-11 w-full rounded-md border border-zinc-300 px-3 text-sm"
                                        type="text"
                                        inputMode="decimal"
                                        value={form.amount_local}
                                        onChange={(event) => setForm({ ...form, amount_local: formatMoneyInput(event.target.value) })}
                                        placeholder="110.00"
                                        required
                                    />
                                    {errors.amount_local && <span className="mt-1 block text-xs text-rose-700">{errors.amount_local[0]}</span>}
                                </label>
                                <label className="flex min-h-[92px] flex-col">
                                    <span className="text-sm font-medium text-zinc-700">Currency</span>
                                    <select
                                        className="mt-1 h-11 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm"
                                        value={form.currency}
                                        onChange={(event) => setForm({ ...form, currency: event.target.value })}
                                        disabled={currenciesLoading || currencies.length === 0}
                                        required
                                    >
                                        {currenciesLoading && <option value="">Loading...</option>}
                                        {!currenciesLoading && currencies.length === 0 && <option value="">No currencies available</option>}
                                        {currencies.map((currency) => (
                                            <option key={currency.code} value={currency.code}>
                                                {currency.code} - {currency.name}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.currency && <span className="mt-1 block text-xs text-rose-700">{errors.currency[0]}</span>}
                                </label>
                            </div>
                            {message && <p className="mt-4 rounded-md bg-rose-50 px-3 py-2 text-sm text-rose-700">{message}</p>}
                            <button className="mt-2 h-10 rounded-md bg-blue-600 px-4 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60" disabled={loading || currenciesLoading || currencies.length === 0}>
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
                                    <div><dt className="text-emerald-700">Fetched at</dt><dd className="font-medium">{formatDateTime(created.rate_fetched_at)}</dd></div>
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
