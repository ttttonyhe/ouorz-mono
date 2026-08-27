import { defineConfig } from "cypress"

export default defineConfig({
	allowCypressEnv: false,
	e2e: {
		baseUrl: "http://localhost:3000",
		supportFile: false,
		video: false,
		screenshotOnRunFailure: true,
		projectId: "etr8wp",
	},
})
