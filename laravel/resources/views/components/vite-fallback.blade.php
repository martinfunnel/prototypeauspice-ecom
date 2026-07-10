@php
$cssPath = public_path('build/assets/app.css');
$jsPath  = public_path('build/assets/app.js');
$cssExists = file_exists($cssPath);
$jsExists  = file_exists($jsPath);
$cssVer = $cssExists ? filemtime($cssPath) : time();
$jsVer  = $jsExists  ? filemtime($jsPath)  : time();
@endphp

@if($cssExists)
<link rel="stylesheet" href="/build/assets/app.css?v={{ $cssVer }}" />
@else
{{-- Fallback CDN : variables CSS pour les couleurs personnalisées --}}
<style>
:root {
    --color-background: #f8fafc;
    --color-foreground: #1e293b;
    --color-card: #ffffff;
    --color-card-foreground: #1e293b;
    --color-popover: #ffffff;
    --color-popover-foreground: #1e293b;
    --color-primary: #3b82f6;
    --color-primary-foreground: #f8fafc;
    --color-secondary: #f1f5f9;
    --color-secondary-foreground: #334155;
    --color-muted: #f1f5f9;
    --color-muted-foreground: #64748b;
    --color-accent: #2e7d4a;
    --color-accent-foreground: #ffffff;
    --color-destructive: #c94a4a;
    --color-destructive-foreground: #ffffff;
    --color-success: #2e7d4a;
    --color-success-foreground: #ffffff;
    --color-warning: #d4a843;
    --color-warning-foreground: #1e293b;
    --color-border: #e2e8f0;
    --color-input: #e2e8f0;
    --color-ring: #4caf7a;
    --color-sidebar: #1e293b;
    --color-sidebar-foreground: #f8fafc;
    --color-sidebar-primary: #2e7d4a;
    --color-sidebar-primary-foreground: #ffffff;
    --color-sidebar-accent: #334155;
    --color-sidebar-accent-foreground: #f8fafc;
    --color-sidebar-border: #334155;
    --color-sidebar-ring: #2e7d4a;
    --font-sans: "DM Sans", system-ui, sans-serif;
    --font-display: "Space Grotesk", system-ui, sans-serif;
    --shadow-card: 0 1px 3px rgba(59,130,246,0.08), 0 4px 16px rgba(59,130,246,0.06);
    --shadow-accent: 0 8px 24px -8px rgba(46,125,74,0.4);
}
.bg-background { background-color: var(--color-background) !important; }
.bg-foreground { background-color: var(--color-foreground) !important; }
.bg-card { background-color: var(--color-card) !important; }
.bg-card-foreground { background-color: var(--color-card-foreground) !important; }
.bg-popover { background-color: var(--color-popover) !important; }
.bg-popover-foreground { background-color: var(--color-popover-foreground) !important; }
.bg-primary { background-color: var(--color-primary) !important; }
.bg-primary-foreground { background-color: var(--color-primary-foreground) !important; }
.bg-secondary { background-color: var(--color-secondary) !important; }
.bg-secondary-foreground { background-color: var(--color-secondary-foreground) !important; }
.bg-muted { background-color: var(--color-muted) !important; }
.bg-muted-foreground { background-color: var(--color-muted-foreground) !important; }
.bg-accent { background-color: var(--color-accent) !important; }
.bg-accent-foreground { background-color: var(--color-accent-foreground) !important; }
.bg-destructive { background-color: var(--color-destructive) !important; }
.bg-destructive-foreground { background-color: var(--color-destructive-foreground) !important; }
.bg-success { background-color: var(--color-success) !important; }
.bg-success-foreground { background-color: var(--color-success-foreground) !important; }
.bg-warning { background-color: var(--color-warning) !important; }
.bg-warning-foreground { background-color: var(--color-warning-foreground) !important; }
.bg-sidebar { background-color: var(--color-sidebar) !important; }
.bg-sidebar-foreground { background-color: var(--color-sidebar-foreground) !important; }
.bg-sidebar-primary { background-color: var(--color-sidebar-primary) !important; }
.bg-sidebar-primary-foreground { background-color: var(--color-sidebar-primary-foreground) !important; }
.bg-sidebar-accent { background-color: var(--color-sidebar-accent) !important; }
.bg-sidebar-accent-foreground { background-color: var(--color-sidebar-accent-foreground) !important; }
.text-background { color: var(--color-background) !important; }
.text-foreground { color: var(--color-foreground) !important; }
.text-card { color: var(--color-card) !important; }
.text-card-foreground { color: var(--color-card-foreground) !important; }
.text-popover { color: var(--color-popover) !important; }
.text-popover-foreground { color: var(--color-popover-foreground) !important; }
.text-primary { color: var(--color-primary) !important; }
.text-primary-foreground { color: var(--color-primary-foreground) !important; }
.text-secondary { color: var(--color-secondary) !important; }
.text-secondary-foreground { color: var(--color-secondary-foreground) !important; }
.text-muted { color: var(--color-muted) !important; }
.text-muted-foreground { color: var(--color-muted-foreground) !important; }
.text-accent { color: var(--color-accent) !important; }
.text-accent-foreground { color: var(--color-accent-foreground) !important; }
.text-destructive { color: var(--color-destructive) !important; }
.text-destructive-foreground { color: var(--color-destructive-foreground) !important; }
.text-success { color: var(--color-success) !important; }
.text-success-foreground { color: var(--color-success-foreground) !important; }
.text-warning { color: var(--color-warning) !important; }
.text-warning-foreground { color: var(--color-warning-foreground) !important; }
.text-sidebar { color: var(--color-sidebar) !important; }
.text-sidebar-foreground { color: var(--color-sidebar-foreground) !important; }
.text-sidebar-primary { color: var(--color-sidebar-primary) !important; }
.text-sidebar-primary-foreground { color: var(--color-sidebar-primary-foreground) !important; }
.text-sidebar-accent { color: var(--color-sidebar-accent) !important; }
.text-sidebar-accent-foreground { color: var(--color-sidebar-accent-foreground) !important; }
.border-border { border-color: var(--color-border) !important; }
.border-input { border-color: var(--color-input) !important; }
.border-ring { border-color: var(--color-ring) !important; }
.border-sidebar-border { border-color: var(--color-sidebar-border) !important; }
.shadow-card { box-shadow: var(--shadow-card) !important; }
.shadow-accent { box-shadow: var(--shadow-accent) !important; }
.font-sans { font-family: var(--font-sans) !important; }
.font-display { font-family: var(--font-display) !important; }

