import React from 'react'
import { ComponentStory, ComponentMeta } from '@storybook/react'

import Label from './label'
import { iconsNames } from '../utils/propTypes'

export default {
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
} as ComponentMeta<typeof Label>

const Template: ComponentStory<typeof Label> = ({ children, ...args }) => {
	return (
		<div className="flex h-[32px] text-label">
			<Label {...args}>{children}</Label>
		</div>
	)
}

export const Primary = Template.bind({})
Primary.args = {
	type: 'primary',
	children: 'Label',
}

export const Secondary = Template.bind({})
Secondary.args = {
	type: 'secondary',
	children: 'Label',
}

export const Sticky = Template.bind({})
Sticky.args = {
	type: 'sticky-icon',
	children: 'Pinned',
}

export const Green = Template.bind({})
Green.args = {
	type: 'green',
	children: 'Label',
}

export const GreenPreview = Template.bind({})
GreenPreview.args = {
	type: 'green',
	icon: 'right',
	children: 'Preview',
	preview: true,
}

export const GreenIcon = Template.bind({})
GreenIcon.args = {
	type: 'green-icon',
	icon: 'preview',
	children: 'Label',
}

export const GrayIcon = Template.bind({})
GrayIcon.args = {
	type: 'gray-icon',
	icon: 'preview',
	children: 'Label',
}

export const OrangeIcon = Template.bind({})
OrangeIcon.args = {
	type: 'orange-icon',
	icon: 'preview',
	children: 'Label',
}
