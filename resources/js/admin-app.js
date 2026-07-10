import './bootstrap';

const BANNER_ERROR_CLASSES = 'animate-fade-up flex items-start gap-2 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-300';
const BANNER_SUCCESS_CLASSES = 'animate-fade-up flex items-start gap-2 rounded-lg border border-accent-200 bg-accent-50 px-4 py-3 text-sm text-accent-700 dark:border-accent-900/50 dark:bg-accent-950/30 dark:text-accent-300';

const BADGE_COLORS = {
    green: ['bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400', 'bg-emerald-500'],
    rose: ['bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400', 'bg-rose-500'],
    amber: ['bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400', 'bg-amber-500'],
    zinc: ['bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300', 'bg-zinc-400'],
    accent: ['bg-accent-50 text-accent-700 dark:bg-accent-950/30 dark:text-accent-400', 'bg-accent-600'],
};

function badgeHtml(label, color = 'zinc') {
    const [classes, dot] = BADGE_COLORS[color] || BADGE_COLORS.zinc;
    return `<span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium transition-colors duration-150 ${classes}"><span class="h-1.5 w-1.5 shrink-0 rounded-full ${dot}"></span>${label}</span>`;
}

function loadingIndicatorHtml() {
    return `<span class="inline-flex items-center gap-2 text-zinc-500 dark:text-zinc-400"><i class="ph ph-circle-notch animate-spin text-base text-accent-500"></i>Loading…</span>`;
}

function loadingRow(colspan) {
    return `<tr><td colspan="${colspan}" class="py-10 text-center text-sm">${loadingIndicatorHtml()}</td></tr>`;
}

function emptyRow(colspan, message) {
    return `<tr><td colspan="${colspan}" class="animate-fade-in py-10 text-center text-sm text-zinc-400 dark:text-zinc-500">${message}</td></tr>`;
}

function errorIndicatorHtml(message) {
    return `<span class="inline-flex items-center gap-2 text-rose-500"><i class="ph ph-warning-circle text-base"></i>${message}</span>`;
}

function errorRow(colspan, message) {
    return `<tr><td colspan="${colspan}" class="animate-fade-in py-10 text-center text-sm">${errorIndicatorHtml(message)}</td></tr>`;
}

function qs(selector, ctx = document) {
    return ctx.querySelector(selector);
}

function qsa(selector, ctx = document) {
    return Array.from(ctx.querySelectorAll(selector));
}

function debounce(fn, delay = 350) {
    let timer = null;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

// ─── Auth guard ─────────────────────────────────────────────────────────────

function requireAdminAuth() {
    const token = localStorage.getItem('admin_token');
    const stored = localStorage.getItem('admin_user');
    let user = null;
    try { user = stored ? JSON.parse(stored) : null; } catch (e) { user = null; }

    if (!token || !user || user.user_type !== 'admin') {
        window.location.href = '/admin/login';
        return null;
    }

    window.axios.defaults.headers.common.Authorization = `Bearer ${token}`;

    const nameEl = qs('#topbar-user-name');
    if (nameEl) nameEl.textContent = user.name || user.email || 'Admin';

    const dashNameEl = qs('#dashboard-user-name');
    if (dashNameEl) dashNameEl.textContent = user.name || user.email || 'Admin';

    return user;
}

function initLogout() {
    const button = qs('#logout-button');
    if (!button) return;
    button.addEventListener('click', async () => {
        try { await window.axios.post('/api/v1/logout'); } catch (e) { /* ignore */ }
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_user');
        window.location.href = '/admin/login';
    });
}

// ─── API helper ─────────────────────────────────────────────────────────────

async function apiRequest(method, path, data = null, { isMultipart = false } = {}) {
    try {
        const config = isMultipart ? { headers: { 'Content-Type': 'multipart/form-data' } } : {};
        let response;
        if (method === 'get') {
            response = await window.axios.get(`/api/v1${path}`, { params: data || {} });
        } else if (isMultipart && (method === 'put' || method === 'patch')) {
            // Laravel doesn't parse multipart on PUT/PATCH; use POST + _method spoof.
            data.append('_method', method);
            response = await window.axios.post(`/api/v1${path}`, data, config);
        } else {
            response = await window.axios[method](`/api/v1${path}`, data, config);
        }
        return { ok: true, status: response.status, data: response.data };
    } catch (error) {
        if (error.response) {
            return { ok: false, status: error.response.status, data: error.response.data || {} };
        }
        return { ok: false, status: 0, data: { message: 'Could not reach the server. Check your connection and try again.' } };
    }
}

// ─── Banners / field errors / loading ──────────────────────────────────────

function showBanner(target, message, type = 'error') {
    const banner = typeof target === 'string' ? qs(target) : target;
    if (!banner) return;
    banner.className = type === 'error' ? BANNER_ERROR_CLASSES : BANNER_SUCCESS_CLASSES;
    const icon = type === 'error' ? 'ph-warning-circle' : 'ph-check-circle';
    banner.innerHTML = `<i class="ph ${icon} mt-0.5 text-base"></i><span>${message}</span>`;
}

function hideBanner(target) {
    const banner = typeof target === 'string' ? qs(target) : target;
    if (!banner) return;
    banner.className = 'hidden';
    banner.innerHTML = '';
}

function clearFieldErrors(form) {
    qsa('[data-error-for]', form).forEach((el) => { el.textContent = ''; el.classList.add('hidden'); });
    qsa('input, select, textarea', form).forEach((el) => el.classList.remove('border-rose-400'));
}

function setFieldErrors(form, errors = {}) {
    Object.entries(errors).forEach(([field, messages]) => {
        const errorEl = qs(`[data-error-for="${field}"]`, form);
        const input = qs(`#${field}`, form);
        const message = Array.isArray(messages) ? messages[0] : messages;
        if (errorEl) { errorEl.textContent = message; errorEl.classList.remove('hidden'); }
        if (input) input.classList.add('border-rose-400');
    });
}

function setLoading(button, isLoading) {
    if (!button) return;
    button.disabled = isLoading;
    const idleLabel = qs('[data-label-idle]', button);
    const busyLabel = qs('[data-label-busy]', button);
    if (idleLabel) idleLabel.style.display = isLoading ? 'none' : '';
    if (busyLabel) busyLabel.style.display = isLoading ? 'inline-flex' : 'none';
}

// ─── Modal helpers ──────────────────────────────────────────────────────────

function openModal(id) {
    const modal = qs(`#${id}`);
    if (!modal) return;
    const panel = qs('[data-modal-panel]', modal);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    requestAnimationFrame(() => {
        modal.classList.remove('opacity-0');
        if (panel) panel.classList.remove('opacity-0', 'scale-95');
    });
}

function closeModal(id) {
    const modal = qs(`#${id}`);
    if (!modal) return;
    const panel = qs('[data-modal-panel]', modal);
    modal.classList.add('opacity-0');
    if (panel) panel.classList.add('opacity-0', 'scale-95');
    window.setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 200);
}

function initSpotlight(root = document) {
    qsa('.spotlight', root).forEach((el) => {
        el.addEventListener('mousemove', (event) => {
            const rect = el.getBoundingClientRect();
            el.style.setProperty('--x', `${event.clientX - rect.left}px`);
            el.style.setProperty('--y', `${event.clientY - rect.top}px`);
        });
    });
}

function initMobileNav() {
    const sidebar = qs('#admin-sidebar');
    const backdrop = qs('[data-sidebar-backdrop]');
    if (!sidebar || !backdrop) return;

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
    }

    qsa('[data-sidebar-open]').forEach((btn) => btn.addEventListener('click', openSidebar));
    qsa('[data-sidebar-close]').forEach((btn) => btn.addEventListener('click', closeSidebar));
    backdrop.addEventListener('click', closeSidebar);
}

function wireModalDismiss() {
    qsa('[data-modal]').forEach((modal) => {
        qsa('[data-modal-close]', modal).forEach((btn) => btn.addEventListener('click', () => closeModal(modal.id)));
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal(modal.id);
        });
    });
}

// ─── Pagination ─────────────────────────────────────────────────────────────

function renderPagination(container, meta, onPage) {
    if (!container) return;
    if (!meta || meta.last_page <= 1) { container.innerHTML = ''; return; }

    const pages = [];
    for (let i = 1; i <= meta.last_page; i++) pages.push(i);

    container.innerHTML = `
        <p class="text-sm text-zinc-500 dark:text-zinc-400">Page ${meta.current_page} of ${meta.last_page} &middot; ${meta.total} total</p>
        <div class="flex items-center gap-1">
            <button data-page-nav="${meta.current_page - 1}" ${meta.current_page <= 1 ? 'disabled' : ''} class="btn btn-secondary px-3 py-1.5 disabled:opacity-40 disabled:hover:translate-y-0">Prev</button>
            <button data-page-nav="${meta.current_page + 1}" ${meta.current_page >= meta.last_page ? 'disabled' : ''} class="btn btn-secondary px-3 py-1.5 disabled:opacity-40 disabled:hover:translate-y-0">Next</button>
        </div>
    `;

    qsa('[data-page-nav]', container).forEach((btn) => {
        btn.addEventListener('click', () => onPage(parseInt(btn.dataset.pageNav, 10)));
    });
}

