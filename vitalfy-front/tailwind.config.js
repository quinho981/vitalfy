/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: ['selector', '[class*="app-dark"]'],
    content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
    safelist: [
        'bg-pink-50', 
        'text-pink-600',
        'bg-indigo-50', 
        'text-indigo-600',
        'bg-green-50', 
        'text-green-700',
        'bg-purple-50', 
        'text-purple-600',
        'bg-orange-50', 
        'text-orange-600',
        'dark:bg-blue-500/10', 
        'dark:text-blue-400',
        'dark:bg-green-500/10', 
        'dark:text-green-400',
        'dark:bg-purple-500/10', 
        'dark:text-purple-400',
        'dark:bg-pink-500/10', 
        'dark:text-pink-400',
        'dark:bg-indigo-500/10', 
        'dark:text-indigo-400',
        'dark:bg-orange-500/10', 
        'dark:text-orange-400',
        'bg-teal-50',
        'text-teal-600',
        'dark:bg-teal-500/10',
        'dark:text-teal-400'
    ],
    plugins: [require('tailwindcss-primeui')],
    theme: {
        screens: {
            sm: '576px',
            md: '768px',
            lg: '992px',
            xl: '1200px',
            '2xl': '1920px'
        },
        fontFamily: {
            robotomono: ['RobotoMono', 'monospace'],
        },
    }
};
