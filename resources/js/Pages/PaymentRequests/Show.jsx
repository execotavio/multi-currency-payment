import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import ProtectedRoute from '../../Components/ProtectedRoute';
import RoleGate from '../../Components/RoleGate';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { api } from '../../lib/api';
import { formatDateTime } from '../../lib/formatters';

export default function Show() {
    const { paymentRequestId } = usePage().props;
    const [request, setRequest] = useState(null);
    const [message, setMessage] = useState('');
    const [loading, setLoading] = useState(true);
    const [acting, setActing] = useState(false);

    function load() {
        setLoading(true);
        setMessage('');

        api.get(`/payment-requests/${paymentRequestId}`)
            .then((response) => setRequest(response.data))
            .catch((error) => setMessage(error.response?.data?.message ?? 'Unable to load payment request.'))
            .finally(() => setLoading(false));
    }

    useEffect(() => {
        load();
    }, [paymentRequestId]);

    async function transition(action) {
        setActing(true);
        setMessage('');

        try {
            const response = await api.post(`/payment-requests/${paymentRequestId}/${action}`);
            setRequest(response.data);
        } catch (error) {
            setMessage(error.response?.data?.message ?? `Unable to ${action} payment request.`);
        } finally {
            setActing(false);
        }
    }

    return (
        <ProtectedRoute>
            <AppLayout>
                {loading && <p className="text-sm text-zinc-500">Loading...</p>}
                {message && <p className="mb-4 rounded-md bg-rose-50 px-3 py-2 text-sm text-rose-700">{message}</p>}
                {request && (
                    <div className="grid gap-5 lg:grid-cols-[1fr_320px]">
                        <section className="rounded-lg border border-zinc-200 bg-white p-5">
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h1 className="text-2xl font-semibold text-zinc-950">Request #{request.id}</h1>
                                    <p className="mt-1 text-sm text-zinc-500">Created {formatDateTime(request.created_at)}</p>
                                </div>
                                <StatusBadge status={request.status} />
                            </div>
                            <dl className="mt-6 grid gap-4 sm:grid-cols-2">
                                {[
                                    ['Local amount', `${request.amount_local} ${request.currency}`],
                                    ['Amount EUR', request.amount_eur],
                                    ['EUR to local rate', request.eur_to_local_rate],
                                    ['Rate source', request.rate_source],
                                    ['Rate fetched at', formatDateTime(request.rate_fetched_at)],
                                    ['Updated at', formatDateTime(request.updated_at)],
                                    ['Reviewed by', request.reviewed_by ?? '—'],
                                    ['Reviewed at', formatDateTime(request.reviewed_at)],
                                    ['Expired at', formatDateTime(request.expired_at)],
                                ].map(([label, value]) => (
                                    <div key={label} className="rounded-md border border-zinc-200 p-3">
                                        <dt className="text-xs font-medium uppercase text-zinc-500">{label}</dt>
                                        <dd className="mt-1 break-words text-sm font-medium text-zinc-950">{value}</dd>
                                    </div>
                                ))}
                            </dl>
                        </section>

                        <aside className="rounded-lg border border-zinc-200 bg-white p-5">
                            <h2 className="font-semibold text-zinc-950">Finance actions</h2>
                            <p className="mt-1 text-sm text-zinc-500">Visible controls are UX only. The backend authorizes every action.</p>
                            <RoleGate allow="finance" fallback={<p className="mt-4 text-sm text-zinc-500">No finance actions available.</p>}>
                                {request.status === 'pending' ? (
                                    <div className="mt-4 flex gap-2">
                                        <button
                                            type="button"
                                            onClick={() => transition('approve')}
                                            disabled={acting}
                                            className="rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-60"
                                        >
                                            Approve
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => transition('reject')}
                                            disabled={acting}
                                            className="rounded-md bg-rose-600 px-3 py-2 text-sm font-medium text-white hover:bg-rose-700 disabled:opacity-60"
                                        >
                                            Reject
                                        </button>
                                    </div>
                                ) : (
                                    <p className="mt-4 text-sm text-zinc-500">Only pending requests can be reviewed.</p>
                                )}
                            </RoleGate>
                        </aside>
                    </div>
                )}
            </AppLayout>
        </ProtectedRoute>
    );
}
