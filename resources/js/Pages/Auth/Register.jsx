import { Link } from '@inertiajs/react';
import { useState } from 'react';
import { register } from '../../lib/api';

const initialForm = {
    name: '',
    email: '',
    country: '',
    currency: '',
    password: '',
    password_confirmation: '',
};

export default function Register() {
    const [form, setForm] = useState(initialForm);
    const [errors, setErrors] = useState({});
    const [message, setMessage] = useState('');
    const [loading, setLoading] = useState(false);

    async function submit(event) {
        event.preventDefault();
        setLoading(true);
        setErrors({});
        setMessage('');

        try {
            await register({ ...form, currency: form.currency.toUpperCase() });
            window.location.assign('/payment-requests');
        } catch (error) {
            setErrors(error.response?.data?.errors ?? {});
            setMessage(error.response?.data?.message ?? 'Unable to register.');
        } finally {
            setLoading(false);
        }
    }

    function field(name, label, type = 'text') {
        return (
            <label className="block">
                <span className="text-sm font-medium text-zinc-700">{label}</span>
                <input
                    className="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2"
                    type={type}
                    value={form[name]}
                    onChange={(event) => setForm({ ...form, [name]: event.target.value })}
                    required
                />
                {errors[name] && <span className="mt-1 block text-xs text-rose-700">{errors[name][0]}</span>}
            </label>
        );
    }

    return (
        <main className="flex min-h-screen items-center justify-center bg-[#f6f7f9] px-4 py-8">
            <form onSubmit={submit} className="w-full max-w-xl rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
                <h1 className="text-xl font-semibold text-zinc-950">Register</h1>
                <div className="mt-6 grid gap-4 sm:grid-cols-2">
                    {field('name', 'Name')}
                    {field('email', 'Email', 'email')}
                    {field('country', 'Country')}
                    {field('currency', 'Currency')}
                    {field('password', 'Password', 'password')}
                    {field('password_confirmation', 'Confirm password', 'password')}
                </div>
                {message && <p className="mt-4 rounded-md bg-rose-50 px-3 py-2 text-sm text-rose-700">{message}</p>}
                <button className="mt-6 w-full rounded-md bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700 disabled:opacity-60" disabled={loading}>
                    {loading ? 'Creating account...' : 'Create account'}
                </button>
                <p className="mt-4 text-sm text-zinc-600">
                    Already have an account? <Link className="font-medium text-blue-700" href="/login">Login</Link>
                </p>
            </form>
        </main>
    );
}
