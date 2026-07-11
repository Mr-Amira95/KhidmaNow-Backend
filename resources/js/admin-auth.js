import './bootstrap';
import { initThemeToggle } from './theme';

const I18N = window.AUTH_I18N || {};

const BANNER_ERROR_CLASSES = 'mb-5 flex items-start gap-2 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-300';
const BANNER_SUCCESS_CLASSES = 'mb-5 flex items-start gap-2 rounded-lg border border-accent-200 bg-accent-50 px-4 py-3 text-sm text-accent-700 dark:border-accent-900/50 dark:bg-accent-950/30 dark:text-accent-300';

function qs(selector, ctx = document) {
    return ctx.querySelector(selector);
}

function qsa(selector, ctx = document) {
    return Array.from(ctx.querySelectorAll(selector));
}

async function apiPost(path, body) {
    try {
        const response = await window.axios.post(`/api/v1${path}`, body);
        return { ok: true, status: response.status, data: response.data };
    } catch (error) {
        if (error.response) {
            return { ok: false, status: error.response.status, data: error.response.data || {} };
        }
        return { ok: false, status: 0, data: { message: I18N.common?.network_error || 'Could not reach the server. Check your connection and try again.' } };
    }
}

function triggerShake(el) {
    if (!el) return;
    el.classList.remove('animate-shake');
    void el.offsetWidth;
    el.classList.add('animate-shake');
}

function triggerFadeDown(el) {
    if (!el) return;
    el.classList.remove('animate-fade-down');
    void el.offsetWidth;
    el.classList.add('animate-fade-down');
}

function showBanner(message, type = 'error') {
    const banner = qs('#form-banner');
    if (!banner) return;
    banner.className = type === 'error' ? BANNER_ERROR_CLASSES : BANNER_SUCCESS_CLASSES;
    const icon = type === 'error' ? 'ph-warning-circle' : 'ph-check-circle';
    banner.innerHTML = `<i class="ph ${icon} mt-0.5 text-base"></i><span>${message}</span>`;
    triggerFadeDown(banner);
    if (type === 'error') triggerShake(banner);
}

function hideBanner() {
    const banner = qs('#form-banner');
    if (!banner) return;
    banner.className = 'hidden';
    banner.innerHTML = '';
}

function clearFieldErrors(form) {
    qsa('[data-error-for]', form).forEach((el) => {
        el.textContent = '';
        el.classList.add('hidden');
    });
    qsa('input', form).forEach((el) => el.classList.remove('border-rose-400', 'focus:ring-rose-500', 'focus:border-rose-500'));
}

function setFieldErrors(form, errors = {}) {
    Object.entries(errors).forEach(([field, messages]) => {
        const errorEl = qs(`[data-error-for="${field}"]`, form);
        const input = qs(`#${field}`, form);
        const message = Array.isArray(messages) ? messages[0] : messages;
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
            triggerFadeDown(errorEl);
        }
        if (input) {
            input.classList.add('border-rose-400', 'focus:ring-rose-500', 'focus:border-rose-500');
            triggerShake(input.closest('.group') || input);
        }
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

function initPasswordToggles(root = document) {
    qsa('[data-toggle-password]', root).forEach((button) => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-toggle-password');
            const input = qs(`#${targetId}`);
            const icon = qs(`[data-icon-for="${targetId}"]`);
            if (!input) return;
            const willReveal = input.type === 'password';
            input.type = willReveal ? 'text' : 'password';
            button.setAttribute('aria-label', willReveal ? 'Hide password' : 'Show password');
            if (icon) {
                icon.classList.toggle('ph-eye', !willReveal);
                icon.classList.toggle('ph-eye-slash', willReveal);
            }
        });
    });
}

function maskLogin(login) {
    if (!login) return '';
    if (login.includes('@')) {
        const [user, domain] = login.split('@');
        const visible = user.slice(0, Math.min(2, user.length));
        return `${visible}${'*'.repeat(Math.max(user.length - visible.length, 1))}@${domain}`;
    }
    const digits = login.replace(/\s+/g, '');
    if (digits.length <= 4) return digits;
    return `${digits.slice(0, 3)}${'*'.repeat(digits.length - 5)}${digits.slice(-2)}`;
}