/* ---- Animations globales ---- */
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-12px) scale(0.98); }
    to   { opacity: 1; transform: translateY(0)     scale(1); }
}
@keyframes slideUp {
    from { opacity: 1; transform: translateY(0)     scale(1); }
    to   { opacity: 0; transform: translateY(-12px) scale(0.98); }
}
@keyframes fadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes scaleIn {
    from { opacity: 0; transform: scale(0.95); }
    to   { opacity: 1; transform: scale(1); }
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
@keyframes pulse-ring {
    0%   { transform: scale(0.8); opacity: 1; }
    100% { transform: scale(2.4); opacity: 0; }
}
@keyframes shimmer {
    0%   { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

/* Inline panel open / close */
.detail-panel, .edit-panel {
    animation: slideDown 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    transform-origin: top center;
}

/* Row hover lift */
.product-row, .role-row, .log-row, .manager-row {
    transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
}
.product-row:hover, .role-row:hover, .log-row:hover, .manager-row:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.08);
}

/* Button press */
button, [type="button"], [type="submit"] {
    transition: transform 0.1s ease, opacity 0.15s ease;
}
button:active, [type="button"]:active, [type="submit"]:active {
    transform: scale(0.96);
}

/* Card entrance stagger */
.card-enter {
    animation: fadeInUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) backwards;
}

