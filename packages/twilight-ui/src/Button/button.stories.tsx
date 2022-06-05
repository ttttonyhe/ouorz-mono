import React from 'react'
import { ComponentStory, ComponentMeta } from '@storybook/react'

import Button from '.'

export default {
	title: 'Button',
	component: Button,
	argTypes: {
		type: {
			options: ['default', 'menu-default', 'primary', 'menu-primary'],
			control: { type: 'select' },
		},
	},
} as ComponentMeta<typeof Button>

const Template: ComponentStory<typeof Button> = ({ children, ...args }) => {
	return <Button {...args}>{children}</Button>
}

export const Default = Template.bind({})
Default.args = {
	type: 'default',
	children: 'Button',
}

export const MenuDefault = Template.bind({})
MenuDefault.args = {
	type: 'menu-default',
	children: 'Button',
}

export const Primary = Template.bind({})
Primary.args = {
	type: 'primary',
	children: 'Button',
}

export const MenuPrimary = Template.bind({})
MenuPrimary.args = {
	type: 'menu-primary',
	children: 'Button',
}