// ─── Misc UI helpers ────────────────────────────────────────────────────────

function confirmAndRun(message, fn) {
    if (window.confirm(message)) fn();
}

function initFilePreviews(root = document) {
    qsa('input[type="file"][data-preview-for], input[type="file"]', root).forEach((input) => {
        input.addEventListener('change', () => {
            const preview = qs(`[data-preview-for="${input.id}"]`, root);
            if (!preview || !input.files || !input.files[0]) return;
            preview.src = URL.createObjectURL(input.files[0]);
            preview.classList.remove('hidden');
        });
    });
}

function escapeHtml(value) {
    if (value === null || value === undefined) return '';
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function formatDate(value) {
    if (!value) return '—';
    return new Date(value).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

// ═══════════════════════════════════════════════════════════════════════════
// Page: users/clients
// ═══════════════════════════════════════════════════════════════════════════

function initUsersClientsPage() {
    const tbody = qs('#clients-table-body');
    if (!tbody) return;

    let page = 1;

    async function load() {
        tbody.innerHTML = loadingRow(6);
        const result = await apiRequest('get', '/admin/users', {
            user_type: 'customer',
            page,
            search: qs('#clients-search').value || undefined,
            status: qs('#clients-status-filter').value || undefined,
        });

        if (!result.ok) {
            tbody.innerHTML = errorRow(6, 'Failed to load clients.');
            return;
        }

        const clients = result.data.data;
        if (!clients.length) {
            tbody.innerHTML = emptyRow(6, 'No clients found.');
        } else {
            tbody.innerHTML = clients.map((c) => `
                <tr class="border-b border-zinc-100 dark:border-zinc-800/70 table-row-motion">
                    <td class="py-3 px-4 text-sm font-medium">${escapeHtml(c.name)}</td>
                    <td class="py-3 px-4 text-sm">${escapeHtml(c.phone) || '—'}</td>
                    <td class="py-3 px-4 text-sm">${escapeHtml(c.email) || '—'}</td>
                    <td class="py-3 px-4">${statusBadge(c.status)}</td>
                    <td class="py-3 px-4 text-sm text-zinc-500">${formatDate(c.created_at)}</td>
                    <td class="py-3 px-4 text-right text-sm">
                        <button data-action="view" data-id="${c.id}" class="link-action">View</button>
                        <button data-action="toggle-block" data-id="${c.id}" data-status="${c.status}" class="ml-3 link-action">${c.status === 'blocked' ? 'Unblock' : 'Block'}</button>
                        <button data-action="delete" data-id="${c.id}" class="ml-3 link-action-danger">Delete</button>
                    </td>
                </tr>
            `).join('');
        }

        renderPagination(qs('#clients-pagination'), result.data.meta, (p) => { page = p; load(); });
    }

    function statusBadge(status) {
        const color = status === 'active' ? 'green' : status === 'blocked' ? 'rose' : 'amber';
        return badgeHtml(status, color);
    }

    function wishlistItemLabel(w) {
        if (!w.item) return `Item no longer available (#${w.item_id})`;
        if (w.item_type === 'provider') return w.item.business_name || w.item.user?.name || `#${w.item_id}`;
        return w.item.name_en || `#${w.item_id}`;
    }

    async function viewClient(id) {
        const [userResult, wishlistResult] = await Promise.all([
            apiRequest('get', `/admin/users/${id}`),
            apiRequest('get', `/admin/users/${id}/wishlist`),
        ]);
        if (!userResult.ok) return;
        const u = userResult.data.data;
        const items = wishlistResult.ok ? wishlistResult.data.data : [];

        qs('#client-detail-body').innerHTML = `
            <div class="space-y-4">
                <div>
                    <p class="text-sm font-semibold">${escapeHtml(u.name)}</p>
                    <p class="text-sm text-zinc-500">${escapeHtml(u.phone) || '—'} &middot; ${escapeHtml(u.email) || '—'}</p>
                    <p class="mt-1 text-xs text-zinc-400">Joined ${formatDate(u.created_at)}</p>
                </div>
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">Wishlist (${items.length})</p>
                    ${items.length ? `<div class="space-y-2">${items.map((w) => `
                        <div class="flex items-center justify-between rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                            <span>${escapeHtml(wishlistItemLabel(w))}</span>
                            <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs capitalize dark:bg-zinc-800">${w.item_type.replace('_', ' ')}</span>
                        </div>
                    `).join('')}</div>` : '<p class="text-sm text-zinc-400">No wishlist items.</p>'}
                </div>
            </div>
        `;

        openModal('client-detail-modal');
    }

    tbody.addEventListener('click', async (event) => {
        const button = event.target.closest('button[data-action]');
        if (!button) return;
        const id = button.dataset.id;

        if (button.dataset.action === 'view') viewClient(id);

        if (button.dataset.action === 'toggle-block') {
            const endpoint = button.dataset.status === 'blocked' ? `/admin/users/${id}/unblock` : `/admin/users/${id}/block`;
            await apiRequest('patch', endpoint);
            load();
        }

        if (button.dataset.action === 'delete') {
            confirmAndRun('Delete this client? This cannot be undone.', async () => {
                await apiRequest('delete', `/admin/users/${id}`);
                load();
            });
        }
    });

    qs('#clients-search').addEventListener('input', debounce(() => { page = 1; load(); }));
    qs('#clients-status-filter').addEventListener('change', () => { page = 1; load(); });

    load();
}

// ═══════════════════════════════════════════════════════════════════════════
// Page: users/providers
// ═══════════════════════════════════════════════════════════════════════════

function initUsersProvidersPage() {
    const tbody = qs('#providers-table-body');
    if (!tbody) return;

    let page = 1;

    async function load() {
        tbody.innerHTML = loadingRow(6);
        const result = await apiRequest('get', '/admin/providers', {
            page,
            search: qs('#providers-search').value || undefined,
            is_verified: qs('#providers-verified-filter').value || undefined,
        });

        if (!result.ok) {
            tbody.innerHTML = errorRow(6, 'Failed to load providers.');
            return;
        }

        const providers = result.data.data;
        if (!providers.length) {
            tbody.innerHTML = emptyRow(6, 'No providers found.');
        } else {
            tbody.innerHTML = providers.map((p) => `
                <tr class="border-b border-zinc-100 dark:border-zinc-800/70 table-row-motion">
                    <td class="py-3 px-4 text-sm font-medium">${escapeHtml(p.business_name) || '—'}</td>
                    <td class="py-3 px-4 text-sm">${escapeHtml(p.user?.name)}<br><span class="text-zinc-400">${escapeHtml(p.user?.phone)}</span></td>
                    <td class="py-3 px-4 text-sm">${escapeHtml(p.city?.name_en) || '—'}</td>
                    <td class="py-3 px-4 text-sm">${p.availability_status}</td>
                    <td class="py-3 px-4">${p.is_verified ? badgeHtml('Verified', 'green') : badgeHtml('Pending', 'amber')}</td>
                    <td class="py-3 px-4 text-right text-sm">
                        <button data-action="view" data-id="${p.id}" class="link-action">View</button>
                        <button data-action="toggle-verify" data-id="${p.id}" data-verified="${p.is_verified ? '1' : '0'}" class="ml-3 link-action">${p.is_verified ? 'Unverify' : 'Verify'}</button>
                        <button data-action="delete" data-id="${p.id}" class="ml-3 link-action-danger">Delete</button>
                    </td>
                </tr>
            `).join('');
        }

        renderPagination(qs('#providers-pagination'), result.data.meta, (p) => { page = p; load(); });
    }

    async function viewProvider(id) {
        const result = await apiRequest('get', `/admin/providers/${id}`);
        if (!result.ok) return;
        const p = result.data.data;

        qs('#provider-detail-body').innerHTML = `
            <div class="space-y-4">
                <div>
                    <p class="text-sm font-semibold">${escapeHtml(p.business_name)}</p>
                    <p class="text-sm text-zinc-500">${escapeHtml(p.description) || 'No description.'}</p>
                    <p class="mt-1 text-xs text-zinc-400">${p.experience_years ?? 0} years experience &middot; ${escapeHtml(p.city?.name_en)}</p>
                </div>
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">Sub-categories</p>
                    ${(p.sub_categories || []).length
                        ? `<div class="flex flex-wrap gap-1">${p.sub_categories.map((s) => `<span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs dark:bg-zinc-800">${escapeHtml(s.sub_category?.name_en)}</span>`).join('')}</div>`
                        : '<p class="text-sm text-zinc-400">None</p>'}
                </div>
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">Documents</p>
                    ${(p.documents || []).length ? p.documents.map((doc) => `
                        <div class="flex items-center justify-between rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 mb-2">
                            <a href="${doc.document_url}" target="_blank" class="link-action">${doc.type}</a>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-zinc-400">${doc.status}</span>
                                ${doc.status === 'pending' ? `
                                    <button data-doc-action="approve" data-doc-id="${doc.id}" class="text-emerald-600 hover:underline">Approve</button>
                                    <button data-doc-action="reject" data-doc-id="${doc.id}" class="text-rose-600 hover:underline">Reject</button>
                                ` : ''}
                            </div>
                        </div>
                    `).join('') : '<p class="text-sm text-zinc-400">None uploaded</p>'}
                </div>
            </div>
        `;

        openModal('provider-detail-modal');
    }

    tbody.addEventListener('click', async (event) => {
        const button = event.target.closest('button[data-action]');
        if (!button) return;
        const id = button.dataset.id;

        if (button.dataset.action === 'view') viewProvider(id);

        if (button.dataset.action === 'toggle-verify') {
            const endpoint = button.dataset.verified === '1' ? `/admin/providers/${id}/unverify` : `/admin/providers/${id}/verify`;
            await apiRequest('patch', endpoint);
            load();
        }

        if (button.dataset.action === 'delete') {
            confirmAndRun('Delete this provider account? This cannot be undone.', async () => {
                await apiRequest('delete', `/admin/providers/${id}`);
                load();
            });
        }
    });

    qs('#provider-detail-body').addEventListener('click', async (event) => {
        const button = event.target.closest('button[data-doc-action]');
        if (!button) return;
        const docId = button.dataset.docId;

        if (button.dataset.docAction === 'approve') {
            await apiRequest('patch', `/admin/provider-documents/${docId}/approve`);
        } else {
            const reason = window.prompt('Reason for rejection:');
            if (!reason) return;
            await apiRequest('patch', `/admin/provider-documents/${docId}/reject`, { rejection_reason: reason });
        }
        closeModal('provider-detail-modal');
        load();
    });

    qs('#providers-search').addEventListener('input', debounce(() => { page = 1; load(); }));
    qs('#providers-verified-filter').addEventListener('change', () => { page = 1; load(); });

    load();
}

// ═══════════════════════════════════════════════════════════════════════════
// Page: categories (with nested sub-categories)
// ═══════════════════════════════════════════════════════════════════════════

function initCategoriesPage() {
    const tbody = qs('#categories-table-body');
    if (!tbody) return;

    let activeCategoryId = null;

    async function loadCategories() {
        tbody.innerHTML = loadingRow(5);
        const result = await apiRequest('get', '/admin/categories', { search: qs('#categories-search').value || undefined });
        if (!result.ok) return;

        const categories = result.data.data;
        if (!categories.length) {
            tbody.innerHTML = emptyRow(5, 'No categories yet.');
            return;
        }

        tbody.innerHTML = categories.map((c) => `
            <tr class="border-b border-zinc-100 dark:border-zinc-800/70 table-row-motion ${activeCategoryId === c.id ? 'bg-accent-50 dark:bg-accent-950/20' : ''}">
                <td class="py-3 px-4">${c.icon ? `<img src="${c.icon}" class="h-8 w-8 rounded-lg object-cover">` : '<div class="h-8 w-8 rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>'}</td>
                <td class="py-3 px-4 text-sm font-medium">${escapeHtml(c.name_en)}<br><span class="text-zinc-400">${escapeHtml(c.name_ar)}</span></td>
                <td class="py-3 px-4 text-sm">${c.sub_categories_count ?? 0}</td>
                <td class="py-3 px-4">${c.is_active ? badgeHtml('Active', 'green') : badgeHtml('Inactive', 'zinc')}</td>
                <td class="py-3 px-4 text-right text-sm">
                    <button data-action="manage" data-id="${c.id}" class="link-action">Sub-categories</button>
                    <button data-action="edit" data-id="${c.id}" class="ml-3 link-action">Edit</button>
                    <button data-action="delete" data-id="${c.id}" class="ml-3 link-action-danger">Delete</button>
                </td>
            </tr>
        `).join('');
    }

    async function loadSubCategories(categoryId) {
        activeCategoryId = categoryId;
        qs('#subcategories-panel').classList.remove('hidden');
        const tbody2 = qs('#subcategories-table-body');
        tbody2.innerHTML = loadingRow(4);

        const result = await apiRequest('get', '/admin/sub-categories', { category_id: categoryId });
        if (!result.ok) return;

        const subCategories = result.data.data;
        tbody2.innerHTML = subCategories.length ? subCategories.map((s) => `
            <tr class="border-b border-zinc-100 dark:border-zinc-800/70 table-row-motion">
                <td class="py-2 px-4">${s.icon ? `<img src="${s.icon}" class="h-7 w-7 rounded-lg object-cover">` : '<div class="h-7 w-7 rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>'}</td>
                <td class="py-2 px-4 text-sm">${escapeHtml(s.name_en)}<br><span class="text-zinc-400">${escapeHtml(s.name_ar)}</span></td>
                <td class="py-2 px-4">${s.is_active ? badgeHtml('Active', 'green') : badgeHtml('Inactive', 'zinc')}</td>
                <td class="py-2 px-4 text-right text-sm">
                    <button data-action="edit-sub" data-id="${s.id}" class="link-action">Edit</button>
                    <button data-action="delete-sub" data-id="${s.id}" class="ml-3 link-action-danger">Delete</button>
                </td>
            </tr>
        `).join('') : emptyRow(4, 'No sub-categories yet.');
    }

    function openCategoryModal(category = null) {
        const form = qs('#category-form');
        form.reset();
        clearFieldErrors(form);
        hideBanner('#category-modal-banner');
        qs('#category-id').value = category?.id || '';
        qs('#category_name_ar').value = category?.name_ar || '';
        qs('#category_name_en').value = category?.name_en || '';
        qs('#category_description_ar').value = category?.description_ar || '';
        qs('#category_description_en').value = category?.description_en || '';
        qs('#category_is_active').checked = category ? !!category.is_active : true;
        qs('#category-modal-title').textContent = category ? 'Edit Category' : 'New Category';
        openModal('category-modal');
    }

    function openSubCategoryModal(sub = null) {
        const form = qs('#sub-category-form');
        form.reset();
        clearFieldErrors(form);
        hideBanner('#sub-category-modal-banner');
        qs('#sub-category-id').value = sub?.id || '';
        qs('#sub_category_name_ar').value = sub?.name_ar || '';
        qs('#sub_category_name_en').value = sub?.name_en || '';
        qs('#sub_category_description_ar').value = sub?.description_ar || '';
        qs('#sub_category_description_en').value = sub?.description_en || '';
        qs('#sub_category_is_active').checked = sub ? !!sub.is_active : true;
        qs('#sub-category-modal-title').textContent = sub ? 'Edit Sub-category' : 'New Sub-category';
        openModal('sub-category-modal');
    }

    qs('#new-category-button').addEventListener('click', () => openCategoryModal());
    qs('#new-sub-category-button').addEventListener('click', () => openSubCategoryModal());

    tbody.addEventListener('click', async (event) => {
        const button = event.target.closest('button[data-action]');
        if (!button) return;
        const id = button.dataset.id;

        if (button.dataset.action === 'manage') loadSubCategories(id);

        if (button.dataset.action === 'edit') {
            const result = await apiRequest('get', `/admin/categories/${id}`);
            if (result.ok) openCategoryModal(result.data.data);
        }

        if (button.dataset.action === 'delete') {
            confirmAndRun('Delete this category and its sub-categories?', async () => {
                await apiRequest('delete', `/admin/categories/${id}`);
                loadCategories();
            });
        }
    });

    qs('#subcategories-table-body').addEventListener('click', async (event) => {
        const button = event.target.closest('button[data-action]');
        if (!button) return;
        const id = button.dataset.id;

        if (button.dataset.action === 'edit-sub') {
            const result = await apiRequest('get', `/admin/sub-categories/${id}`);
            if (result.ok) openSubCategoryModal(result.data.data);
        }

        if (button.dataset.action === 'delete-sub') {
            confirmAndRun('Delete this sub-category?', async () => {
                await apiRequest('delete', `/admin/sub-categories/${id}`);
                loadSubCategories(activeCategoryId);
                loadCategories();
            });
        }
    });

    qs('#category-form').addEventListener('submit', async (event) => {
        event.preventDefault();
        const id = qs('#category-id').value;
        const form = event.target;
        const button = qs('button[type="submit"]', form);
        setLoading(button, true);
        clearFieldErrors(form);

        const formData = new FormData();
        formData.append('name_ar', qs('#category_name_ar').value);
        formData.append('name_en', qs('#category_name_en').value);
        formData.append('description_ar', qs('#category_description_ar').value);
        formData.append('description_en', qs('#category_description_en').value);
        formData.append('is_active', qs('#category_is_active').checked ? '1' : '0');
        const iconFile = qs('#category_icon').files[0];
        if (iconFile) formData.append('icon', iconFile);

        const result = id
            ? await apiRequest('put', `/admin/categories/${id}`, formData, { isMultipart: true })
            : await apiRequest('post', '/admin/categories', formData, { isMultipart: true });

        setLoading(button, false);

        if (result.ok) {
            closeModal('category-modal');
            loadCategories();
        } else if (result.status === 422) {
            setFieldErrors(form, result.data.errors);
        } else {
            showBanner('#category-modal-banner', result.data.message || 'Something went wrong.');
        }
    });

    qs('#sub-category-form').addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!activeCategoryId) return;
        const id = qs('#sub-category-id').value;
        const form = event.target;
        const button = qs('button[type="submit"]', form);
        setLoading(button, true);
        clearFieldErrors(form);

        const formData = new FormData();
        formData.append('category_id', activeCategoryId);
        formData.append('name_ar', qs('#sub_category_name_ar').value);
        formData.append('name_en', qs('#sub_category_name_en').value);
        formData.append('description_ar', qs('#sub_category_description_ar').value);
        formData.append('description_en', qs('#sub_category_description_en').value);
        formData.append('is_active', qs('#sub_category_is_active').checked ? '1' : '0');
        const iconFile = qs('#sub_category_icon').files[0];
        if (iconFile) formData.append('icon', iconFile);

        const result = id
            ? await apiRequest('put', `/admin/sub-categories/${id}`, formData, { isMultipart: true })
            : await apiRequest('post', '/admin/sub-categories', formData, { isMultipart: true });

        setLoading(button, false);

        if (result.ok) {
            closeModal('sub-category-modal');
            loadSubCategories(activeCategoryId);
            loadCategories();
        } else if (result.status === 422) {
            setFieldErrors(form, result.data.errors);
        } else {
            showBanner('#sub-category-modal-banner', result.data.message || 'Something went wrong.');
        }
    });

    qs('#categories-search').addEventListener('input', debounce(() => loadCategories()));

    loadCategories();
}