function initLoginPage() {
    const form = qs('#login-form');
    if (!form) return;

    const t = I18N.login || {};

    const params = new URLSearchParams(window.location.search);
    if (params.get('reset') === 'success') {
        showBanner(t.reset_success || 'Your password has been reset. Sign in with your new password.', 'success');
    }

    const rememberedLogin = localStorage.getItem('admin_remember_login');
    if (rememberedLogin) {
        const loginInput = qs('#login', form);
        const rememberInput = qs('#remember', form);
        if (loginInput) loginInput.value = rememberedLogin;
        if (rememberInput) rememberInput.checked = true;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        hideBanner();
        clearFieldErrors(form);

        const button = qs('button[type="submit"]', form);
        setLoading(button, true);

        const loginValue = qs('#login', form).value.trim();
        const remember = qs('#remember', form)?.checked;

        const payload = {
            login: loginValue,
            password: qs('#password', form).value,
            device_name: 'Admin Web Portal',
        };

        const result = await apiPost('/login', payload);
        setLoading(button, false);

        if (result.ok) {
            if (remember) {
                localStorage.setItem('admin_remember_login', loginValue);
            } else {
                localStorage.removeItem('admin_remember_login');
            }
            localStorage.setItem('admin_token', result.data.data.token);
            localStorage.setItem('admin_user', JSON.stringify(result.data.data.user));
            window.location.href = '/admin/dashboard';
            return;
        }

        if (result.status === 422) {
            setFieldErrors(form, result.data.errors);
            showBanner(t.fix_fields || 'Please fix the highlighted fields.');
        } else if (result.status === 401) {
            showBanner(t.invalid_credentials || 'That email/phone or password is incorrect.');
        } else if (result.status === 403) {
            showBanner(result.data.message || t.inactive_account || 'This account is inactive. Contact an administrator.');
        } else {
            showBanner(result.data.message || I18N.common?.generic_error || 'Something went wrong. Please try again.');
        }
    });
}

function initForgotPasswordPage() {
    const form = qs('#forgot-password-form');
    if (!form) return;

    const t = I18N.forgot_password || {};
    const panel = qs('#forgot-password-panel');
    const successPanel = qs('#forgot-password-success');
    const successMasked = qs('#success-masked-login');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        hideBanner();
        clearFieldErrors(form);

        const button = qs('button[type="submit"]', form);
        setLoading(button, true);

        const login = qs('#login', form).value.trim();
        const result = await apiPost('/forget-password', { login });
        setLoading(button, false);

        if (result.ok) {
            sessionStorage.setItem('admin_reset_login', login);

            if (panel && successPanel) {
                if (successMasked) successMasked.textContent = maskLogin(login);
                panel.classList.add('hidden');
                successPanel.classList.remove('hidden');
                successPanel.classList.add('animate-scale-in');
                setTimeout(() => {
                    window.location.href = '/admin/verify-code';
                }, 1100);
            } else {
                window.location.href = '/admin/verify-code';
            }
            return;
        }

        if (result.status === 422) {
            setFieldErrors(form, result.data.errors);
        } else if (result.status === 404) {
            showBanner(t.not_found || 'We could not find an account with that email or phone.');
        } else {
            showBanner(result.data.message || I18N.common?.generic_error || 'Something went wrong. Please try again.');
        }
    });
}

