import React from "react"
import { StoryFn, StoryObj, Meta } from "@storybook/react"

import Icon from "."
import icons from "./icons"

const meta: Meta<typeof Icon> = {
	title: "Components/ Icon",
	component: Icon,
	argTypes: {
		name: {
			options: Object.keys(icons),
			control: { type: "select" },
		},
	},
}

export default meta

type Story = StoryObj<typeof Icon>

const Template: StoryFn<typeof Icon> = (args) => {
	return (
		<i className="flex w-8 h-8">
			<Icon {...args} />
		</i>
	)
}

export const Default: Story = {
	render: Template,
}