// ═══════════════════════════════════════════════════════════════════════════
// Page: locations/countries
// ═══════════════════════════════════════════════════════════════════════════

function initLocationsCountriesPage() {
    const tbody = qs('#countries-table-body');
    if (!tbody) return;

    async function load() {
        tbody.innerHTML = loadingRow(6);
        const result = await apiRequest('get', '/admin/countries', { search: qs('#countries-search').value || undefined });
        if (!result.ok) return;

        const countries = result.data.data;
        tbody.innerHTML = countries.length ? countries.map((c) => `
            <tr class="border-b border-zinc-100 dark:border-zinc-800/70 table-row-motion">
                <td class="py-3 px-4">${c.flag ? `<img src="${c.flag}" class="h-6 w-9 rounded object-cover">` : '—'}</td>
                <td class="py-3 px-4 text-sm font-medium">${escapeHtml(c.name_en)}<br><span class="text-zinc-400">${escapeHtml(c.name_ar)}</span></td>
                <td class="py-3 px-4 text-sm">${escapeHtml(c.iso)}</td>
                <td class="py-3 px-4 text-sm">${escapeHtml(c.phone_code)}</td>
                <td class="py-3 px-4">${c.is_active ? badgeHtml('Active', 'green') : badgeHtml('Inactive', 'zinc')}</td>
                <td class="py-3 px-4 text-right text-sm">
                    <button data-action="edit" data-id="${c.id}" class="link-action">Edit</button>
                    <button data-action="delete" data-id="${c.id}" class="ml-3 link-action-danger">Delete</button>
                </td>
            </tr>
        `).join('') : emptyRow(6, 'No countries yet.');
    }

    function openCountryModal(country = null) {
        const form = qs('#country-form');
        form.reset();
        clearFieldErrors(form);
        hideBanner('#country-modal-banner');
        qs('#country-id').value = country?.id || '';
        qs('#country_name_ar').value = country?.name_ar || '';
        qs('#country_name_en').value = country?.name_en || '';
        qs('#country_iso').value = country?.iso || '';
        qs('#country_phone_code').value = country?.phone_code || '';
        qs('#country_currency_code').value = country?.currency_code || '';
        qs('#country_currency_value').value = country?.currency_value ?? '';
        qs('#country_is_active').checked = country ? !!country.is_active : true;
        qs('#country-modal-title').textContent = country ? 'Edit Country' : 'New Country';
        openModal('country-modal');
    }

    qs('#new-country-button').addEventListener('click', () => openCountryModal());

    tbody.addEventListener('click', async (event) => {
        const button = event.target.closest('button[data-action]');
        if (!button) return;
        const id = button.dataset.id;

        if (button.dataset.action === 'edit') {
            const result = await apiRequest('get', `/admin/countries/${id}`);
            if (result.ok) openCountryModal(result.data.data);
        }

        if (button.dataset.action === 'delete') {
            confirmAndRun('Delete this country?', async () => {
                await apiRequest('delete', `/admin/countries/${id}`);
                load();
            });
        }
    });

    qs('#country-form').addEventListener('submit', async (event) => {
        event.preventDefault();
        const id = qs('#country-id').value;
        const form = event.target;
        const button = qs('button[type="submit"]', form);
        setLoading(button, true);
        clearFieldErrors(form);

        const formData = new FormData();
        formData.append('name_ar', qs('#country_name_ar').value);
        formData.append('name_en', qs('#country_name_en').value);
        formData.append('iso', qs('#country_iso').value);
        formData.append('phone_code', qs('#country_phone_code').value);
        formData.append('currency_code', qs('#country_currency_code').value);
        formData.append('currency_value', qs('#country_currency_value').value);
        formData.append('is_active', qs('#country_is_active').checked ? '1' : '0');
        const flagFile = qs('#country_flag').files[0];
        if (flagFile) formData.append('flag', flagFile);

        const result = id
            ? await apiRequest('put', `/admin/countries/${id}`, formData, { isMultipart: true })
            : await apiRequest('post', '/admin/countries', formData, { isMultipart: true });

        setLoading(button, false);

        if (result.ok) {
            closeModal('country-modal');
            load();
        } else if (result.status === 422) {
            setFieldErrors(form, result.data.errors);
        } else {
            showBanner('#country-modal-banner', result.data.message || 'Something went wrong.');
        }
    });

    qs('#countries-search').addEventListener('input', debounce(() => load()));

    load();
}

