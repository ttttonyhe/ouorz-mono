/* eslint-disable @typescript-eslint/no-var-requires */
/* eslint-disable prettier/prettier */

var flattenColorPalette = require('tailwindcss/lib/util/flattenColorPalette').default;

module.exports = {
  purge: ['./src/**/*.tsx'],
  darkMode: 'media',
  theme: {
    extend: {
      spacing: {
        '1': '3px',
        '2': '6px',
        '3': '9px',
        '4': '12px',
        '6': '18px',
        '7': '21px',
        '9': '27px',
        '10': '30px',
        '20': '60px'
      },
      colors: {
        gbg: '#f6f7f8',
        menu: '#ebeced',
      },
      boxShadow: {
        header: '0 4px 8px rgba(0,0,0,.04)',
      },
      width: {
        content: '680px',
        page: '720px',
        toc: '200px',
      },
      height: {
        img: '240px',
      },
      fontSize: {
        '1': ['32px', '1.4'],
        '2': ['18px', '1.4'],
        '3': ['15px', '1.4'],
        '4': ['14px', '1.4'],
        '5': ['12px', '1.4'],
        listTitle: '26px',
        label: '18px',
        postTitle: '30px',
        xl: '15px',
        '2xl': '18px',
        '3xl': '22.5px'
      },
      padding: {
        pre: '0.32rem',
      },
      margin: {
        '-82': "-220px"
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
            fontSize: '16.2px',
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
      margin: ['hover'],
      borderWidth: ['first'],
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