import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { Button, Input, Alert, Logo } from '../../components/ui';

export default function LoginPage() {
    const { login } = useAuth();
    const navigate = useNavigate();
    const [form, setForm] = useState({ email: '', password: '' });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError('');

        try {
            await login(form);
            navigate('/');
        } catch (err) {
            setError(err.response?.data?.message || 'Login failed. Please check your credentials.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 px-4">
            <div className="w-full max-w-md">
                {/* Logo */}
                <div className="mb-8 flex flex-col items-center">
                    <Logo size="lg" variant="light" />
                    <p className="mt-3 text-sm text-gray-500">Sign in to your account</p>
                </div>

                {/* Form */}
                <div className="rounded-2xl border border-gray-200 bg-white p-8 shadow-xl shadow-gray-200/50">
                    {error && (
                        <div className="mb-4">
                            <Alert type="error" onClose={() => setError('')}>
                                {error}
                            </Alert>
                        </div>
                    )}

                    <form onSubmit={handleSubmit} className="space-y-5">
                        <Input
                            label="Email"
                            type="email"
                            placeholder="admin@demo-erp.com"
                            value={form.email}
                            onChange={(e) => setForm({ ...form, email: e.target.value })}
                            required
                        />
                        <Input
                            label="Password"
                            type="password"
                            placeholder="••••••••"
                            value={form.password}
                            onChange={(e) => setForm({ ...form, password: e.target.value })}
                            required
                        />
                        <Button type="submit" className="w-full" disabled={loading}>
                            {loading ? 'Signing in...' : 'Sign In'}
                        </Button>
                    </form>
                </div>

                <p className="mt-6 text-center text-xs text-gray-400">
                    Demo: admin@demo-erp.com / password
                </p>
            </div>
        </div>
    );
}