// ═══════════════════════════════════════════════════════════════════════════
// Page: locations/cities
// ═══════════════════════════════════════════════════════════════════════════

function initLocationsCitiesPage() {
    const tbody = qs('#cities-table-body');
    if (!tbody) return;

    async function populateCountryOptions() {
        const result = await apiRequest('get', '/admin/countries', {});
        if (!result.ok) return;
        const options = result.data.data.map((c) => `<option value="${c.id}">${escapeHtml(c.name_en)}</option>`).join('');
        qs('#cities-country-filter').innerHTML = '<option value="">All countries</option>' + options;
        qs('#city_country_id').innerHTML = '<option value="">Select a country</option>' + options;
    }

    async function load() {
        tbody.innerHTML = loadingRow(4);
        const result = await apiRequest('get', '/admin/cities', {
            search: qs('#cities-search').value || undefined,
            country_id: qs('#cities-country-filter').value || undefined,
        });
        if (!result.ok) return;

        const cities = result.data.data;
        tbody.innerHTML = cities.length ? cities.map((c) => `
            <tr class="border-b border-zinc-100 dark:border-zinc-800/70 table-row-motion">
                <td class="py-3 px-4 text-sm font-medium">${escapeHtml(c.name_en)}<br><span class="text-zinc-400">${escapeHtml(c.name_ar)}</span></td>
                <td class="py-3 px-4 text-sm">${escapeHtml(c.country?.name_en) || '—'}</td>
                <td class="py-3 px-4">${c.is_active ? badgeHtml('Active', 'green') : badgeHtml('Inactive', 'zinc')}</td>
                <td class="py-3 px-4 text-right text-sm">
                    <button data-action="edit" data-id="${c.id}" class="link-action">Edit</button>
                    <button data-action="delete" data-id="${c.id}" class="ml-3 link-action-danger">Delete</button>
                </td>
            </tr>
        `).join('') : emptyRow(4, 'No cities yet.');
    }

    function openCityModal(city = null) {
        const form = qs('#city-form');
        form.reset();
        clearFieldErrors(form);
        hideBanner('#city-modal-banner');
        qs('#city-id').value = city?.id || '';
        qs('#city_country_id').value = city?.country_id || '';
        qs('#city_name_ar').value = city?.name_ar || '';
        qs('#city_name_en').value = city?.name_en || '';
        qs('#city_is_active').checked = city ? !!city.is_active : true;
        qs('#city-modal-title').textContent = city ? 'Edit City' : 'New City';
        openModal('city-modal');
    }

    qs('#new-city-button').addEventListener('click', () => openCityModal());

    tbody.addEventListener('click', async (event) => {
        const button = event.target.closest('button[data-action]');
        if (!button) return;
        const id = button.dataset.id;

        if (button.dataset.action === 'edit') {
            const result = await apiRequest('get', `/admin/cities/${id}`);
            if (result.ok) openCityModal(result.data.data);
        }

        if (button.dataset.action === 'delete') {
            confirmAndRun('Delete this city?', async () => {
                await apiRequest('delete', `/admin/cities/${id}`);
                load();
            });
        }
    });

    qs('#city-form').addEventListener('submit', async (event) => {
        event.preventDefault();
        const id = qs('#city-id').value;
        const form = event.target;
        const button = qs('button[type="submit"]', form);
        setLoading(button, true);
        clearFieldErrors(form);

        const payload = {
            country_id: qs('#city_country_id').value,
            name_ar: qs('#city_name_ar').value,
            name_en: qs('#city_name_en').value,
            is_active: qs('#city_is_active').checked,
        };

        const result = id
            ? await apiRequest('put', `/admin/cities/${id}`, payload)
            : await apiRequest('post', '/admin/cities', payload);

        setLoading(button, false);

        if (result.ok) {
            closeModal('city-modal');
            load();
        } else if (result.status === 422) {
            setFieldErrors(form, result.data.errors);
        } else {
            showBanner('#city-modal-banner', result.data.message || 'Something went wrong.');
        }
    });

    qs('#cities-search').addEventListener('input', debounce(() => load()));
    qs('#cities-country-filter').addEventListener('change', () => load());

    populateCountryOptions().then(load);
}

