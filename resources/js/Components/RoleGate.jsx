import { getStoredUser } from '../lib/auth';

export default function RoleGate({ allow, children, fallback = null }) {
    const user = getStoredUser();
    const roles = Array.isArray(allow) ? allow : [allow];

    // UX only: every protected operation is still authorized by the backend API.
    if (!user || !roles.includes(user.role)) {
        return fallback;
    }

    return children;
}
