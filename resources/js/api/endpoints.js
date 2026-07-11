import api from './client';

export const authApi = {
    login: (credentials) => api.post('/v1/auth/login', credentials),
    register: (data) => api.post('/v1/auth/register', data),
    logout: () => api.post('/v1/auth/logout'),
    me: () => api.get('/v1/auth/me'),
};

export const dashboardApi = {
    index: () => api.get('/v1/dashboard'),
    notifications: () => api.get('/v1/dashboard/notifications'),
    unreadCount: () => api.get('/v1/dashboard/notifications/unread-count'),
    markRead: (id) => api.post(`/v1/dashboard/notifications/${id}/read`),
    markAllRead: () => api.post('/v1/dashboard/notifications/read-all'),
    auditLogs: (params) => api.get('/v1/dashboard/audit-logs', { params }),
};

export const usersApi = {
    list: (params) => api.get('/v1/settings/users', { params }),
    store: (data) => api.post('/v1/settings/users', data),
    show: (id) => api.get(`/v1/settings/users/${id}`),
    update: (id, data) => api.put(`/v1/settings/users/${id}`, data),
    delete: (id) => api.delete(`/v1/settings/users/${id}`),
};

export const companiesApi = {
    list: (params) => api.get('/v1/settings/companies', { params }),
    store: (data) => api.post('/v1/settings/companies', data),
    show: (id) => api.get(`/v1/settings/companies/${id}`),
    update: (id, data) => api.put(`/v1/settings/companies/${id}`, data),
    delete: (id) => api.delete(`/v1/settings/companies/${id}`),
};

export const settingsApi = {
    currencies: (params) => api.get('/v1/settings/currencies', { params }),
    storeCurrency: (data) => api.post('/v1/settings/currencies', data),
    taxSettings: (params) => api.get('/v1/settings/tax-settings', { params }),
    storeTaxSetting: (data) => api.post('/v1/settings/tax-settings', data),
    numberingSequences: (params) => api.get('/v1/settings/numbering-sequences', { params }),
    storeNumberingSequence: (data) => api.post('/v1/settings/numbering-sequences', data),
    branches: (params) => api.get('/v1/settings/branches', { params }),
    departments: (params) => api.get('/v1/settings/departments', { params }),
    positions: (params) => api.get('/v1/settings/positions', { params }),
};

// Settings CRUD adapters for useCrudApi hook
export const settingsBranchesApi = {
    list: (params) => api.get('/v1/settings/branches', { params }),
};

export const settingsDepartmentsApi = {
    list: (params) => api.get('/v1/settings/departments', { params }),
};

export const settingsPositionsApi = {
    list: (params) => api.get('/v1/settings/positions', { params }),
};

export const settingsCurrenciesApi = {
    list: (params) => api.get('/v1/settings/currencies', { params }),
    store: (data) => api.post('/v1/settings/currencies', data),
};

export const settingsTaxSettingsApi = {
    list: (params) => api.get('/v1/settings/tax-settings', { params }),
    store: (data) => api.post('/v1/settings/tax-settings', data),
};

export const settingsNumberingApi = {
    list: (params) => api.get('/v1/settings/numbering-sequences', { params }),
    store: (data) => api.post('/v1/settings/numbering-sequences', data),
};

export const masterDataApi = {
    list: (module, params) => api.get(`/v1/master-data/${module}`, { params }),
    store: (module, data) => api.post(`/v1/master-data/${module}`, data),
    show: (module, id) => api.get(`/v1/master-data/${module}/${id}`),
    update: (module, id, data) => api.put(`/v1/master-data/${module}/${id}`, data),
    destroy: (module, id) => api.delete(`/v1/master-data/${module}/${id}`),
    toggleStatus: (module, id) => api.patch(`/v1/master-data/${module}/${id}/toggle-status`),
    export: (module, params) => api.get(`/v1/master-data/${module}/export`, { params, responseType: 'blob' }),
    import: (module, formData) => api.post(`/v1/master-data/${module}/import`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
    }),
};