// ═══════════════════════════════════════════════════════════════════════════
// Page: locations/areas
// ═══════════════════════════════════════════════════════════════════════════

function initLocationsAreasPage() {
    const tbody = qs('#areas-table-body');
    if (!tbody) return;

    async function populateCityOptions() {
        const result = await apiRequest('get', '/admin/cities', {});
        if (!result.ok) return;
        const options = result.data.data.map((c) => `<option value="${c.id}">${escapeHtml(c.name_en)}</option>`).join('');
        qs('#areas-city-filter').innerHTML = '<option value="">All cities</option>' + options;
        qs('#area_city_id').innerHTML = '<option value="">Select a city</option>' + options;
    }

    async function load() {
        tbody.innerHTML = loadingRow(4);
        const result = await apiRequest('get', '/admin/areas', {
            search: qs('#areas-search').value || undefined,
            city_id: qs('#areas-city-filter').value || undefined,
        });
        if (!result.ok) return;

        const areas = result.data.data;
        tbody.innerHTML = areas.length ? areas.map((a) => `
            <tr class="border-b border-zinc-100 dark:border-zinc-800/70 table-row-motion">
                <td class="py-3 px-4 text-sm font-medium">${escapeHtml(a.name_en)}<br><span class="text-zinc-400">${escapeHtml(a.name_ar)}</span></td>
                <td class="py-3 px-4 text-sm">${escapeHtml(a.city?.name_en) || '—'}</td>
                <td class="py-3 px-4">${a.is_active ? badgeHtml('Active', 'green') : badgeHtml('Inactive', 'zinc')}</td>
                <td class="py-3 px-4 text-right text-sm">
                    <button data-action="edit" data-id="${a.id}" class="link-action">Edit</button>
                    <button data-action="delete" data-id="${a.id}" class="ml-3 link-action-danger">Delete</button>
                </td>
            </tr>
        `).join('') : emptyRow(4, 'No areas yet.');
    }

    function openAreaModal(area = null) {
        const form = qs('#area-form');
        form.reset();
        clearFieldErrors(form);
        hideBanner('#area-modal-banner');
        qs('#area-id').value = area?.id || '';
        qs('#area_city_id').value = area?.city_id || '';
        qs('#area_name_ar').value = area?.name_ar || '';
        qs('#area_name_en').value = area?.name_en || '';
        qs('#area_is_active').checked = area ? !!area.is_active : true;
        qs('#area-modal-title').textContent = area ? 'Edit Area' : 'New Area';
        openModal('area-modal');
    }

    qs('#new-area-button').addEventListener('click', () => openAreaModal());

    tbody.addEventListener('click', async (event) => {
        const button = event.target.closest('button[data-action]');
        if (!button) return;
        const id = button.dataset.id;

        if (button.dataset.action === 'edit') {
            const result = await apiRequest('get', `/admin/areas/${id}`);
            if (result.ok) openAreaModal(result.data.data);
        }

        if (button.dataset.action === 'delete') {
            confirmAndRun('Delete this area?', async () => {
                await apiRequest('delete', `/admin/areas/${id}`);
                load();
            });
        }
    });

    qs('#area-form').addEventListener('submit', async (event) => {
        event.preventDefault();
        const id = qs('#area-id').value;
        const form = event.target;
        const button = qs('button[type="submit"]', form);
        setLoading(button, true);
        clearFieldErrors(form);

        const payload = {
            city_id: qs('#area_city_id').value,
            name_ar: qs('#area_name_ar').value,
            name_en: qs('#area_name_en').value,
            is_active: qs('#area_is_active').checked,
        };

        const result = id
            ? await apiRequest('put', `/admin/areas/${id}`, payload)
            : await apiRequest('post', '/admin/areas', payload);

        setLoading(button, false);

        if (result.ok) {
            closeModal('area-modal');
            load();
        } else if (result.status === 422) {
            setFieldErrors(form, result.data.errors);
        } else {
            showBanner('#area-modal-banner', result.data.message || 'Something went wrong.');
        }
    });

    qs('#areas-search').addEventListener('input', debounce(() => load()));
    qs('#areas-city-filter').addEventListener('change', () => load());

    populateCityOptions().then(load);
}

// ═══════════════════════════════════════════════════════════════════════════
// Page: cms/intro-screens
// ═══════════════════════════════════════════════════════════════════════════

function initCmsIntroScreensPage() {
    const tbody = qs('#intro-screens-table-body');
    if (!tbody) return;

    async function load() {
        tbody.innerHTML = loadingRow(5);
        const result = await apiRequest('get', '/admin/intro-screens');
        if (!result.ok) return;

        const screens = result.data.data;
        tbody.innerHTML = screens.length ? screens.map((s) => `
            <tr class="border-b border-zinc-100 dark:border-zinc-800/70 table-row-motion">
                <td class="py-3 px-4">${s.image ? `<img src="${s.image}" class="h-12 w-12 rounded-lg object-cover">` : '—'}</td>
                <td class="py-3 px-4 text-sm font-medium">${escapeHtml(s.title_en)}<br><span class="text-zinc-400">${escapeHtml(s.title_ar)}</span></td>
                <td class="py-3 px-4 text-sm">${s.order}</td>
                <td class="py-3 px-4">${s.is_active ? badgeHtml('Active', 'green') : badgeHtml('Inactive', 'zinc')}</td>
                <td class="py-3 px-4 text-right text-sm">
                    <button data-action="edit" data-id="${s.id}" class="link-action">Edit</button>
                    <button data-action="delete" data-id="${s.id}" class="ml-3 link-action-danger">Delete</button>
                </td>
            </tr>
        `).join('') : emptyRow(5, 'No intro screens yet.');
    }

    function openIntroScreenModal(screen = null) {
        const form = qs('#intro-screen-form');
        form.reset();
        clearFieldErrors(form);
        hideBanner('#intro-screen-modal-banner');
        qs('#intro-screen-id').value = screen?.id || '';
        qs('#intro_screen_title_ar').value = screen?.title_ar || '';
        qs('#intro_screen_title_en').value = screen?.title_en || '';
        qs('#intro_screen_description_ar').value = screen?.description_ar || '';
        qs('#intro_screen_description_en').value = screen?.description_en || '';
        qs('#intro_screen_order').value = screen?.order ?? 0;
        qs('#intro_screen_is_active').checked = screen ? !!screen.is_active : true;
        qs('#intro-screen-modal-title').textContent = screen ? 'Edit Intro Screen' : 'New Intro Screen';
        openModal('intro-screen-modal');
    }

    qs('#new-intro-screen-button').addEventListener('click', () => openIntroScreenModal());

    tbody.addEventListener('click', async (event) => {
        const button = event.target.closest('button[data-action]');
        if (!button) return;
        const id = button.dataset.id;

        if (button.dataset.action === 'edit') {
            const result = await apiRequest('get', `/admin/intro-screens/${id}`);
            if (result.ok) openIntroScreenModal(result.data.data);
        }

        if (button.dataset.action === 'delete') {
            confirmAndRun('Delete this intro screen?', async () => {
                await apiRequest('delete', `/admin/intro-screens/${id}`);
                load();
            });
        }
    });

    qs('#intro-screen-form').addEventListener('submit', async (event) => {
        event.preventDefault();
        const id = qs('#intro-screen-id').value;
        const form = event.target;
        const button = qs('button[type="submit"]', form);
        setLoading(button, true);
        clearFieldErrors(form);

        const formData = new FormData();
        formData.append('title_ar', qs('#intro_screen_title_ar').value);
        formData.append('title_en', qs('#intro_screen_title_en').value);
        formData.append('description_ar', qs('#intro_screen_description_ar').value);
        formData.append('description_en', qs('#intro_screen_description_en').value);
        formData.append('order', qs('#intro_screen_order').value || 0);
        formData.append('is_active', qs('#intro_screen_is_active').checked ? '1' : '0');
        const imageFile = qs('#intro_screen_image').files[0];
        if (imageFile) formData.append('image', imageFile);

        const result = id
            ? await apiRequest('put', `/admin/intro-screens/${id}`, formData, { isMultipart: true })
            : await apiRequest('post', '/admin/intro-screens', formData, { isMultipart: true });

        setLoading(button, false);

        if (result.ok) {
            closeModal('intro-screen-modal');
            load();
        } else if (result.status === 422) {
            setFieldErrors(form, result.data.errors);
        } else {
            showBanner('#intro-screen-modal-banner', result.data.message || 'Something went wrong.');
        }
    });

    load();
}

