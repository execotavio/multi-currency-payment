import { Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { api, register } from '../../lib/api';

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
    const [countries, setCountries] = useState([]);
    const [countriesLoading, setCountriesLoading] = useState(true);
    const [currencies, setCurrencies] = useState([]);
    const [currenciesLoading, setCurrenciesLoading] = useState(true);
    const [errors, setErrors] = useState({});
    const [message, setMessage] = useState('');
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        let active = true;

        api.get('/countries')
            .then((response) => {
                if (!active) return;

                const options = response.data.data ?? [];

                setCountries(options);
                setForm((current) => ({
                    ...current,
                    country: current.country || (options[0]?.code ?? ''),
                }));
            })
            .catch((error) => {
                if (active) {
                    setMessage(error.response?.data?.message ?? 'Unable to load countries.');
                }
            })
            .finally(() => {
                if (active) setCountriesLoading(false);
            });

        api.get('/currencies')
            .then((response) => {
                if (!active) return;

                const options = response.data.data ?? [];

                setCurrencies(options);
                setForm((current) => ({
                    ...current,
                    currency: current.currency || (options[0]?.code ?? ''),
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

        try {
            await register(form);
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

    function countryField() {
        return (
            <label className="block">
                <span className="text-sm font-medium text-zinc-700">Country</span>
                <select
                    className="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 py-2"
                    value={form.country}
                    onChange={(event) => setForm({ ...form, country: event.target.value })}
                    disabled={countriesLoading || countries.length === 0}
                    required
                >
                    {countriesLoading && <option value="">Loading...</option>}
                    {!countriesLoading && countries.length === 0 && <option value="">No countries available</option>}
                    {countries.map((country) => (
                        <option key={country.code} value={country.code}>
                            {country.name}
                        </option>
                    ))}
                </select>
                {errors.country && <span className="mt-1 block text-xs text-rose-700">{errors.country[0]}</span>}
            </label>
        );
    }

    function currencyField() {
        return (
            <label className="block">
                <span className="text-sm font-medium text-zinc-700">Currency</span>
                <select
                    className="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 py-2"
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
        );
    }

    return (
        <main className="flex min-h-screen items-center justify-center bg-[#f6f7f9] px-4 py-8">
            <form onSubmit={submit} className="w-full max-w-xl rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
                <h1 className="text-xl font-semibold text-zinc-950">Register</h1>
                <div className="mt-6 grid gap-4 sm:grid-cols-2">
                    {field('name', 'Name')}
                    {field('email', 'Email', 'email')}
                    {countryField()}
                    {currencyField()}
                    {field('password', 'Password', 'password')}
                    {field('password_confirmation', 'Confirm password', 'password')}
                </div>
                {message && <p className="mt-4 rounded-md bg-rose-50 px-3 py-2 text-sm text-rose-700">{message}</p>}
                <button className="mt-6 w-full rounded-md bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700 disabled:opacity-60" disabled={loading || countriesLoading || currenciesLoading || countries.length === 0 || currencies.length === 0}>
                    {loading ? 'Creating account...' : 'Create account'}
                </button>
                <p className="mt-4 text-sm text-zinc-600">
                    Already have an account? <Link className="font-medium text-blue-700" href="/login">Login</Link>
                </p>
            </form>
        </main>
    );
}