// ─── Phase 2: Purchasing ─────────────────────────
export const purchaseOrdersApi = {
    list: (params) => api.get('/v1/purchasing/purchase-orders', { params }),
    store: (data) => api.post('/v1/purchasing/purchase-orders', data),
    show: (id) => api.get(`/v1/purchasing/purchase-orders/${id}`),
    update: (id, data) => api.put(`/v1/purchasing/purchase-orders/${id}`, data),
    destroy: (id) => api.delete(`/v1/purchasing/purchase-orders/${id}`),
    submit: (id) => api.post(`/v1/purchasing/purchase-orders/${id}/submit`),
    approve: (id) => api.post(`/v1/purchasing/purchase-orders/${id}/approve`),
    cancel: (id) => api.post(`/v1/purchasing/purchase-orders/${id}/cancel`),
    sendToVendor: (id) => api.post(`/v1/purchasing/purchase-orders/${id}/send`),
};

export const supplierQuotationsApi = {
    list: (params) => api.get('/v1/purchasing/quotations', { params }),
    store: (data) => api.post('/v1/purchasing/quotations', data),
    show: (id) => api.get(`/v1/purchasing/quotations/${id}`),
    update: (id, data) => api.put(`/v1/purchasing/quotations/${id}`, data),
    destroy: (id) => api.delete(`/v1/purchasing/quotations/${id}`),
    sendToVendor: (id) => api.post(`/v1/purchasing/quotations/${id}/send`),
    accept: (id) => api.post(`/v1/purchasing/quotations/${id}/accept`),
    reject: (id) => api.post(`/v1/purchasing/quotations/${id}/reject`),
};

export const purchaseRequisitionsApi = {
    list: (params) => api.get('/v1/purchasing/requisitions', { params }),
    store: (data) => api.post('/v1/purchasing/requisitions', data),
    show: (id) => api.get(`/v1/purchasing/requisitions/${id}`),
    update: (id, data) => api.put(`/v1/purchasing/requisitions/${id}`, data),
    destroy: (id) => api.delete(`/v1/purchasing/requisitions/${id}`),
    approve: (id) => api.post(`/v1/purchasing/requisitions/${id}/approve`),
    cancel: (id) => api.post(`/v1/purchasing/requisitions/${id}/cancel`),
};

export const vendorPricelistsApi = {
    list: (params) => api.get('/v1/purchasing/pricelists', { params }),
    store: (data) => api.post('/v1/purchasing/pricelists', data),
    show: (id) => api.get(`/v1/purchasing/pricelists/${id}`),
    update: (id, data) => api.put(`/v1/purchasing/pricelists/${id}`, data),
    destroy: (id) => api.delete(`/v1/purchasing/pricelists/${id}`),
    bestPrice: (productId) => api.get(`/v1/purchasing/pricelists/best-price/${productId}`),
};

// ─── Phase 2: Inventory ─────────────────────────
export const stockPickingsApi = {
    list: (params) => api.get('/v1/inventory/pickings', { params }),
    store: (data) => api.post('/v1/inventory/pickings', data),
    show: (id) => api.get(`/v1/inventory/pickings/${id}`),
    update: (id, data) => api.put(`/v1/inventory/pickings/${id}`, data),
    destroy: (id) => api.delete(`/v1/inventory/pickings/${id}`),
    confirm: (id) => api.post(`/v1/inventory/pickings/${id}/confirm`),
    validate: (id) => api.post(`/v1/inventory/pickings/${id}/validate`),
    cancel: (id) => api.post(`/v1/inventory/pickings/${id}/cancel`),
};

