import { useState, useEffect } from 'react';
import { Users, UserCheck, UserX, Clock, CalendarDays, Briefcase, DollarSign, TrendingUp } from 'lucide-react';
import { employeesApi, attendanceApi, leaveRequestsApi, payrollRunsApi } from '../../api/endpoints';
import { Spinner } from '../../components/ui/Loader';

const StatCard = ({ icon: Icon, label, value, sub, color = 'indigo' }) => {
    const colors = {
        indigo: 'bg-indigo-50 text-indigo-600',
        green: 'bg-green-50 text-green-600',
        yellow: 'bg-yellow-50 text-yellow-600',
        red: 'bg-red-50 text-red-600',
        blue: 'bg-blue-50 text-blue-600',
        purple: 'bg-purple-50 text-purple-600',
    };
    return (
        <div className="rounded-xl border border-gray-200 bg-white p-5">
            <div className="flex items-center gap-3">
                <div className={`rounded-lg p-2.5 ${colors[color]}`}>
                    <Icon className="h-5 w-5" />
                </div>
                <div>
                    <p className="text-sm text-gray-500">{label}</p>
                    <p className="text-2xl font-bold text-gray-900">{value}</p>
                    {sub && <p className="text-xs text-gray-400">{sub}</p>}
                </div>
            </div>
        </div>
    );
};

export default function HrDashboardPage() {
    const [stats, setStats] = useState({
        totalEmployees: 0, activeEmployees: 0, onLeave: 0, newHires: 0,
        todayAttendance: 0, pendingLeaves: 0, openVacancies: 0, lastPayroll: null,
    });
    const [recentEmployees, setRecentEmployees] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        async function load() {
            setLoading(true);
            try {
                const [empRes, attRes, leaveRes] = await Promise.all([
                    employeesApi.list({ per_page: 5, sort_by: 'created_at', sort_dir: 'desc' }),
                    attendanceApi.list({ per_page: 1, date_from: new Date().toISOString().slice(0, 10) }),
                    leaveRequestsApi.list({ per_page: 1, status: 'pending' }),
                ]);

                const empPayload = empRes.data?.data || {};
                const empList = Array.isArray(empPayload) ? empPayload : (empPayload.data || []);
                const empMeta = empPayload.meta || {};

                setRecentEmployees(empList.slice(0, 5));
                setStats({
                    totalEmployees: empMeta.total || empList.length,
                    activeEmployees: empList.filter(e => e.status === 'active').length,
                    onLeave: empList.filter(e => e.status === 'on_leave').length,
                    newHires: 0,
                    todayAttendance: attRes.data?.data?.meta?.total || 0,
                    pendingLeaves: leaveRes.data?.data?.meta?.total || 0,
                    openVacancies: 0,
                    lastPayroll: null,
                });
            } catch {} finally {
                setLoading(false);
            }
        }
        load();
    }, []);

    if (loading) return <div className="flex justify-center py-20"><Spinner /></div>;

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-2xl font-bold text-gray-900">HR Dashboard</h1>
                <p className="text-sm text-gray-500">Human Resource Management overview</p>
            </div>

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard icon={Users} label="Total Employees" value={stats.totalEmployees} sub={`${stats.activeEmployees} active`} color="indigo" />
                <StatCard icon={Clock} label="Today's Attendance" value={stats.todayAttendance} sub="checked in" color="green" />
                <StatCard icon={CalendarDays} label="Pending Leaves" value={stats.pendingLeaves} sub="awaiting approval" color="yellow" />
                <StatCard icon={Briefcase} label="Open Vacancies" value={stats.openVacancies} sub="recruiting" color="blue" />
            </div>

            <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div className="rounded-xl border border-gray-200 bg-white p-5">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Recent Employees</h3>
                    <div className="space-y-3">
                        {recentEmployees.length === 0 ? (
                            <p className="text-sm text-gray-400">No employees found.</p>
                        ) : recentEmployees.map(emp => (
                            <div key={emp.id} className="flex items-center justify-between rounded-lg border border-gray-100 p-3">
                                <div className="flex items-center gap-3">
                                    <div className="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-600">
                                        {(emp.full_name || emp.first_name || '?')[0].toUpperCase()}
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-gray-900">{emp.full_name || `${emp.first_name} ${emp.last_name}`}</p>
                                        <p className="text-xs text-gray-500">{emp.department?.name || '-'} · {emp.position?.name || '-'}</p>
                                    </div>
                                </div>
                                <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${
                                    emp.status === 'active' ? 'bg-green-100 text-green-700' :
                                    emp.status === 'on_leave' ? 'bg-yellow-100 text-yellow-700' :
                                    'bg-gray-100 text-gray-700'
                                }`}>
                                    {emp.status?.replace(/_/g, ' ')}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-5">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Quick Actions</h3>
                    <div className="grid grid-cols-2 gap-3">
                        {[
                            { label: 'Employees', to: '/hrm/employees', icon: Users, color: 'bg-indigo-50 text-indigo-600' },
                            { label: 'Attendance', to: '/hrm/attendance', icon: Clock, color: 'bg-green-50 text-green-600' },
                            { label: 'Leave Requests', to: '/hrm/leave/requests', icon: CalendarDays, color: 'bg-yellow-50 text-yellow-600' },
                            { label: 'Payroll', to: '/hrm/payroll/runs', icon: DollarSign, color: 'bg-purple-50 text-purple-600' },
                            { label: 'Recruitment', to: '/hrm/recruitment/vacancies', icon: Briefcase, color: 'bg-blue-50 text-blue-600' },
                            { label: 'Expenses', to: '/hrm/expenses', icon: TrendingUp, color: 'bg-red-50 text-red-600' },
                        ].map(a => (
                            <a key={a.label} href={a.to}
                                className="flex items-center gap-3 rounded-lg border border-gray-200 p-3 transition hover:bg-gray-50">
                                <div className={`rounded-lg p-2 ${a.color}`}><a.icon className="h-4 w-4" /></div>
                                <span className="text-sm font-medium text-gray-700">{a.label}</span>
                            </a>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}
