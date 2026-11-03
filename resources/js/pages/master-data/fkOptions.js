import {
    masterDataApi,
    settingsCurrenciesApi,
    settingsTaxSettingsApi,
    usersApi,
} from '../../api/endpoints';

/**
 * Resolve a list endpoint response into a normalized array of { value, label }.
 * Accepts common shapes: { data: [...] }, { data: { data: [...] } }, or [...].
 */
function normalizeList(payload) {
    const data = payload?.data ?? payload;
    const list = Array.isArray(data) ? data : data?.data;
    return Array.isArray(list) ? list : [];
}

function buildOptions(list, labelFn) {
    return list
        .filter((item) => item && item.id != null)
        .map((item) => ({
            value: String(item.id),
            label: labelFn(item),
        }));
}

const codeName = (item) =>
    item.code ? `${item.name} (${item.code})` : item.name;

/**
 * Map a foreign-key field name (plus the owning module) to a loader that
 * returns [{ value, label }] suitable for the SearchSelect component.
 *
 * `module` is required for self-referential `parent_id` fields, which point at
 * the same master-data module that owns the field.
 */
export function getFkLoader(fieldName, module) {
    switch (fieldName) {
        case 'category_id':
            return async (search) => {
                const res = await masterDataApi.list('product-categories', { search, per_page: 100 });
                return buildOptions(normalizeList(res), codeName);
            };
        case 'uom_id':
            return async (search) => {
                const res = await masterDataApi.list('unit-of-measures', { search, per_page: 100 });
                return buildOptions(normalizeList(res), codeName);
            };
        case 'tax_id':
            return async (search) => {
                const res = await settingsTaxSettingsApi.list({ search, per_page: 100 });
                return buildOptions(normalizeList(res), codeName);
            };
        case 'warehouse_id':
            return async (search) => {
                const res = await masterDataApi.list('warehouses', { search, per_page: 100 });
                return buildOptions(normalizeList(res), codeName);
            };
        case 'bank_account_id':
            return async (search) => {
                const res = await masterDataApi.list('bank-accounts', { search, per_page: 100 });
                return buildOptions(normalizeList(res), codeName);
            };
        case 'currency_id':
            return async (search) => {
                const res = await settingsCurrenciesApi.list({ search, per_page: 100 });
                return buildOptions(normalizeList(res), (item) =>
                    item.code ? `${item.name} (${item.code})` : item.name
                );
            };
        case 'pic_id':
            return async (search) => {
                const res = await usersApi.list({ search, per_page: 100 });
                return buildOptions(normalizeList(res), (item) => item.name || item.email);
            };
        case 'parent_id':
            // Self-referential: points at the same module.
            return async (search) => {
                const res = await masterDataApi.list(module, { search, per_page: 100 });
                return buildOptions(normalizeList(res), codeName);
            };
        default:
            return null;
    }
}

/**
 * Map a field name to a static options list (non-async / enum-like FKs).
 * Returns null when the field is not a known FK.
 */
export function getFkStaticOptions(fieldName) {
    // Reserved for future enum-backed foreign keys.
    return null;
}
