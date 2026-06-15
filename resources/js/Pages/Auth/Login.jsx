import { Link } from '@inertiajs/react';
import { useState } from 'react';
import { login } from '../../lib/api';

export default function Login() {
    const [form, setForm] = useState({ email: '', password: '' });
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    async function submit(event) {
        event.preventDefault();
        setLoading(true);
        setError('');

        try {
            await login(form);
            window.location.assign('/payment-requests');
        } catch (error) {
            setError(error.response?.data?.message ?? 'Unable to login.');
        } finally {
            setLoading(false);
        }
    }

    return (
        <main className="flex min-h-screen items-center justify-center bg-[#f6f7f9] px-4">
            <form onSubmit={submit} className="w-full max-w-sm rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
                <h1 className="text-xl font-semibold text-zinc-950">Login</h1>
                <div className="mt-6 space-y-4">
                    <label className="block">
                        <span className="text-sm font-medium text-zinc-700">Email</span>
                        <input
                            className="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2"
                            type="email"
                            value={form.email}
                            onChange={(event) => setForm({ ...form, email: event.target.value })}
                            required
                        />
                    </label>
                    <label className="block">
                        <span className="text-sm font-medium text-zinc-700">Password</span>
                        <input
                            className="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2"
                            type="password"
                            value={form.password}
                            onChange={(event) => setForm({ ...form, password: event.target.value })}
                            required
                        />
                    </label>
                    {error && <p className="rounded-md bg-rose-50 px-3 py-2 text-sm text-rose-700">{error}</p>}
                    <button className="w-full rounded-md bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700 disabled:opacity-60" disabled={loading}>
                        {loading ? 'Signing in...' : 'Sign in'}
                    </button>
                </div>
                <p className="mt-4 text-sm text-zinc-600">
                    Need an account? <Link className="font-medium text-blue-700" href="/register">Register</Link>
                </p>
            </form>
        </main>
    );
}