export const inventoryApi = {
    // Quants (read-only)
    list: (params) => api.get('/v1/inventory/quants', { params }),
    quants: (params) => api.get('/v1/inventory/quants', { params }),
    productStock: (productId) => api.get(`/v1/inventory/quants/product/${productId}`),
    totalOnHand: (productId) => api.get(`/v1/inventory/quants/on-hand/${productId}`),
    lowStock: () => api.get('/v1/inventory/quants/low-stock'),
    // Adjustments CRUD
    adjustments: (params) => api.get('/v1/inventory/adjustments', { params }),
    store: (data) => api.post('/v1/inventory/adjustments', data),
    createAdjustment: (data) => api.post('/v1/inventory/adjustments', data),
    show: (id) => api.get(`/v1/inventory/adjustments/${id}`),
    showAdjustment: (id) => api.get(`/v1/inventory/adjustments/${id}`),
    update: (id, data) => api.put(`/v1/inventory/adjustments/${id}`, data),
    updateAdjustment: (id, data) => api.put(`/v1/inventory/adjustments/${id}`, data),
    destroy: (id) => api.delete(`/v1/inventory/adjustments/${id}`),
    deleteAdjustment: (id) => api.delete(`/v1/inventory/adjustments/${id}`),
    validateAdjustment: (id) => api.post(`/v1/inventory/adjustments/${id}/validate`),
};

// Inventory Adjustments specific (for useCrudApi compatibility)
export const inventoryAdjustmentsApi = {
    list: (params) => api.get('/v1/inventory/adjustments', { params }),
    store: (data) => api.post('/v1/inventory/adjustments', data),
    show: (id) => api.get(`/v1/inventory/adjustments/${id}`),
    update: (id, data) => api.put(`/v1/inventory/adjustments/${id}`, data),
    destroy: (id) => api.delete(`/v1/inventory/adjustments/${id}`),
    validate: (id) => api.post(`/v1/inventory/adjustments/${id}/validate`),
};

// ─── Reports ─────────────────────────────────────
export const reportsApi = {
    purchasing: (params) => api.get('/v1/reports/purchasing', { params }),
    inventory: (params) => api.get('/v1/reports/inventory', { params }),
    purchasingPdf: (params) => api.get('/v1/reports/purchasing/pdf', { params, responseType: 'blob' }),
    inventoryPdf: (params) => api.get('/v1/reports/inventory/pdf', { params, responseType: 'blob' }),
};

// ─── Phase 4: Manufacturing ──────────────────────
export const workCentersApi = {
    list: (params) => api.get('/v1/manufacturing/work-centers', { params }),
    store: (data) => api.post('/v1/manufacturing/work-centers', data),
    show: (id) => api.get(`/v1/manufacturing/work-centers/${id}`),
    update: (id, data) => api.put(`/v1/manufacturing/work-centers/${id}`, data),
    destroy: (id) => api.delete(`/v1/manufacturing/work-centers/${id}`),
};

export const routingsApi = {
    list: (params) => api.get('/v1/manufacturing/routings', { params }),
    store: (data) => api.post('/v1/manufacturing/routings', data),
    show: (id) => api.get(`/v1/manufacturing/routings/${id}`),
    update: (id, data) => api.put(`/v1/manufacturing/routings/${id}`, data),
    destroy: (id) => api.delete(`/v1/manufacturing/routings/${id}`),
};

export const bomsApi = {
    list: (params) => api.get('/v1/manufacturing/boms', { params }),
    store: (data) => api.post('/v1/manufacturing/boms', data),
    show: (id) => api.get(`/v1/manufacturing/boms/${id}`),
    update: (id, data) => api.put(`/v1/manufacturing/boms/${id}`, data),
    destroy: (id) => api.delete(`/v1/manufacturing/boms/${id}`),
    approve: (id) => api.post(`/v1/manufacturing/boms/${id}/approve`),
    activate: (id) => api.post(`/v1/manufacturing/boms/${id}/activate`),
    cancel: (id) => api.post(`/v1/manufacturing/boms/${id}/cancel`),
    clone: (id) => api.post(`/v1/manufacturing/boms/${id}/clone`),
};

