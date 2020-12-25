/* eslint-disable @typescript-eslint/no-var-requires */
/* eslint-disable prettier/prettier */

var flattenColorPalette = require('tailwindcss/lib/util/flattenColorPalette').default;

module.exports = {
  purge: ['./src/**/*.tsx'],
  darkMode: 'media',
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
        page: '720px',
      },
      height: {
        img: '240px',
      },
      fontSize: {
        top: ['2.55rem', '2.55rem'],
        listTitle: ['2.1rem', '2.8rem'],
        label: '1.35rem',
        postTitle: '2.45rem',
      },
      padding: {
        pre: '0.32rem',
      },
      animation: {
        pointer: 'pointer 1s infinite',
      },
      keyframes: {
        pointer: {
          '0%,100%': {
            transform: 'translateX(0)',
          },
          '50%': {
            transform: 'translateX(15%)',
          },
        },
      },
      transitionProperty: {
        width: 'width',
      },
      typography: {
        'xl': {
          css: {
            fontSize: '1.35rem',
            color: 'rgba(33,37,41,0.95)',
            a: {
              color: '#1e87f0',
              textDecoration: "none",
              fontWeight: "normal",
              '&:hover': {
                textDecoration: "underline",
              },
            },
            blockquote: {
              fontWeight: "400",
              justifyItems: "center",
            },
            h4: {
              fontSize: "1.3em",
            },
          },
        },
      }
    },
  },
  variants: {
    extend: {
      animation: ['group-hover', 'hover'],
      display: ['group-hover'],
      transitionProperty: ['hover', 'focus'],
      margin: ['hover']
    },
  },
  plugins: [
    ({
      addUtilities,
      theme,
      variants
    }) => {
      const colors = flattenColorPalette(theme('borderColor'))
      delete colors['default']

      const colorMap = Object.keys(colors).map((color) => ({
        [`.border-t-${color}`]: {
          borderTopColor: colors[color],
        },
        [`.border-r-${color}`]: {
          borderRightColor: colors[color],
        },
        [`.border-b-${color}`]: {
          borderBottomColor: colors[color],
        },
        [`.border-l-${color}`]: {
          borderLeftColor: colors[color],
        },
      }))
      const utilities = Object.assign({}, ...colorMap)

      addUtilities(utilities, variants('borderColor'))
    },
    require('@tailwindcss/typography'),
  ],
}