// ═══════════════════════════════════════════════════════════════════════════
// Page: cms/faqs
// ═══════════════════════════════════════════════════════════════════════════

function initCmsFaqsPage() {
    const tbody = qs('#faqs-table-body');
    if (!tbody) return;

    async function load() {
        tbody.innerHTML = loadingRow(4);
        const result = await apiRequest('get', '/admin/faqs');
        if (!result.ok) return;

        const faqs = result.data.data;
        tbody.innerHTML = faqs.length ? faqs.map((f) => `
            <tr class="border-b border-zinc-100 dark:border-zinc-800/70 table-row-motion">
                <td class="py-3 px-4 text-sm font-medium">${escapeHtml(f.question_en)}<br><span class="text-zinc-400">${escapeHtml(f.question_ar)}</span></td>
                <td class="py-3 px-4 text-sm">${f.order}</td>
                <td class="py-3 px-4">${f.is_active ? badgeHtml('Active', 'green') : badgeHtml('Inactive', 'zinc')}</td>
                <td class="py-3 px-4 text-right text-sm">
                    <button data-action="edit" data-id="${f.id}" class="link-action">Edit</button>
                    <button data-action="delete" data-id="${f.id}" class="ml-3 link-action-danger">Delete</button>
                </td>
            </tr>
        `).join('') : emptyRow(4, 'No FAQs yet.');
    }

    function openFaqModal(faq = null) {
        const form = qs('#faq-form');
        form.reset();
        clearFieldErrors(form);
        hideBanner('#faq-modal-banner');
        qs('#faq-id').value = faq?.id || '';
        qs('#faq_question_ar').value = faq?.question_ar || '';
        qs('#faq_question_en').value = faq?.question_en || '';
        qs('#faq_answer_ar').value = faq?.answer_ar || '';
        qs('#faq_answer_en').value = faq?.answer_en || '';
        qs('#faq_order').value = faq?.order ?? 0;
        qs('#faq_is_active').checked = faq ? !!faq.is_active : true;
        qs('#faq-modal-title').textContent = faq ? 'Edit FAQ' : 'New FAQ';
        openModal('faq-modal');
    }

    qs('#new-faq-button').addEventListener('click', () => openFaqModal());

    tbody.addEventListener('click', async (event) => {
        const button = event.target.closest('button[data-action]');
        if (!button) return;
        const id = button.dataset.id;

        if (button.dataset.action === 'edit') {
            const result = await apiRequest('get', `/admin/faqs/${id}`);
            if (result.ok) openFaqModal(result.data.data);
        }

        if (button.dataset.action === 'delete') {
            confirmAndRun('Delete this FAQ?', async () => {
                await apiRequest('delete', `/admin/faqs/${id}`);
                load();
            });
        }
    });

    qs('#faq-form').addEventListener('submit', async (event) => {
        event.preventDefault();
        const id = qs('#faq-id').value;
        const form = event.target;
        const button = qs('button[type="submit"]', form);
        setLoading(button, true);
        clearFieldErrors(form);

        const payload = {
            question_ar: qs('#faq_question_ar').value,
            question_en: qs('#faq_question_en').value,
            answer_ar: qs('#faq_answer_ar').value,
            answer_en: qs('#faq_answer_en').value,
            order: qs('#faq_order').value || 0,
            is_active: qs('#faq_is_active').checked,
        };

        const result = id
            ? await apiRequest('put', `/admin/faqs/${id}`, payload)
            : await apiRequest('post', '/admin/faqs', payload);

        setLoading(button, false);

        if (result.ok) {
            closeModal('faq-modal');
            load();
        } else if (result.status === 422) {
            setFieldErrors(form, result.data.errors);
        } else {
            showBanner('#faq-modal-banner', result.data.message || 'Something went wrong.');
        }
    });

    load();
}

// ═══════════════════════════════════════════════════════════════════════════
// Page: cms/terms & cms/privacy-policy (singleton content pages)
// ═══════════════════════════════════════════════════════════════════════════

function initCmsSingletonPage({ pageEl, endpoint, contentArId, contentEnId, formId, bannerSelector }) {
    const page = qs(pageEl);
    if (!page) return;

    async function load() {
        const result = await apiRequest('get', endpoint);
        if (!result.ok) return;
        qs(contentArId).value = result.data.data.content_ar || '';
        qs(contentEnId).value = result.data.data.content_en || '';
    }

    qs(formId).addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.target;
        const button = qs('button[type="submit"]', form);
        setLoading(button, true);
        clearFieldErrors(form);
        hideBanner(bannerSelector);

        const result = await apiRequest('put', endpoint, {
            content_ar: qs(contentArId).value,
            content_en: qs(contentEnId).value,
        });

        setLoading(button, false);

        if (result.ok) {
            showBanner(bannerSelector, 'Saved successfully.', 'success');
        } else if (result.status === 422) {
            setFieldErrors(form, result.data.errors);
        } else {
            showBanner(bannerSelector, result.data.message || 'Something went wrong.');
        }
    });

    load();
}

// ═══════════════════════════════════════════════════════════════════════════
// Page: chats (view-only)
// ═══════════════════════════════════════════════════════════════════════════

