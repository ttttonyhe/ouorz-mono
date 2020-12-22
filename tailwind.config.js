module.exports = {
  purge: ['./pages/**/*.tsx', './components/**/*.tsx'],
  darkMode: false,
  theme: {
    extend: {
      colors: {
        gbg: '#f6f7f8',
        menu: '#ebeced',
      },
      boxShadow: {
        header: '0 4px 8px rgba(0,0,0,.04)',
      },
      width: {
        content: '640px',
      },
      fontSize: {
        top: ['2.5rem', '2.5rem'],
      },
    },
  },
  variants: {
    extend: {},
  },
  plugins: [],
}
