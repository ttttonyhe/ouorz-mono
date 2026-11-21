import React from "react"
import ReactDOM from "react-dom"
import { useSpring, animated } from "@react-spring/web"
import styles from "./Modal.module.css"

function Modal({ title, children }) {
	const props = useSpring({ opacity: 1, from: { opacity: 0 } })

	return ReactDOM.createPortal(
		<animated.div className={styles.modal} style={props}>
			<div className={styles.content}>
				{title && <div className={styles.header}>{title}</div>}
				<div className={styles.body}>{children}</div>
			</div>
		</animated.div>,
		document.getElementById("__modals")
	)
}

export default Modal
