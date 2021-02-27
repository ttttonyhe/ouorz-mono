/* eslint-disable @typescript-eslint/no-var-requires */
/* eslint-disable prettier/prettier */

var flattenColorPalette = require('tailwindcss/lib/util/flattenColorPalette').default;
const colors = require('tailwindcss/colors')

module.exports = {
  purge: ['./src/**/*.tsx'],
  darkMode: 'class',
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
        '20': '60px',
        readerOffset: '-47.5px',
        searchOffset: 'calc((100% - 680px) / 2)',
        aside: 'calc(100% - 6rem)'
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
        '1': '32px',
        '1.5': '23px',
        '2': '18px',
        '3': '15px',
        '4': '14px',
        '5': '12px',
        listTitle: '26px',
        label: '18px',
        postTitle: '30px',
        xl: '15px',
        '2xl': '18px',
        '3xl': '22.5px',
        '3.5xl': '27px',
      },
      lineHeight: {
        14: '1.4',
      },
      padding: {
        pre: '0.32rem',
      },
      margin: {
        '-82': "-220px"
      },
      animation: {
        pointer: 'pointer 1s infinite',
        reader: 'moveUp ease-in-out .5s',
        readerOut: 'moveDown ease-in-out .5s',
        readerBg: 'opacityProgressIn ease-in-out .5s',
        readerBgOut: 'opacityProgressOut ease-in-out .5s',
        searchBg: 'opacityProgressIn ease-in-out .25s',
        searchBgOut: 'opacityProgressOut ease-in-out .25s',
        search: 'search ease-in-out .25s',
        searchOut: 'searchOut ease-in-out .25s'
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
        moveUp: {
          '0%': {
            transform: 'translateY(100vh)',
          },
          '80%': {
            transform: 'translateY(-5px)',
          },
          '100%': {
            transform: 'translateY(0vh)',
          },
        },
        moveDown: {
          '0%': {
            transform: 'translateY(0vh)',
          },
          '100%': {
            transform: 'translateY(100vh)',
          },
        },
        opacityProgressIn: {
          '0%': {
            opacity: '0'
          },
          '100%': {
            opacity: '1',
          }
        },
        opacityProgressOut: {
          '0%': {
            opacity: '1'
          },
          '100%': {
            opacity: '0',
          }
        },
        search: {
          '0%': {
            opacity: 0,
            transform: 'scale(1.1,1.1)'
          },
          '100%': {
            opacity: 1,
            transform: 'scale(1.0,1.0)'
          }
        },
        searchOut: {
          '0%': {
            opacity: 1,
            transform: 'scale(1.0,1.0)'
          },
          '100%': {
            opacity: 0,
            transform: 'scale(1.1,1.1)'
          }
        }
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
        dark: {
          css: {
            fontSize: '16.2px',
            a: {
              color: colors.blue[500],
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
            color: colors.white,
          },
        },
      }
    },
  },
  variants: {
    extend: {
      animation: ['group-hover', 'hover', 'focus'],
      display: ['group-hover'],
      transitionProperty: ['hover', 'focus'],
      margin: ['hover'],
      borderWidth: ['first'],
      opacity: ['dark'],
      typography: ['dark'],
      borderRadius: ['hover']
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