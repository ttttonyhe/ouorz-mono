import React from "react"
import classNames from "classnames"
import styles from "./Footer.module.css"

export default function Footer() {
	return (
		<footer className={classNames(styles.footer, "row")}>
			<p>
				Powered by&nbsp;
				<a href="https://umami.is" target="_blank" rel="noopener noreferrer">
					Umami
				</a>
			</p>
		</footer>
	)
}
