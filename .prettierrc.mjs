/**
 * Solidity only. Oxfmt formats everything else and does not read `.sol`, and
 * `.solhint.json` runs this same config through `solhint-plugin-prettier`.
 * Reach for it via `format:sol` / `lint:sol`, never on the whole repository.
 */
export default {
	plugins: ["prettier-plugin-solidity"],
	overrides: [
		{
			files: "*.sol",
			options: {
				useTabs: true,
				semi: true,
				printWidth: 100,
				tabWidth: 2,
				bracketSpacing: true,
				compiler: "0.8.30",
			},
		},
	],
}
