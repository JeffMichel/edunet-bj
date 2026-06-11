/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./client/**/*.html",
    "./client/**/*.js",
    "./client/*.html"
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#1C3F94',
          light: '#2952C4',
          dark: '#112569'
        },
        secondary: {
          DEFAULT: '#E8621A',
          light: '#F07840',
          dark: '#C04E10'
        },
        neutralBg: '#F8F9FC',
        neutralSurface: '#FFFFFF',
        neutralBorder: '#E4E8F0',
        neutralText: '#0F1C3F',
        neutralMuted: '#64748B',
        success: '#10B981',
        warning: '#F59E0B',
        error: '#EF4444'
      },
      fontFamily: {
        sans: ['Poppins', 'sans-serif']
      }
    },
  },
  plugins: [],
}
