import React from "react"
import classNames from "classnames"
import NextLink from "next/link"
import Icon from "./Icon"
import styles from "./Link.module.css"

function Link({
	className,
	icon,
	children,
	size,
	iconRight,
	onClick,
	...props
}) {
	return (
        (<NextLink
            {...props}
            className={classNames(styles.link, className, {
                [styles.large]: size === "large",
                [styles.small]: size === "small",
                [styles.xsmall]: size === "xsmall",
                [styles.iconRight]: iconRight,
            })}
            onClick={onClick}>

            {icon && <Icon className={styles.icon} icon={icon} size={size} />}
            {children}

        </NextLink>)
    );
}

export default Link
