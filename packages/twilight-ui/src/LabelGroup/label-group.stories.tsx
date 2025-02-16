import Label from "../Label/label"
import LabelGroup from "./label-group"
import { StoryFn, Meta, StoryObj } from "@storybook/react"
import React from "react"

const meta: Meta<typeof LabelGroup> = {
	title: "Components/ Label Group",
	component: LabelGroup,
}

export default meta

type Story = StoryObj<typeof LabelGroup>

const Template: StoryFn<typeof LabelGroup> = ({ children }) => {
	return (
		<div className="flex h-[32px] text-label">
			<LabelGroup>{children}</LabelGroup>
		</div>
	)
}

export const OneLabel: Story = {
	render: Template,
	args: {
		children: (
			<>
				<Label type="green">Preview</Label>
			</>
		),
	},
}

export const TwoLabels: Story = {
	render: Template,
	args: {
		children: (
			<>
				<Label type="green" icon="right" preview>
					Preview
				</Label>
				<Label type="gray-icon" icon="preview">
					Gray
				</Label>
			</>
		),
	},
}

export const ThreeLabels: Story = {
	render: Template,
	args: {
		children: (
			<>
				<Label type="gray-icon" icon="preview" />
				<Label type="green" icon="right" preview>
					Preview
				</Label>
				<Label type="sticky-icon" />
			</>
		),
	},
}
