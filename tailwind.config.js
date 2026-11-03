import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
  ],
  theme: {
    extend: {
      fontFamily: { sans: ['"Inter Variable"', 'Inter', ...defaultTheme.fontFamily.sans] },
      colors: {
        primary: {
          50: '#EFF6FF', 100: '#DBEAFE', 200: '#BFDBFE', 300: '#93C5FD', 400: '#60A5FA',
          500: '#3B82F6', 600: '#2563EB', 700: '#1D4ED8', 800: '#1E40AF', 900: '#1E3A8A',
          DEFAULT: '#2563EB',
        },
        ink: '#0F172A',
        muted: '#475569',
        subtle: '#64748B',
        line: '#E2E8F0',
        canvas: '#F8FAFC',
        surface: '#FFFFFF',
        success: { DEFAULT: '#16A34A', soft: '#DCFCE7', ink: '#15803D' },
        warning: { DEFAULT: '#D97706', soft: '#FEF3C7', ink: '#B45309' },
        danger:  { DEFAULT: '#DC2626', soft: '#FEE2E2', ink: '#B91C1C' },
        info:    { DEFAULT: '#0EA5E9', soft: '#E0F2FE', ink: '#0369A1' },
      },
      borderRadius: { card: '16px' },
      boxShadow: {
        card: '0 1px 2px rgba(15,23,42,.04), 0 1px 3px rgba(15,23,42,.06)',
        pop: '0 10px 24px rgba(15,23,42,.12)',
      },
    },
  },
  plugins: [forms],
};
