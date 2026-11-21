import React from "react"
import { Tooltip } from "react-tooltip"
import classNames from "classnames"
import Icon from "./Icon"
import styles from "./Button.module.css"

function Button({
	type = "button",
	icon,
	size,
	variant,
	children,
	className,
	tooltip,
	tooltipId,
	disabled,
	iconRight,
	onClick = () => {},
	...props
}) {
	return (
		<button
			data-tooltip-id={tooltip ? tooltipId : undefined}
			data-tooltip-content={typeof tooltip === "string" ? tooltip : undefined}
			type={type}
			className={classNames(styles.button, className, {
				[styles.large]: size === "large",
				[styles.small]: size === "small",
				[styles.xsmall]: size === "xsmall",
				[styles.action]: variant === "action",
				[styles.danger]: variant === "danger",
				[styles.light]: variant === "light",
				[styles.iconRight]: iconRight,
			})}
			disabled={disabled}
			onClick={!disabled ? onClick : undefined}
			{...props}
		>
			{icon && <Icon className={styles.icon} icon={icon} size={size} />}
			{children && <div className={styles.label}>{children}</div>}
			{tooltip && (
				<Tooltip id={tooltipId}>
					{typeof tooltip === "string" ? null : tooltip}
				</Tooltip>
			)}
		</button>
	)
}

export default Button
