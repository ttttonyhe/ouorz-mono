import prettierConfig from '@ouorz/prettier-config'

export default {
  ...prettierConfig,
  plugins: [
    "prettier-plugin-solidity"
  ],
  overrides: [
    {
      files: "*.sol",
      options: {
        semi: true,
        printWidth: 100,
        tabWidth: 2,
        bracketSpacing: true,
        compiler: "0.8.18"
      }
    }
  ]
}