export const manufacturingOrdersApi = {
    list: (params) => api.get('/v1/manufacturing/orders', { params }),
    store: (data) => api.post('/v1/manufacturing/orders', data),
    show: (id) => api.get(`/v1/manufacturing/orders/${id}`),
    update: (id, data) => api.put(`/v1/manufacturing/orders/${id}`, data),
    destroy: (id) => api.delete(`/v1/manufacturing/orders/${id}`),
    confirm: (id) => api.post(`/v1/manufacturing/orders/${id}/confirm`),
    reserveMaterials: (id) => api.post(`/v1/manufacturing/orders/${id}/reserve-materials`),
    startProduction: (id) => api.post(`/v1/manufacturing/orders/${id}/start-production`),
    finishProduction: (id) => api.post(`/v1/manufacturing/orders/${id}/finish-production`),
    markFinished: (id) => api.post(`/v1/manufacturing/orders/${id}/mark-finished`),
    close: (id) => api.post(`/v1/manufacturing/orders/${id}/close`),
    cancel: (id) => api.post(`/v1/manufacturing/orders/${id}/cancel`),
};

export const qualityChecksApi = {
    list: (params) => api.get('/v1/manufacturing/quality/checks', { params }),
    store: (data) => api.post('/v1/manufacturing/quality/checks', data),
    show: (id) => api.get(`/v1/manufacturing/quality/checks/${id}`),
    update: (id, data) => api.put(`/v1/manufacturing/quality/checks/${id}`, data),
    destroy: (id) => api.delete(`/v1/manufacturing/quality/checks/${id}`),
    startInspection: (id) => api.post(`/v1/manufacturing/quality/checks/${id}/start-inspection`),
    pass: (id) => api.post(`/v1/manufacturing/quality/checks/${id}/pass`),
    fail: (id) => api.post(`/v1/manufacturing/quality/checks/${id}/fail`),
};

export const qualityControlPointsApi = {
    list: (params) => api.get('/v1/manufacturing/quality/control-points', { params }),
    store: (data) => api.post('/v1/manufacturing/quality/control-points', data),
    show: (id) => api.get(`/v1/manufacturing/quality/control-points/${id}`),
    update: (id, data) => api.put(`/v1/manufacturing/quality/control-points/${id}`, data),
    destroy: (id) => api.delete(`/v1/manufacturing/quality/control-points/${id}`),
};

export const scrapOrdersApi = {
    list: (params) => api.get('/v1/manufacturing/scraps', { params }),
    store: (data) => api.post('/v1/manufacturing/scraps', data),
    show: (id) => api.get(`/v1/manufacturing/scraps/${id}`),
    update: (id, data) => api.put(`/v1/manufacturing/scraps/${id}`, data),
    destroy: (id) => api.delete(`/v1/manufacturing/scraps/${id}`),
    confirm: (id) => api.post(`/v1/manufacturing/scraps/${id}/confirm`),
    process: (id) => api.post(`/v1/manufacturing/scraps/${id}/process`),
};

export const equipmentApi = {
    list: (params) => api.get('/v1/manufacturing/equipment', { params }),
    store: (data) => api.post('/v1/manufacturing/equipment', data),
    show: (id) => api.get(`/v1/manufacturing/equipment/${id}`),
    update: (id, data) => api.put(`/v1/manufacturing/equipment/${id}`, data),
    destroy: (id) => api.delete(`/v1/manufacturing/equipment/${id}`),
};

export const maintenanceOrdersApi = {
    list: (params) => api.get('/v1/manufacturing/maintenance', { params }),
    store: (data) => api.post('/v1/manufacturing/maintenance', data),
    show: (id) => api.get(`/v1/manufacturing/maintenance/${id}`),
    update: (id, data) => api.put(`/v1/manufacturing/maintenance/${id}`, data),
    destroy: (id) => api.delete(`/v1/manufacturing/maintenance/${id}`),
    schedule: (id) => api.post(`/v1/manufacturing/maintenance/${id}/schedule`),
    startWork: (id) => api.post(`/v1/manufacturing/maintenance/${id}/start-work`),
    complete: (id) => api.post(`/v1/manufacturing/maintenance/${id}/complete`),
    cancel: (id) => api.post(`/v1/manufacturing/maintenance/${id}/cancel`),
};

export const assetCategoriesApi = {
    list: (params) => api.get('/v1/manufacturing/asset-categories', { params }),
    store: (data) => api.post('/v1/manufacturing/asset-categories', data),
    show: (id) => api.get(`/v1/manufacturing/asset-categories/${id}`),
    update: (id, data) => api.put(`/v1/manufacturing/asset-categories/${id}`, data),
    destroy: (id) => api.delete(`/v1/manufacturing/asset-categories/${id}`),
};

