import { createContext, useContext, useState, useEffect, useCallback } from 'react';
import { authApi } from '../api/endpoints';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const [user, setUser] = useState(() => {
        try {
            return JSON.parse(localStorage.getItem('user'));
        } catch {
            return null;
        }
    });
    const [loading, setLoading] = useState(true);

    const fetchUser = useCallback(async () => {
        try {
            const { data } = await authApi.me();
            setUser(data.data);
            localStorage.setItem('user', JSON.stringify(data.data));
        } catch {
            setUser(null);
            localStorage.removeItem('user');
            localStorage.removeItem('token');
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        const token = localStorage.getItem('token');
        if (token) {
            fetchUser();
        } else {
            setLoading(false);
        }
    }, [fetchUser]);

    const login = async (credentials) => {
        const { data } = await authApi.login(credentials);
        const { user: userData, token } = data.data;
        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(userData));
        setUser(userData);
        return userData;
    };

    const logout = async () => {
        try {
            await authApi.logout();
        } catch {
            // ignore
        } finally {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            setUser(null);
        }
    };

    const hasRole = (role) => user?.roles?.includes(role);
    const hasPermission = (permission) => user?.permissions?.includes(permission);

    return (
        <AuthContext.Provider value={{ user, loading, login, logout, hasRole, hasPermission, fetchUser }}>
            {children}
        </AuthContext.Provider>
    );
}

export function useAuth() {
    const ctx = useContext(AuthContext);
    if (!ctx) throw new Error('useAuth must be used within AuthProvider');
    return ctx;
}
