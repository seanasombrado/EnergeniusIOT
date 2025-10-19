// theme.js
// Define setTheme globally
function setTheme(theme) {
    const themeToggleDarkIcon = document.getElementById('theme-dark-btn');
    const themeToggleLightIcon = document.getElementById('theme-light-btn');
    // 💡 NEW: Get the system button element (needed for ring highlight)
    const themeToggleSystemIcon = document.getElementById('theme-system-btn'); 

    let actualTheme = theme;
    
    // 💡 CRITICAL FIX: Add logic to resolve 'system' to 'dark' or 'light'
    if (theme === 'system') {
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            actualTheme = 'dark';
        } else {
            actualTheme = 'light';
        }
    }

    // Apply the actual theme (dark/light) to the HTML tag
    if (actualTheme === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    
    // 💡 CRITICAL FIX: Always store the chosen preference ('light', 'dark', or 'system')
    localStorage.setItem('color-theme', theme); 

    // 💡 NEW LOGIC: Remove ring from all buttons first
    [themeToggleLightIcon, themeToggleDarkIcon, themeToggleSystemIcon].forEach(btn => {
        btn?.classList.remove('ring-2', 'ring-blue-500');
    });

    // Apply ring highlight based on the CHOSEN PREFERENCE (theme)
    if (theme === 'light') {
        themeToggleLightIcon?.classList.add('ring-2', 'ring-blue-500');
    } else if (theme === 'dark') {
        themeToggleDarkIcon?.classList.add('ring-2', 'ring-blue-500');
    } else if (theme === 'system') {
        themeToggleSystemIcon?.classList.add('ring-2', 'ring-blue-500');
    }
}
document.addEventListener('DOMContentLoaded', () => {
    // Add transition classes
    const elements = document.querySelectorAll('h1, h2, h3, h4, h5, h6, p, span, label, input, button');
    elements.forEach(element => {
        element.classList.add('transition-colors', 'duration-200');
    });

    // 💡 CRITICAL FIX: The initial setting logic must use the stored preference, 
    // or default to 'system' if nothing is stored.
    const storedTheme = localStorage.getItem('color-theme');

    if (storedTheme) {
        setTheme(storedTheme); 
    } else {
        // If nothing is saved, default to system theme
        setTheme('system');
    }

    // Initial layout setting (New logic)
    const initialLayout = localStorage.getItem('dashboard-layout') || 'default';
    // Assuming setLayout is defined and correct
    // setLayout(initialLayout); 

    // Add click handlers for admin_settings.php buttons
    document.getElementById('theme-light-btn')?.addEventListener('click', () => setTheme('light'));
    document.getElementById('theme-dark-btn')?.addEventListener('click', () => setTheme('dark'));
    // 💡 CRITICAL FIX: Add click handler for the new system button
    document.getElementById('theme-system-btn')?.addEventListener('click', () => setTheme('system'));
});