export const assetsApi = {
    list: (params) => api.get('/v1/manufacturing/assets', { params }),
    store: (data) => api.post('/v1/manufacturing/assets', data),
    show: (id) => api.get(`/v1/manufacturing/assets/${id}`),
    update: (id, data) => api.put(`/v1/manufacturing/assets/${id}`, data),
    destroy: (id) => api.delete(`/v1/manufacturing/assets/${id}`),
    activate: (id) => api.post(`/v1/manufacturing/assets/${id}/activate`),
    transfer: (id, data) => api.post(`/v1/manufacturing/assets/${id}/transfer`, data),
    dispose: (id, data) => api.post(`/v1/manufacturing/assets/${id}/dispose`, data),
    depreciate: (id) => api.post(`/v1/manufacturing/assets/${id}/depreciate`),
};

export const manufacturingDashboardApi = {
    summary: () => api.get('/v1/manufacturing/dashboard/summary'),
    productionTrend: (params) => api.get('/v1/manufacturing/dashboard/production-trend', { params }),
    productionReport: (params) => api.get('/v1/manufacturing/reports/production', { params }),
    qualityReport: (params) => api.get('/v1/manufacturing/reports/quality', { params }),
    costReport: (params) => api.get('/v1/manufacturing/reports/costs', { params }),
    maintenanceReport: (params) => api.get('/v1/manufacturing/reports/maintenance', { params }),
    assetReport: (params) => api.get('/v1/manufacturing/reports/assets', { params }),
    scrapReport: (params) => api.get('/v1/manufacturing/reports/scraps', { params }),
};

// ─── Phase 5: HRM ────────────────────────────────
export const employeesApi = {
    list: (params) => api.get('/v1/hrm/employees', { params }),
    store: (data) => api.post('/v1/hrm/employees', data),
    show: (id) => api.get(`/v1/hrm/employees/${id}`),
    update: (id, data) => api.put(`/v1/hrm/employees/${id}`, data),
    destroy: (id) => api.delete(`/v1/hrm/employees/${id}`),
    terminate: (id, data) => api.post(`/v1/hrm/employees/${id}/terminate`, data),
};

export const employeeContractsApi = {
    list: (params) => api.get('/v1/hrm/contracts', { params }),
    store: (data) => api.post('/v1/hrm/contracts', data),
    show: (id) => api.get(`/v1/hrm/contracts/${id}`),
    update: (id, data) => api.put(`/v1/hrm/contracts/${id}`, data),
    destroy: (id) => api.delete(`/v1/hrm/contracts/${id}`),
};

export const employeeDocumentsApi = {
    list: (params) => api.get('/v1/hrm/documents', { params }),
    store: (data) => api.post('/v1/hrm/documents', data),
    show: (id) => api.get(`/v1/hrm/documents/${id}`),
    update: (id, data) => api.put(`/v1/hrm/documents/${id}`, data),
    destroy: (id) => api.delete(`/v1/hrm/documents/${id}`),
};

export const workSchedulesApi = {
    list: (params) => api.get('/v1/hrm/work-schedules', { params }),
    store: (data) => api.post('/v1/hrm/work-schedules', data),
    show: (id) => api.get(`/v1/hrm/work-schedules/${id}`),
    update: (id, data) => api.put(`/v1/hrm/work-schedules/${id}`, data),
    destroy: (id) => api.delete(`/v1/hrm/work-schedules/${id}`),
};

export const shiftsApi = {
    list: (params) => api.get('/v1/hrm/shifts', { params }),
    store: (data) => api.post('/v1/hrm/shifts', data),
    show: (id) => api.get(`/v1/hrm/shifts/${id}`),
    update: (id, data) => api.put(`/v1/hrm/shifts/${id}`, data),
    destroy: (id) => api.delete(`/v1/hrm/shifts/${id}`),
};

