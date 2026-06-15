const AUTH_KEY = 'mcp.auth';

export function getStoredAuth() {
    try {
        const value = window.localStorage.getItem(AUTH_KEY);
        return value ? JSON.parse(value) : null;
    } catch {
        return null;
    }
}

export function storeAuth(authResponse) {
    window.localStorage.setItem(AUTH_KEY, JSON.stringify(authResponse));
}

export function clearAuth() {
    window.localStorage.removeItem(AUTH_KEY);
}

export function getStoredUser() {
    return getStoredAuth()?.user ?? null;
}

export function getStoredToken() {
    return getStoredAuth()?.token ?? null;
}
