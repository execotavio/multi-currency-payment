import { Link } from '@inertiajs/react';
import RoleGate from '../Components/RoleGate';
import { getStoredUser } from '../lib/auth';
import { logout } from '../lib/api';

export default function AppLayout({ children }) {
    const user = getStoredUser();

    return (
        <div className="min-h-screen bg-[#f6f7f9]">
            <header className="border-b border-zinc-200 bg-white">
                <div className="mx-auto flex max-w-6xl flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <Link href="/payment-requests" className="text-lg font-semibold text-zinc-950">
                            Multi-Currency Payment
                        </Link>
                        {user && (
                            <p className="mt-1 text-sm text-zinc-500">
                                {user.name} · {user.role}
                            </p>
                        )}
                    </div>
                    <nav className="flex flex-wrap items-center gap-2">
                        <Link className="rounded-md px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100" href="/payment-requests">
                            Requests
                        </Link>
                        <RoleGate allow="employee">
                            <Link className="rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700" href="/payment-requests/create">
                                New request
                            </Link>
                        </RoleGate>
                        {user && (
                            <button
                                type="button"
                                onClick={logout}
                                className="rounded-md border border-zinc-300 px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100"
                            >
                                Logout
                            </button>
                        )}
                    </nav>
                </div>
            </header>
            <main className="mx-auto max-w-6xl px-4 py-6">{children}</main>
        </div>
    );
}
