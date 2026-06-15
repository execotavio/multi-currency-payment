import { Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { api } from '../../lib/api';
import ProtectedRoute from '../../Components/ProtectedRoute';
import { formatDateTime } from '../../lib/formatters';

const statuses = ['all', 'pending', 'approved', 'rejected', 'expired'];

export default function Index() {
    const [status, setStatus] = useState('all');
    const [items, setItems] = useState([]);
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        let active = true;
        setLoading(true);
        setError('');

        api.get('/payment-requests', { params: status === 'all' ? {} : { status } })
            .then((response) => {
                if (active) setItems(response.data.data ?? []);
            })
            .catch((error) => {
                if (active) setError(error.response?.data?.message ?? 'Unable to load payment requests.');
            })
            .finally(() => {
                if (active) setLoading(false);
            });

        return () => {
            active = false;
        };
    }, [status]);

    return (
        <ProtectedRoute>
            <AppLayout>
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-zinc-950">Payment requests</h1>
                        <p className="mt-1 text-sm text-zinc-500">Track local amounts, EUR conversion, and review status.</p>
                    </div>
                </div>

                <div className="mt-6 flex flex-wrap gap-2">
                    {statuses.map((item) => (
                        <button
                            key={item}
                            type="button"
                            onClick={() => setStatus(item)}
                            className={`rounded-md border px-3 py-2 text-sm font-medium ${status === item ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-zinc-300 bg-white text-zinc-700'}`}
                        >
                            {item}
                        </button>
                    ))}
                </div>

                {error && <p className="mt-4 rounded-md bg-rose-50 px-3 py-2 text-sm text-rose-700">{error}</p>}

                <div className="mt-4 overflow-hidden rounded-lg border border-zinc-200 bg-white">
                    <table className="w-full min-w-[760px] text-left text-sm">
                        <thead className="border-b border-zinc-200 bg-zinc-50 text-xs uppercase text-zinc-500">
                            <tr>
                                <th className="px-4 py-3">ID</th>
                                <th className="px-4 py-3">Currency</th>
                                <th className="px-4 py-3">Local</th>
                                <th className="px-4 py-3">EUR</th>
                                <th className="px-4 py-3">Rate</th>
                                <th className="px-4 py-3">Status</th>
                                <th className="px-4 py-3">Created</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-zinc-100">
                            {items.map((request) => (
                                <tr key={request.id} className="hover:bg-zinc-50">
                                    <td className="px-4 py-3">
                                        <Link className="font-medium text-blue-700" href={`/payment-requests/${request.id}`}>#{request.id}</Link>
                                    </td>
                                    <td className="px-4 py-3">{request.currency}</td>
                                    <td className="px-4 py-3">{request.amount_local}</td>
                                    <td className="px-4 py-3">{request.amount_eur}</td>
                                    <td className="px-4 py-3">{request.eur_to_local_rate}</td>
                                    <td className="px-4 py-3"><StatusBadge status={request.status} /></td>
                                    <td className="px-4 py-3 text-zinc-500">{formatDateTime(request.created_at)}</td>
                                </tr>
                            ))}
                            {!loading && items.length === 0 && (
                                <tr>
                                    <td className="px-4 py-8 text-center text-zinc-500" colSpan="7">No payment requests found.</td>
                                </tr>
                            )}
                            {loading && (
                                <tr>
                                    <td className="px-4 py-8 text-center text-zinc-500" colSpan="7">Loading...</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </AppLayout>
        </ProtectedRoute>
    );
}