export const attendanceApi = {
    list: (params) => api.get('/v1/attendance/records', { params }),
    store: (data) => api.post('/v1/attendance/records', data),
    show: (id) => api.get(`/v1/attendance/records/${id}`),
    update: (id, data) => api.put(`/v1/attendance/records/${id}`, data),
    destroy: (id) => api.delete(`/v1/attendance/records/${id}`),
    checkIn: (data) => api.post('/v1/attendance/check-in', data),
    checkOut: (id) => api.post(`/v1/attendance/${id}/check-out`),
};

export const attendanceCorrectionsApi = {
    list: (params) => api.get('/v1/attendance/corrections', { params }),
    store: (data) => api.post('/v1/attendance/corrections', data),
    show: (id) => api.get(`/v1/attendance/corrections/${id}`),
    approve: (id) => api.post(`/v1/attendance/corrections/${id}/approve`),
    reject: (id, data) => api.post(`/v1/attendance/corrections/${id}/reject`, data),
};

export const leaveTypesApi = {
    list: (params) => api.get('/v1/leave/types', { params }),
    store: (data) => api.post('/v1/leave/types', data),
    show: (id) => api.get(`/v1/leave/types/${id}`),
    update: (id, data) => api.put(`/v1/leave/types/${id}`, data),
    destroy: (id) => api.delete(`/v1/leave/types/${id}`),
};

export const leaveBalancesApi = {
    list: (params) => api.get('/v1/leave/balances', { params }),
    store: (data) => api.post('/v1/leave/balances', data),
    show: (id) => api.get(`/v1/leave/balances/${id}`),
    update: (id, data) => api.put(`/v1/leave/balances/${id}`, data),
    destroy: (id) => api.delete(`/v1/leave/balances/${id}`),
    initializeYear: (data) => api.post('/v1/leave/balances/initialize-year', data),
};

export const leaveRequestsApi = {
    list: (params) => api.get('/v1/leave/requests', { params }),
    store: (data) => api.post('/v1/leave/requests', data),
    show: (id) => api.get(`/v1/leave/requests/${id}`),
    update: (id, data) => api.put(`/v1/leave/requests/${id}`, data),
    destroy: (id) => api.delete(`/v1/leave/requests/${id}`),
    approve: (id) => api.post(`/v1/leave/requests/${id}/approve`),
    reject: (id, data) => api.post(`/v1/leave/requests/${id}/reject`, data),
    cancel: (id) => api.post(`/v1/leave/requests/${id}/cancel`),
};

export const payrollComponentsApi = {
    list: (params) => api.get('/v1/payroll/components', { params }),
    store: (data) => api.post('/v1/payroll/components', data),
    show: (id) => api.get(`/v1/payroll/components/${id}`),
    update: (id, data) => api.put(`/v1/payroll/components/${id}`, data),
    destroy: (id) => api.delete(`/v1/payroll/components/${id}`),
};

export const salaryStructuresApi = {
    list: (params) => api.get('/v1/payroll/salary-structures', { params }),
    store: (data) => api.post('/v1/payroll/salary-structures', data),
    show: (id) => api.get(`/v1/payroll/salary-structures/${id}`),
    update: (id, data) => api.put(`/v1/payroll/salary-structures/${id}`, data),
    destroy: (id) => api.delete(`/v1/payroll/salary-structures/${id}`),
};

export const employeeSalariesApi = {
    list: (params) => api.get('/v1/payroll/employee-salaries', { params }),
    store: (data) => api.post('/v1/payroll/employee-salaries', data),
    show: (id) => api.get(`/v1/payroll/employee-salaries/${id}`),
    update: (id, data) => api.put(`/v1/payroll/employee-salaries/${id}`, data),
    destroy: (id) => api.delete(`/v1/payroll/employee-salaries/${id}`),
};

export const payrollPeriodsApi = {
    list: (params) => api.get('/v1/payroll/periods', { params }),
    store: (data) => api.post('/v1/payroll/periods', data),
    show: (id) => api.get(`/v1/payroll/periods/${id}`),
    update: (id, data) => api.put(`/v1/payroll/periods/${id}`, data),
    destroy: (id) => api.delete(`/v1/payroll/periods/${id}`),
};