/* Page loading overlay */
.page-loader {
    position: absolute;
    inset: 0;
    z-index: 50;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: var(--color-background);
    transition: opacity 0.4s ease, visibility 0.4s ease;
}
.page-loader.hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}
.page-loader .loader-ring {
    position: relative;
    width: 64px;
    height: 64px;
}
.page-loader .loader-ring::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 3px solid var(--color-border);
}
.page-loader .loader-ring::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 3px solid transparent;
    border-top-color: var(--color-accent);
    animation: spin 0.8s linear infinite;
}
.page-loader .loader-pulse {
    position: absolute;
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: var(--color-accent);
    opacity: 0.15;
    animation: pulse-ring 1.5s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
}
.page-loader .loader-text {
    margin-top: 1.5rem;
    font-family: var(--font-display);
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--color-muted-foreground);
    letter-spacing: 0.05em;
}
.page-loader .loader-bar {
    margin-top: 0.75rem;
    width: 120px;
    height: 3px;
    border-radius: 999px;
    background: var(--color-border);
    overflow: hidden;
}
.page-loader .loader-bar::after {
    content: '';
    display: block;
    width: 40%;
    height: 100%;
    border-radius: 999px;
    background: var(--color-accent);
    animation: shimmer 1.2s linear infinite;
    background-size: 200% 100%;
}

/* Toast notification */
.toast-enter {
    animation: slideDown 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.toast-exit {
    animation: slideUp 0.2s ease forwards;
}

/* Form inputs focus glow */
input:focus, select:focus, textarea:focus {
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
    box-shadow: 0 0 0 3px rgba(76, 175, 122, 0.15);
}

/* Table row entrance */
tr {
    animation: fadeIn 0.2s ease backwards;
}

/* Gradient utilities */
.gradient-hero { background-image: linear-gradient(135deg, #3b82f6, #2563eb); }
.gradient-accent { background-image: linear-gradient(135deg, #5cc78a, #4caf7a); }
.gradient-soft { background-image: linear-gradient(180deg, #f8fafc, #e8f5e9); }

/* Base styles */
* { border-color: var(--color-border); }
html { font-family: var(--font-sans); }
body {
    background-color: var(--color-background);
    color: var(--color-foreground);
    -webkit-font-smoothing: antialiased;
}
h1, h2, h3, h4, h5 { font-family: var(--font-display); letter-spacing: -0.02em; }
</style>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                background: '#f8fafc',
                foreground: '#1e293b',
                card: '#ffffff',
                'card-foreground': '#1e293b',
                popover: '#ffffff',
                'popover-foreground': '#1e293b',
                primary: '#3b82f6',
                'primary-foreground': '#f8fafc',
                secondary: '#f1f5f9',
                'secondary-foreground': '#334155',
                muted: '#f1f5f9',
                'muted-foreground': '#64748b',
                accent: '#2e7d4a',
                'accent-foreground': '#ffffff',
                destructive: '#c94a4a',
                'destructive-foreground': '#ffffff',
                success: '#4caf7a',
                'success-foreground': '#ffffff',
                warning: '#d4a843',
                'warning-foreground': '#1e293b',
                border: '#e2e8f0',
                input: '#e2e8f0',
                ring: '#4caf7a',
                sidebar: {
                    DEFAULT: '#1e293b',
                    foreground: '#f8fafc',
                    primary: '#2e7d4a',
                    'primary-foreground': '#ffffff',
                    accent: '#334155',
                    'accent-foreground': '#f8fafc',
                    border: '#334155',
                    ring: '#2e7d4a',
                },
            },
            fontFamily: {
                sans: ['"DM Sans"', 'system-ui', 'sans-serif'],
                display: ['"Space Grotesk"', 'system-ui', 'sans-serif'],
            },
        }
    }
}
</script>
@endif

@if($jsExists)
<script type="module" src="/build/assets/app.js?v={{ $jsVer }}"></script>
@endif
