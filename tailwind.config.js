/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './src/Views/**/*.php',
    './resources/js/**/*.js',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      // Paleta de colores custom
      colors: {
        dark: {
          950: '#050505',
          900: '#0a0a0a',
          800: '#111111',
          700: '#1a1a1a',
          600: '#222222',
        },
        light: {
          50: '#f5f5f5',
          100: '#ebebeb',
          200: '#d9d9d9',
          400: '#a0a0a0',
          600: '#666666',
        },
        accent: {
          DEFAULT: '#c8a97e',   // dorado cálido — a ajustar
          light: '#dfc29a',
          dark: '#a88a60',
        },
      },
      // Tipografías
      fontFamily: {
        display: ['"Clash Display"', 'sans-serif'],
        body: ['Satoshi', 'sans-serif'],
        mono: ['"JetBrains Mono"', 'monospace'],
      },
      // Tamaños de fuente fluidos
      fontSize: {
        'fluid-sm': 'clamp(0.875rem, 1.5vw, 1rem)',
        'fluid-base': 'clamp(1rem, 2vw, 1.125rem)',
        'fluid-lg': 'clamp(1.25rem, 3vw, 1.5rem)',
        'fluid-xl': 'clamp(1.5rem, 4vw, 2rem)',
        'fluid-2xl': 'clamp(2rem, 6vw, 3.5rem)',
        'fluid-3xl': 'clamp(3rem, 8vw, 6rem)',
        'fluid-hero': 'clamp(4rem, 12vw, 10rem)',
      },
      // Espaciados extra
      spacing: {
        '18': '4.5rem',
        '22': '5.5rem',
        '30': '7.5rem',
        '120': '30rem',
        '160': '40rem',
      },
      // Alturas
      height: {
        screen: '100dvh',
      },
      minHeight: {
        screen: '100dvh',
      },
      // Transiciones
      transitionDuration: {
        '400': '400ms',
        '600': '600ms',
        '800': '800ms',
      },
      transitionTimingFunction: {
        'smooth': 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
        'expo-out': 'cubic-bezier(0.16, 1, 0.3, 1)',
      },
      // Grid
      gridTemplateColumns: {
        'portfolio': 'repeat(auto-fill, minmax(min(100%, 380px), 1fr))',
      },
      // Animaciones
      keyframes: {
        'fade-up': {
          '0%': { opacity: '0', transform: 'translateY(30px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        'fade-in': {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        'slide-in-left': {
          '0%': { opacity: '0', transform: 'translateX(-40px)' },
          '100%': { opacity: '1', transform: 'translateX(0)' },
        },
      },
      animation: {
        'fade-up': 'fade-up 0.7s ease-out forwards',
        'fade-in': 'fade-in 0.6s ease-out forwards',
        'slide-in-left': 'slide-in-left 0.7s ease-out forwards',
      },
    },
  },
  plugins: [],
};