export const payrollRunsApi = {
    list: (params) => api.get('/v1/payroll/runs', { params }),
    store: (data) => api.post('/v1/payroll/runs', data),
    show: (id) => api.get(`/v1/payroll/runs/${id}`),
    update: (id, data) => api.put(`/v1/payroll/runs/${id}`, data),
    destroy: (id) => api.delete(`/v1/payroll/runs/${id}`),
    process: (id) => api.post(`/v1/payroll/runs/${id}/process`),
    confirm: (id) => api.post(`/v1/payroll/runs/${id}/confirm`),
    approve: (id) => api.post(`/v1/payroll/runs/${id}/approve`),
    cancel: (id) => api.post(`/v1/payroll/runs/${id}/cancel`),
};

export const payslipsApi = {
    list: (params) => api.get('/v1/payroll/payslips', { params }),
    store: (data) => api.post('/v1/payroll/payslips', data),
    show: (id) => api.get(`/v1/payroll/payslips/${id}`),
    destroy: (id) => api.delete(`/v1/payroll/payslips/${id}`),
    confirm: (id) => api.post(`/v1/payroll/payslips/${id}/confirm`),
    pay: (id) => api.post(`/v1/payroll/payslips/${id}/pay`),
};

export const jobVacanciesApi = {
    list: (params) => api.get('/v1/recruitment/vacancies', { params }),
    store: (data) => api.post('/v1/recruitment/vacancies', data),
    show: (id) => api.get(`/v1/recruitment/vacancies/${id}`),
    update: (id, data) => api.put(`/v1/recruitment/vacancies/${id}`, data),
    destroy: (id) => api.delete(`/v1/recruitment/vacancies/${id}`),
    publish: (id) => api.post(`/v1/recruitment/vacancies/${id}/publish`),
    close: (id) => api.post(`/v1/recruitment/vacancies/${id}/close`),
};

export const applicantsApi = {
    list: (params) => api.get('/v1/recruitment/applicants', { params }),
    store: (data) => api.post('/v1/recruitment/applicants', data),
    show: (id) => api.get(`/v1/recruitment/applicants/${id}`),
    update: (id, data) => api.put(`/v1/recruitment/applicants/${id}`, data),
    destroy: (id) => api.delete(`/v1/recruitment/applicants/${id}`),
    updateStage: (id, data) => api.put(`/v1/recruitment/applicants/${id}/stage`, data),
};

export const interviewsApi = {
    list: (params) => api.get('/v1/recruitment/interviews', { params }),
    store: (data) => api.post('/v1/recruitment/interviews', data),
    show: (id) => api.get(`/v1/recruitment/interviews/${id}`),
    update: (id, data) => api.put(`/v1/recruitment/interviews/${id}`, data),
    destroy: (id) => api.delete(`/v1/recruitment/interviews/${id}`),
    submitFeedback: (id, data) => api.post(`/v1/recruitment/interviews/${id}/feedback`, data),
};

export const expenseClaimsApi = {
    list: (params) => api.get('/v1/expenses', { params }),
    store: (data) => api.post('/v1/expenses', data),
    show: (id) => api.get(`/v1/expenses/${id}`),
    update: (id, data) => api.put(`/v1/expenses/${id}`, data),
    destroy: (id) => api.delete(`/v1/expenses/${id}`),
    submit: (id) => api.post(`/v1/expenses/${id}/submit`),
    approve: (id) => api.post(`/v1/expenses/${id}/approve`),
    reject: (id, data) => api.post(`/v1/expenses/${id}/reject`, data),
    markPaid: (id) => api.post(`/v1/expenses/${id}/mark-paid`),
};

export const hrmDashboardApi = {
    summary: () => api.get('/v1/hrm/dashboard/summary'),
    headcount: (params) => api.get('/v1/hrm/dashboard/headcount', { params }),
    attendance: (params) => api.get('/v1/hrm/dashboard/attendance', { params }),
    leave: (params) => api.get('/v1/hrm/dashboard/leave', { params }),
};
