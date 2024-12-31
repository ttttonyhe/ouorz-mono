import { dependencies } from "../package.json"
import autoprefixer from "autoprefixer"
import type { BuildOptions } from "esbuild"
import { build } from "esbuild"
import { dtsPlugin } from "esbuild-plugin-d.ts"
import { sassPlugin } from "esbuild-sass-plugin"
import postcss from "postcss"
import tailwind from "tailwindcss"

const isDevelopment = process.env.NODE_ENV === "development"

const ESBUILD_CONFIG_BASE = {
	outdir: "dist",
	bundle: true,
	minify: !isDevelopment,
	sourcemap: isDevelopment,
	define: {
		DEBUG: isDevelopment ? "true" : "false",
	},
}

const ESBUILD_CONFIG: BuildOptions = {
	...ESBUILD_CONFIG_BASE,
	// ESBuild has built-in support for TypeScript
	entryPoints: ["src/index.ts", "src/index-headless.ts"],
	platform: "browser",
	metafile: true,
	format: "esm",
	external: Object.keys(dependencies),
	plugins: [
		sassPlugin({
			// TailwindCSS
			transform: async (source, _resolveDir) => {
				const postcssPlugins = [autoprefixer, tailwind as any]
				const { css } = await postcss(postcssPlugins).process(source, {
					from: undefined,
				})
				return css
			},
		}),
		// Generate declaration files
		dtsPlugin(),
	],
}

const terminateProcess = (code: number = 0) => {
	process.exit(code)
}

build({ ...ESBUILD_CONFIG })
	.finally(terminateProcess)
	.catch(terminateProcess)
