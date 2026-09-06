/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './index.html',
    './pricing.html',
    './compare.html',
    './cardiologia.html',
    './security.html',
  ],
  theme: {
    extend: {
      colors: {
        base: 'rgb(var(--c-base) / <alpha-value>)',
        raised: 'rgb(var(--c-raised) / <alpha-value>)',
        elev: 'rgb(var(--c-elev) / <alpha-value>)',
        panel: 'rgb(var(--c-panel) / <alpha-value>)',
        surface: 'rgb(var(--c-surface) / <alpha-value>)',
        surface2: 'rgb(var(--c-surface2) / <alpha-value>)',
        ink: 'rgb(var(--c-ink) / <alpha-value>)',
        muted: 'rgb(var(--c-muted) / <alpha-value>)',
        dim: 'rgb(var(--c-dim) / <alpha-value>)',
        faint: 'rgb(var(--c-faint) / <alpha-value>)',
        accent: 'rgb(var(--c-accent) / <alpha-value>)',
        'accent-deep': 'rgb(var(--c-accent-deep) / <alpha-value>)',
        health: 'rgb(var(--c-health) / <alpha-value>)',
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
      },
      letterSpacing: {
        tightest: '-0.04em',
      },
    },
  },
  plugins: [],
};