function initChatsPage() {
    const list = qs('#chats-list');
    if (!list) return;

    let listPage = 1;
    let activeChatId = null;
    let threadPage = 1;
    let threadHasMore = false;

    function chatLabel(chat) {
        const providerName = chat.provider?.business_name || chat.provider?.user?.name || 'Provider';
        return `${chat.client?.name || 'Client'} ↔ ${providerName}`;
    }

    function messagePreview(chat) {
        if (!chat.last_message) return 'No messages yet';
        if (chat.last_message.media_type === 'text') return chat.last_message.message || '';
        return `[${chat.last_message.media_type}]`;
    }

    function renderAttachment(message) {
        if (message.media_type === 'image') return `<img src="${message.media_url}" class="mt-1 max-h-48 rounded-lg">`;
        if (message.media_type === 'audio') return `<audio controls src="${message.media_url}" class="mt-1 max-w-full"></audio>`;
        return `<a href="${message.media_url}" target="_blank" class="underline">Attachment</a>`;
    }

    async function loadChats() {
        list.innerHTML = `${loadingIndicatorHtml()}`;
        const result = await apiRequest('get', '/admin/chats', {
            page: listPage,
            search: qs('#chats-search').value || undefined,
        });

        if (!result.ok) {
            list.innerHTML = errorIndicatorHtml('Failed to load chats.');
            return;
        }

        const chats = result.data.data;
        list.innerHTML = chats.length ? chats.map((chat) => `
            <button type="button" data-chat-id="${chat.id}" data-label="${escapeHtml(chatLabel(chat))}"
                class="chat-row animate-fade-up block w-full border-l-2 border-transparent px-4 py-3 text-left transition-colors duration-150 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 ${activeChatId == chat.id ? 'border-accent-600 bg-accent-50 dark:bg-accent-950/20' : ''}">
                <div class="flex items-center justify-between gap-2">
                    <p class="truncate text-sm font-medium">${escapeHtml(chatLabel(chat))}</p>
                    <span class="flex-shrink-0 text-xs text-zinc-400">${formatDate(chat.last_message_at)}</span>
                </div>
                <p class="mt-1 truncate text-xs text-zinc-500 dark:text-zinc-400">${escapeHtml(messagePreview(chat))}</p>
            </button>
        `).join('') : `<div class="p-4 text-center text-sm text-zinc-500">No chats found.</div>`;

        renderPagination(qs('#chats-pagination'), result.data.meta, (p) => { listPage = p; loadChats(); });
    }

    async function loadThread(chatId, label, page = 1) {
        activeChatId = chatId;
        qsa('.chat-row', list).forEach((row) => {
            const isActive = row.dataset.chatId == chatId;
            row.classList.toggle('bg-accent-50', isActive);
            row.classList.toggle('dark:bg-accent-950/20', isActive);
            row.classList.toggle('border-accent-600', isActive);
        });

        qs('#chat-thread-header').innerHTML = `<p class="text-sm font-semibold">${escapeHtml(label)}</p>`;
        const container = qs('#chat-thread-messages');
        container.innerHTML = `<p class="text-center text-sm">${loadingIndicatorHtml()}</p>`;

        const result = await apiRequest('get', `/admin/chats/${chatId}/messages`, { page });
        if (!result.ok) {
            container.innerHTML = `<p class="text-center text-sm">${errorIndicatorHtml('Failed to load messages.')}</p>`;
            return;
        }

        threadPage = result.data.meta.current_page;
        threadHasMore = result.data.meta.current_page < result.data.meta.last_page;

        const messages = result.data.data.slice().reverse();
        const bubbles = messages.map((m) => `
            <div class="animate-fade-up max-w-[75%] rounded-2xl px-4 py-2 text-sm ${m.sender?.user_type === 'provider' ? 'ml-auto bg-accent-600 text-white shadow-lg shadow-accent-600/20' : 'bg-zinc-100 dark:bg-zinc-800'}">
                <p class="mb-0.5 text-xs font-medium opacity-70">${escapeHtml(m.sender?.name)}</p>
                ${m.media_type === 'text' ? `<p>${escapeHtml(m.message)}</p>` : renderAttachment(m)}
                <p class="mt-1 text-right text-[10px] opacity-60">${new Date(m.created_at).toLocaleString()}</p>
            </div>
        `).join('');

        const loadOlderButton = threadHasMore
            ? `<button id="load-older-messages" class="mx-auto block text-xs link-action">Load older messages</button>`
            : '';

        container.innerHTML = messages.length ? loadOlderButton + bubbles : `<p class="text-center text-sm text-zinc-500">No messages in this chat yet.</p>`;

        if (!loadOlderButton) container.scrollTop = container.scrollHeight;

        qs('#load-older-messages', container)?.addEventListener('click', () => loadThread(chatId, label, threadPage + 1));
    }

    list.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-chat-id]');
        if (!button) return;
        loadThread(button.dataset.chatId, button.dataset.label);
    });

    qs('#chats-search').addEventListener('input', debounce(() => { listPage = 1; loadChats(); }));

    loadChats();
}

// ═══════════════════════════════════════════════════════════════════════════
// Page: support-tickets
// ═══════════════════════════════════════════════════════════════════════════

function initSupportTicketsPage() {
    const list = qs('#tickets-list');
    if (!list) return;

    let listPage = 1;
    let activeTicketId = null;
    let activeTicketStatus = null;

    function statusBadge(status) {
        return status === 'closed' ? badgeHtml('Closed', 'zinc') : badgeHtml('Open', 'accent');
    }

    function renderAttachment(item) {
        if (!item.attachment_url) return '';
        if (item.attachment_type === 'image') return `<img src="${item.attachment_url}" class="mt-1 max-h-48 rounded-lg">`;
        if (item.attachment_type === 'video') return `<video src="${item.attachment_url}" controls class="mt-1 max-h-48 rounded-lg"></video>`;
        return `<a href="${item.attachment_url}" target="_blank" class="underline">Attachment</a>`;
    }

    async function loadTickets() {
        list.innerHTML = `${loadingIndicatorHtml()}`;
        const result = await apiRequest('get', '/admin/support-tickets', {
            page: listPage,
            search: qs('#tickets-search').value || undefined,
            status: qs('#tickets-status-filter').value || undefined,
        });

        if (!result.ok) {
            list.innerHTML = errorIndicatorHtml('Failed to load tickets.');
            return;
        }

        const tickets = result.data.data;
        list.innerHTML = tickets.length ? tickets.map((ticket) => `
            <button type="button" data-ticket-id="${ticket.id}"
                class="ticket-row animate-fade-up block w-full border-l-2 border-transparent px-4 py-3 text-left transition-colors duration-150 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 ${activeTicketId == ticket.id ? 'border-accent-600 bg-accent-50 dark:bg-accent-950/20' : ''}">
                <div class="flex items-center justify-between gap-2">
                    <p class="truncate text-sm font-medium">${escapeHtml(ticket.subject)}</p>
                    ${statusBadge(ticket.status)}
                </div>
                <p class="mt-1 truncate text-xs text-zinc-500 dark:text-zinc-400">${escapeHtml(ticket.user?.name || 'User')} &middot; ${formatDate(ticket.created_at)}</p>
            </button>
        `).join('') : `<div class="p-4 text-center text-sm text-zinc-500">No tickets found.</div>`;

        renderPagination(qs('#tickets-pagination'), result.data.meta, (p) => { listPage = p; loadTickets(); });
    }

    function renderComposer(ticketId) {
        const composer = qs('#ticket-thread-composer');
        if (activeTicketStatus === 'closed') {
            composer.innerHTML = `<p class="text-center text-sm text-zinc-500">This ticket is closed. Reopen it to add a reply.</p>`;
            return;
        }

        composer.innerHTML = `
            <form id="ticket-reply-form" class="space-y-2">
                <textarea id="ticket-reply-message" rows="2" placeholder="Write a reply..."
                    class="w-full input-field-sm"></textarea>
                <div class="flex items-center justify-between gap-2">
                    <input id="ticket-reply-attachment" type="file" class="text-xs">
                    <button type="submit" class="btn btn-primary">
                        <span data-label-idle>Send</span>
                        <span data-label-busy class="hidden">Sending...</span>
                    </button>
                </div>
            </form>
        `;

        qs('#ticket-reply-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const button = qs('button[type="submit"]', event.target);
            const message = qs('#ticket-reply-message').value;
            const file = qs('#ticket-reply-attachment').files[0];
            if (!message && !file) return;

            const formData = new FormData();
            if (message) formData.append('message', message);
            if (file) formData.append('attachment', file);

            setLoading(button, true);
            const result = await apiRequest('post', `/admin/support-tickets/${ticketId}/replies`, formData, { isMultipart: true });
            setLoading(button, false);

            if (result.ok) {
                loadThread(ticketId);
            } else {
                alert(result.data.message || 'Failed to send reply.');
            }
        });
    }

    async function loadThread(ticketId) {
        activeTicketId = ticketId;
        qsa('.ticket-row', list).forEach((row) => {
            const isActive = row.dataset.ticketId == ticketId;
            row.classList.toggle('bg-accent-50', isActive);
            row.classList.toggle('dark:bg-accent-950/20', isActive);
            row.classList.toggle('border-accent-600', isActive);
        });

        const header = qs('#ticket-thread-header');
        const container = qs('#ticket-thread-messages');
        header.innerHTML = `<p class="text-sm">${loadingIndicatorHtml()}</p>`;
        container.innerHTML = `<p class="text-center text-sm">${loadingIndicatorHtml()}</p>`;
        qs('#ticket-thread-composer').innerHTML = '';

        const [ticketResult, repliesResult] = await Promise.all([
            apiRequest('get', `/admin/support-tickets/${ticketId}`),
            apiRequest('get', `/admin/support-tickets/${ticketId}/replies`),
        ]);

        if (!ticketResult.ok || !repliesResult.ok) {
            container.innerHTML = `<p class="text-center text-sm">${errorIndicatorHtml('Failed to load ticket.')}</p>`;
            return;
        }

        const ticket = ticketResult.data.data;
        activeTicketStatus = ticket.status;

        header.innerHTML = `
            <div>
                <p class="text-sm font-semibold">${escapeHtml(ticket.subject)}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">${escapeHtml(ticket.user?.name || 'User')} (${escapeHtml(ticket.user?.user_type || '')})</p>
            </div>
            <div class="flex items-center gap-2">
                ${statusBadge(ticket.status)}
                <button id="ticket-toggle-status" data-action="${ticket.status === 'closed' ? 'reopen' : 'close'}"
                    class="btn btn-secondary px-3 py-1.5 text-xs">
                    ${ticket.status === 'closed' ? 'Reopen' : 'Close'}
                </button>
            </div>
        `;

        qs('#ticket-toggle-status').addEventListener('click', async (event) => {
            const action = event.currentTarget.dataset.action;
            const result = await apiRequest('patch', `/admin/support-tickets/${ticketId}/${action}`);
            if (result.ok) {
                loadThread(ticketId);
                loadTickets();
            } else {
                alert(result.data.message || 'Action failed.');
            }
        });

        const replies = repliesResult.data.data.slice().reverse();
        const descriptionBubble = `
            <div class="animate-fade-up max-w-[85%] rounded-2xl bg-zinc-100 px-4 py-2 text-sm dark:bg-zinc-800">
                <p class="mb-0.5 text-xs font-medium opacity-70">${escapeHtml(ticket.user?.name)}</p>
                <p>${escapeHtml(ticket.description)}</p>
                ${renderAttachment(ticket)}
                <p class="mt-1 text-right text-[10px] opacity-60">${new Date(ticket.created_at).toLocaleString()}</p>
            </div>
        `;
        const bubbles = replies.map((r) => `
            <div class="animate-fade-up max-w-[85%] rounded-2xl px-4 py-2 text-sm ${r.sender?.user_type === 'admin' ? 'ml-auto bg-accent-600 text-white shadow-lg shadow-accent-600/20' : 'bg-zinc-100 dark:bg-zinc-800'}">
                <p class="mb-0.5 text-xs font-medium opacity-70">${escapeHtml(r.sender?.name)}</p>
                ${r.message ? `<p>${escapeHtml(r.message)}</p>` : ''}
                ${renderAttachment(r)}
                <p class="mt-1 text-right text-[10px] opacity-60">${new Date(r.created_at).toLocaleString()}</p>
            </div>
        `).join('');

        container.innerHTML = descriptionBubble + bubbles;
        container.scrollTop = container.scrollHeight;

        renderComposer(ticketId);
    }

    list.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-ticket-id]');
        if (!button) return;
        loadThread(button.dataset.ticketId);
    });

    qs('#tickets-search').addEventListener('input', debounce(() => { listPage = 1; loadTickets(); }));
    qs('#tickets-status-filter').addEventListener('change', () => { listPage = 1; loadTickets(); });

    loadTickets();
}

