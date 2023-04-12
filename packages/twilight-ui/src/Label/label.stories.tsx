import React from 'react'
import { StoryFn, Meta, StoryObj } from '@storybook/react'

import Label from './label'
import { iconsNames } from '../utils/propTypes'

const meta: Meta<typeof Label> = {
	title: 'Components/ Label',
	component: Label,
	argTypes: {
		icon: {
			options: iconsNames,
			control: { type: 'select' },
		},
		preview: {
			control: { type: 'boolean' },
		},
	},
}

export default meta

type Story = StoryObj<typeof Label>

const Template: StoryFn<typeof Label> = ({ children, ...args }) => {
	return (
		<div className="flex h-[32px] text-label">
			<Label {...args}>{children}</Label>
		</div>
	)
}

export const Primary: Story = {
	render: Template,
	args: {
		type: 'primary',
		children: 'Label',
	},
}

export const Secondary: Story = {
	render: Template,
	args: {
		type: 'secondary',
		children: 'Label',
	},
}

export const Sticky: Story = {
	render: Template,
	args: {
		type: 'sticky-icon',
		children: 'Pinned',
	},
}

export const Green: Story = {
	render: Template,
	args: {
		type: 'green',
		children: 'Label',
	},
}

export const GreenPreview: Story = {
	render: Template,
	args: {
		type: 'green',
		icon: 'right',
		children: 'Preview',
		preview: true,
	},
}

export const GreenIcon: Story = {
	render: Template,
	args: {
		type: 'green-icon',
		icon: 'preview',
		children: 'Label',
	},
}

export const GrayIcon: Story = {
	render: Template,
	args: {
		type: 'gray-icon',
		icon: 'preview',
		children: 'Label',
	},
}

export const OrangeIcon: Story = {
	render: Template,
	args: {
		type: 'orange-icon',
		icon: 'preview',
		children: 'Label',
	},
}
