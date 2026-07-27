const storedTheme = window.localStorage.getItem('gymflow-theme');
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
const theme = storedTheme ?? (prefersDark ? 'dark' : 'light');

const applyTheme = (nextTheme) => {
    document.documentElement.dataset.theme = nextTheme;
    window.localStorage.setItem('gymflow-theme', nextTheme);

    document.querySelectorAll('[data-theme-icon]').forEach((icon) => {
        icon.textContent = nextTheme === 'dark' ? 'Light' : 'Dark';
    });
};

applyTheme(theme);

document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
    applyTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark');
});
