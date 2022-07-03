import React from 'react'
import { ComponentStory, ComponentMeta } from '@storybook/react'

import Button from './button'
import { iconsNames } from '../utils/propTypes'

export default {
	title: 'Components/ Button',
	component: Button,
	argTypes: {
		icon: {
			options: iconsNames,
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
