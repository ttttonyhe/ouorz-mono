import About from "./About"
import { Meta, StoryObj } from "@storybook/react"

const meta: Meta<typeof About> = {
	title: "Introduction/ About",
	component: About,
	parameters: {
		controls: {
			hideNoControlsWarning: true,
		},
	},
}

export default meta
type Story = StoryObj<typeof About>

export const AboutPage: Story = {}
