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
      height: {
        img: '240px',
      },
      fontSize: {
        top: ['2.55rem', '2.55rem'],
        listTitle: ['2.1rem', '2.8rem'],
        label: '1.35rem',
      },
      padding: {
        pre: '0.32rem',
      },
    },
  },
  variants: {
    extend: {},
  },
  plugins: [],
}
