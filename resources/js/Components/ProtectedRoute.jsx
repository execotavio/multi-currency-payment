import { useEffect, useState } from 'react';
import { getStoredAuth } from '../lib/auth';

export default function ProtectedRoute({ children }) {
    const [auth] = useState(() => getStoredAuth());

    useEffect(() => {
        if (!auth?.token) {
            window.location.assign('/login');
        }
    }, [auth]);

    if (!auth?.token) {
        return null;
    }

    return children;
}