// ═══════════════════════════════════════════════════════════════════════════
// Page: notifications/send
// ═══════════════════════════════════════════════════════════════════════════

function initNotificationsSendPage() {
    const form = qs('#send-notification-form');
    if (!form) return;

    const sendToAllToggle = qs('#send_to_all');
    const userIdsWrapper = qs('#notification-user-ids-wrapper');

    function syncUserIdsVisibility() {
        userIdsWrapper.classList.toggle('hidden', sendToAllToggle.checked);
    }
    sendToAllToggle.addEventListener('change', syncUserIdsVisibility);
    syncUserIdsVisibility();

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = qs('button[type="submit"]', form);
        setLoading(button, true);
        clearFieldErrors(form);
        hideBanner('#send-notification-banner');

        const payload = {
            title: qs('#notification_title').value,
            body: qs('#notification_description').value,
            icon: qs('#notification_icon').value || undefined,
            type: qs('#notification_action').value,
            type_id: qs('#notification_action_id').value || undefined,
        };

        if (!sendToAllToggle.checked) {
            payload.user_ids = qs('#notification_user_ids').value
                .split(',')
                .map((id) => id.trim())
                .filter(Boolean)
                .map(Number);
        }

        const result = await apiRequest('post', '/admin/notifications/send', payload);
        setLoading(button, false);

        if (result.ok) {
            showBanner('#send-notification-banner', `Notification sent to ${result.data.data.sent_to} user(s).`, 'success');
            form.reset();
            syncUserIdsVisibility();
        } else if (result.status === 422) {
            setFieldErrors(form, result.data.errors);
        } else {
            showBanner('#send-notification-banner', result.data.message || 'Something went wrong.');
        }
    });
}

// ═══════════════════════════════════════════════════════════════════════════
// Page: notifications (index/filters)
// ═══════════════════════════════════════════════════════════════════════════

function initNotificationsIndexPage() {
    const tbody = qs('#notifications-table-body');
    if (!tbody) return;

    let page = 1;

    const actionLabels = {
        service_request: 'Service Request',
        payment: 'Payment',
        chat: 'Chat',
        system: 'System',
    };

    function renderIcon(icon) {
        if (!icon) return '<div class="h-8 w-8 rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>';
        if (icon.startsWith('http')) return `<img src="${icon}" class="h-8 w-8 rounded-lg object-cover">`;
        return `<i class="ph ph-${escapeHtml(icon)} text-lg"></i>`;
    }

    async function load() {
        tbody.innerHTML = loadingRow(7);
        const result = await apiRequest('get', '/admin/notifications', {
            page,
            user_id: qs('#notifications-user-id-filter').value || undefined,
            type: qs('#notifications-action-filter').value || undefined,
            is_read: qs('#notifications-read-filter').value || undefined,
        });

        if (!result.ok) {
            tbody.innerHTML = errorRow(7, 'Failed to load notifications.');
            return;
        }

        const notifications = result.data.data;
        tbody.innerHTML = notifications.length ? notifications.map((n) => `
            <tr class="border-b border-zinc-100 dark:border-zinc-800/70 table-row-motion">
                <td class="py-3 px-4">${renderIcon(n.icon)}</td>
                <td class="py-3 px-4 text-sm font-medium">${escapeHtml(n.title)}</td>
                <td class="py-3 px-4 max-w-xs truncate text-sm text-zinc-500 dark:text-zinc-400">${escapeHtml(n.description)}</td>
                <td class="py-3 px-4 text-sm">${actionLabels[n.action] || escapeHtml(n.action)}</td>
                <td class="py-3 px-4 text-sm">${escapeHtml(n.user?.name || '—')}</td>
                <td class="py-3 px-4 text-sm text-zinc-500 dark:text-zinc-400">${formatDate(n.timestamp)}</td>
                <td class="py-3 px-4">${n.is_read ? badgeHtml('Read', 'green') : badgeHtml('Unread', 'zinc')}</td>
            </tr>
        `).join('') : emptyRow(7, 'No notifications found.');

        renderPagination(qs('#notifications-pagination'), result.data.meta, (p) => { page = p; load(); });
    }

    qs('#notifications-user-id-filter').addEventListener('input', debounce(() => { page = 1; load(); }));
    qs('#notifications-action-filter').addEventListener('change', () => { page = 1; load(); });
    qs('#notifications-read-filter').addEventListener('change', () => { page = 1; load(); });

    load();
}

// ─── Dispatcher ─────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    if (!requireAdminAuth()) return;

    initLogout();
    wireModalDismiss();
    initFilePreviews();
    initMobileNav();
    initSpotlight();

    switch (document.body.dataset.page) {
        case 'users-clients':
            initUsersClientsPage();
            break;
        case 'users-providers':
            initUsersProvidersPage();
            break;
        case 'categories':
            initCategoriesPage();
            break;
        case 'chats':
            initChatsPage();
            break;
        case 'support-tickets':
            initSupportTicketsPage();
            break;
        case 'notifications-send':
            initNotificationsSendPage();
            break;
        case 'notifications-index':
            initNotificationsIndexPage();
            break;
        case 'locations-countries':
            initLocationsCountriesPage();
            break;
        case 'locations-cities':
            initLocationsCitiesPage();
            break;
        case 'locations-areas':
            initLocationsAreasPage();
            break;
        case 'cms-intro-screens':
            initCmsIntroScreensPage();
            break;
        case 'cms-faqs':
            initCmsFaqsPage();
            break;
        case 'cms-terms':
            initCmsSingletonPage({
                pageEl: '[data-page="cms-terms"]',
                endpoint: '/admin/terms-and-conditions',
                contentArId: '#terms_content_ar',
                contentEnId: '#terms_content_en',
                formId: '#terms-form',
                bannerSelector: '#terms-banner',
            });
            break;
        case 'cms-privacy-policy':
            initCmsSingletonPage({
                pageEl: '[data-page="cms-privacy-policy"]',
                endpoint: '/admin/privacy-policy',
                contentArId: '#privacy_content_ar',
                contentEnId: '#privacy_content_en',
                formId: '#privacy-form',
                bannerSelector: '#privacy-banner',
            });
            break;
        default:
            break;
    }
});
