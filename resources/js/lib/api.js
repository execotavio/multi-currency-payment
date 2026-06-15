import axios from 'axios';
import { clearAuth, getStoredToken, storeAuth } from './auth';

const client = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

export function setToken(token) {
    if (token) {
        client.defaults.headers.common.Authorization = `Bearer ${token}`;
        return;
    }

    delete client.defaults.headers.common.Authorization;
}

setToken(getStoredToken());

client.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            clearAuth();
            setToken(null);

            if (window.location.pathname !== '/login') {
                window.location.assign('/login');
            }
        }

        return Promise.reject(error);
    },
);

export async function login(credentials) {
    const response = await client.post('/auth/login', credentials);
    storeAuth(response.data);
    setToken(response.data.token);
    return response.data;
}

export async function register(payload) {
    const response = await client.post('/auth/register', payload);
    storeAuth(response.data);
    setToken(response.data.token);
    return response.data;
}

export async function logout() {
    try {
        await client.post('/auth/logout');
    } finally {
        clearAuth();
        setToken(null);
        window.location.assign('/login');
    }
}

export const api = client;
