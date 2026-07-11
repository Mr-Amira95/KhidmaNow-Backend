export function initThemeToggle(root = document) {
    const button = root.querySelector('#theme-toggle');
    if (!button) return;

    button.addEventListener('click', () => {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });
}
