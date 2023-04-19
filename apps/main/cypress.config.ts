import { defineConfig } from "cypress"

export default defineConfig({
	e2e: {
		baseUrl: "http://localhost:3000",
		video: false,
		screenshotOnRunFailure: true,
		projectId: "etr8wp",
	},
})
