/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        // Colores futboleros de Infutt
        'futbol': {
          'primary': '#2E7D32',
          'secondary': '#4CAF50',
          'accent': '#81C784',
          'field': '#4A6741',
          'grass': '#66BB6A',
          'orange': '#FF6B35',
          'orange-light': '#FF8A65',
          'orange-dark': '#E64A19',
          'black': '#1A1A1A',
          'dark-black': '#000000',
        },
        // Gradientes personalizados
        'gradient': {
          'primary': 'linear-gradient(135deg, #2E7D32 0%, #4CAF50 100%)',
          'field': 'linear-gradient(135deg, #4A6741 0%, #66BB6A 100%)',
          'card': 'linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%)',
          'navbar': 'linear-gradient(90deg, #1A1A1A 0%, #000000 100%)',
          'orange': 'linear-gradient(135deg, #FF6B35 0%, #FF8A65 100%)',
        }
      },
      fontFamily: {
        'futbol': ['Nunito', 'sans-serif'],
      },
      boxShadow: {
        'futbol': '0 4px 12px rgba(46, 125, 50, 0.3)',
        'futbol-lg': '0 8px 16px rgba(46, 125, 50, 0.4)',
        'orange': '0 4px 12px rgba(255, 107, 53, 0.3)',
      },
      animation: {
        'futbol-pulse': 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        'futbol-bounce': 'bounce 1s infinite',
        'futbol-spin': 'spin 3s linear infinite',
      },
      backgroundImage: {
        'futbol-gradient': 'linear-gradient(135deg, #2E7D32 0%, #4CAF50 100%)',
        'field-gradient': 'linear-gradient(135deg, #4A6741 0%, #66BB6A 100%)',
        'orange-gradient': 'linear-gradient(135deg, #FF6B35 0%, #FF8A65 100%)',
      }
    },
  },
  plugins: [],
}
