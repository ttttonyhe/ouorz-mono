import { Meta, StoryObj } from "@storybook/react"

import Button from "./button"
import { iconsNames } from "../utils/propTypes"

const meta: Meta<typeof Button> = {
	title: "Components/ Button",
	component: Button,
	tags: ["button", "input", "autodocs"],
	argTypes: {
		icon: {
			options: iconsNames,
			control: { type: "select" },
		},
	},
}

export default meta

type Story = StoryObj<typeof Button>

export const Default: Story = {
	args: {
		type: "default",
		children: "Button",
	},
}

export const MenuDefault: Story = {
	args: {
		type: "menu-default",
		children: "Button",
	},
}

export const Primary: Story = {
	args: {
		type: "primary",
		children: "Button",
	},
}

export const MenuPrimary: Story = {
	args: {
		type: "menu-primary",
		children: "Button",
	},
}
