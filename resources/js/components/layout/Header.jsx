import { Menu, Bell, LogOut, User } from 'lucide-react';
import { useAuth } from '../../contexts/AuthContext';
import { useNavigate } from 'react-router-dom';
import { useState, useRef, useEffect } from 'react';

export default function Header({ onMenuClick, user }) {
    const { logout } = useAuth();
    const navigate = useNavigate();
    const [dropdownOpen, setDropdownOpen] = useState(false);
    const dropdownRef = useRef(null);

    useEffect(() => {
        function handleClick(e) {
            if (dropdownRef.current && !dropdownRef.current.contains(e.target)) {
                setDropdownOpen(false);
            }
        }
        document.addEventListener('mousedown', handleClick);
        return () => document.removeEventListener('mousedown', handleClick);
    }, []);

    const handleLogout = async () => {
        await logout();
        navigate('/login');
    };

    return (
        <header className="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 shadow-sm">
            {/* Left */}
            <button onClick={onMenuClick} className="text-gray-500 hover:text-gray-700">
                <Menu className="h-6 w-6" />
            </button>

            {/* Right */}
            <div className="flex items-center gap-4">
                {/* Notifications */}
                <button className="relative text-gray-500 hover:text-gray-700">
                    <Bell className="h-5 w-5" />
                    <span className="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                        3
                    </span>
                </button>

                {/* User dropdown */}
                <div className="relative" ref={dropdownRef}>
                    <button
                        onClick={() => setDropdownOpen(!dropdownOpen)}
                        className="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-gray-100"
                    >
                        <div className="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                            <User className="h-4 w-4" />
                        </div>
                        <div className="text-left hidden sm:block">
                            <p className="text-sm font-medium text-gray-700">{user?.name}</p>
                            <p className="text-xs text-gray-500">{user?.roles?.[0]}</p>
                        </div>
                    </button>

                    {dropdownOpen && (
                        <div className="absolute right-0 mt-2 w-48 rounded-lg border border-gray-200 bg-white py-1 shadow-lg z-50">
                            <button
                                onClick={() => { navigate('/profile'); setDropdownOpen(false); }}
                                className="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            >
                                <User className="h-4 w-4" />
                                Profile
                            </button>
                            <hr className="my-1" />
                            <button
                                onClick={handleLogout}
                                className="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                            >
                                <LogOut className="h-4 w-4" />
                                Logout
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </header>
    );
}