function initVerifyCodePage() {
    const form = qs('#verify-code-form');
    if (!form) return;

    const t = I18N.verify_code || {};

    const login = sessionStorage.getItem('admin_reset_login');
    if (!login) {
        window.location.href = '/admin/forgot-password';
        return;
    }

    const maskedTarget = qs('#masked-login');
    if (maskedTarget) maskedTarget.textContent = maskLogin(login);

    const boxes = qsa('.otp-box', form);
    boxes.forEach((box, index) => {
        box.addEventListener('input', () => {
            box.value = box.value.replace(/\D/g, '').slice(-1);
            if (box.value && boxes[index + 1]) boxes[index + 1].focus();
        });
        box.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace' && !box.value && boxes[index - 1]) {
                boxes[index - 1].focus();
            }
        });
        box.addEventListener('paste', (event) => {
            event.preventDefault();
            const digits = (event.clipboardData.getData('text') || '').replace(/\D/g, '').split('');
            boxes.forEach((b, i) => { b.value = digits[i] || ''; });
            const next = boxes[Math.min(digits.length, boxes.length - 1)];
            if (next) next.focus();
        });
    });
    if (boxes[0]) boxes[0].focus();

    let cooldownTimer = null;
    const resendButton = qs('#resend-code');

    function resendLabel(seconds) {
        const template = t.resend_countdown || 'Resend code (:seconds s)';
        return template.replace(':seconds', seconds);
    }

    function startCooldown(seconds = 30) {
        let remaining = seconds;
        if (!resendButton) return;
        resendButton.disabled = true;
        resendButton.textContent = resendLabel(remaining);
        cooldownTimer = setInterval(() => {
            remaining -= 1;
            if (remaining <= 0) {
                clearInterval(cooldownTimer);
                resendButton.disabled = false;
                resendButton.textContent = t.resend || 'Resend code';
                return;
            }
            resendButton.textContent = resendLabel(remaining);
        }, 1000);
    }

    startCooldown();

    if (resendButton) {
        resendButton.addEventListener('click', async () => {
            if (resendButton.disabled) return;
            hideBanner();
            const result = await apiPost('/forget-password', { login });
            if (result.ok) {
                showBanner(t.resend_success || 'A new code has been sent.', 'success');
                startCooldown();
            } else {
                showBanner(result.data.message || t.resend_error || 'Could not resend the code. Try again shortly.');
            }
        });
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        hideBanner();

        const code = boxes.map((box) => box.value).join('');
        if (code.length < boxes.length) {
            showBanner(t.incomplete_code || 'Enter the full 4-digit code.');
            triggerShake(boxes[0]?.parentElement);
            return;
        }

        const button = qs('button[type="submit"]', form);
        setLoading(button, true);

        const result = await apiPost('/verify-code', { login, code });
        setLoading(button, false);

        if (result.ok) {
            sessionStorage.setItem('admin_reset_code', code);
            window.location.href = '/admin/reset-password';
            return;
        }

        showBanner(result.data.message || t.invalid_code || 'That code is invalid or has expired.');
        triggerShake(boxes[0]?.parentElement);
        boxes.forEach((box) => { box.value = ''; });
        if (boxes[0]) boxes[0].focus();
    });
}

function initResetPasswordPage() {
    const form = qs('#reset-password-form');
    if (!form) return;

    const t = I18N.reset_password || {};

    const login = sessionStorage.getItem('admin_reset_login');
    const code = sessionStorage.getItem('admin_reset_code');
    if (!login || !code) {
        window.location.href = '/admin/forgot-password';
        return;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        hideBanner();
        clearFieldErrors(form);

        const password = qs('#password', form).value;
        const confirmation = qs('#password_confirmation', form).value;

        if (password.length < 8) {
            setFieldErrors(form, { password: [t.password_too_short || 'Password must be at least 8 characters.'] });
            return;
        }
        if (password !== confirmation) {
            setFieldErrors(form, { password_confirmation: [t.password_mismatch || 'Passwords do not match.'] });
            return;
        }

        const button = qs('button[type="submit"]', form);
        setLoading(button, true);

        const result = await apiPost('/reset-password', {
            login,
            code,
            password,
            password_confirmation: confirmation,
        });
        setLoading(button, false);

        if (result.ok) {
            sessionStorage.removeItem('admin_reset_login');
            sessionStorage.removeItem('admin_reset_code');
            window.location.href = '/admin/login?reset=success';
            return;
        }

        if (result.status === 422 && result.data.errors) {
            setFieldErrors(form, result.data.errors);
        } else {
            showBanner(result.data.message || t.expired || 'That code has expired. Please request a new one.');
        }
    });
}

function initDashboardPage() {
    if (!localStorage.getItem('admin_token')) {
        window.location.href = '/admin/login';
        return;
    }

    const userEl = qs('#dashboard-user-name');
    const stored = localStorage.getItem('admin_user');
    if (userEl && stored) {
        try {
            const user = JSON.parse(stored);
            userEl.textContent = user.name || user.email || 'Admin';
        } catch (error) {
            /* ignore malformed cache */
        }
    }

    const logoutButton = qs('#logout-button');
    if (!logoutButton) return;
    logoutButton.addEventListener('click', async () => {
        const token = localStorage.getItem('admin_token');
        if (token) {
            window.axios.defaults.headers.common.Authorization = `Bearer ${token}`;
            await apiPost('/logout', {});
        }
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_user');
        window.location.href = '/admin/login';
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initPasswordToggles();
    initThemeToggle();

    switch (document.body.dataset.page) {
        case 'login':
            initLoginPage();
            break;
        case 'forgot-password':
            initForgotPasswordPage();
            break;
        case 'verify-code':
            initVerifyCodePage();
            break;
        case 'reset-password':
            initResetPasswordPage();
            break;
        case 'dashboard':
            initDashboardPage();
            break;
        default:
            break;
    }
});
