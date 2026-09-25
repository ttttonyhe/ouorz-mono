import { Icon } from "@twilight-toolkit/ui"
import Image from "next/image"
import { useId, useRef, useState } from "react"

import { supportsViewTransitions } from "~/utilities/environment"

const SRC = "https://static.ouorz.com/avatar_real_small.jpg"
const ALT = "Teaching an undergraduate CS class"
const THUMBNAIL_SIZE = 105
const FULL_SIZE = 708

interface Props {
	className?: string
}

export default function ProfilePicture({ className }: Props) {
	const dialogRef = useRef<HTMLDialogElement>(null)
	const latestMorphRef = useRef<ViewTransition>(null)
	const captionId = useId()
	const [fullSizeLoaded, setFullSizeLoaded] = useState(false)

	// The flag scopes the view transition name to this morph (see global.css)
	const morph = (update: () => void) => {
		if (!supportsViewTransitions()) {
			update()
			return
		}

		const dialog = dialogRef.current
		dialog.dataset.morphing = ""

		const transition = document.startViewTransition(update)
		latestMorphRef.current = transition

		// A morph started mid-flight skips this one and keeps the flag
		const settle = () => {
			if (latestMorphRef.current === transition) {
				delete dialog.dataset.morphing
			}
		}

		void transition.finished.then(settle, settle)
	}

	const show = () => morph(() => dialogRef.current.showModal())
	const hide = () => morph(() => dialogRef.current.close())

	return (
		<>
			{/* global.css hides this while the dialog right after it is open */}
			<button
				type="button"
				aria-label={`View full-size photo: ${ALT}`}
				aria-haspopup="dialog"
				onClick={show}
				style={{ width: THUMBNAIL_SIZE, height: THUMBNAIL_SIZE }}
				className={`profile-picture effect-pressing cursor-zoom-in overflow-hidden rounded-xl shadow-xs dark:border dark:border-gray-600 ${className ?? ""}`}>
				<Image
					src={SRC}
					width={THUMBNAIL_SIZE}
					height={THUMBNAIL_SIZE}
					alt={ALT}
					preload
					className="size-full bg-gray-200"
				/>
			</button>
			<dialog
				ref={dialogRef}
				aria-labelledby={captionId}
				onClick={hide}
				onCancel={(event) => {
					event.preventDefault()
					hide()
				}}
				className="fixed inset-0 size-full max-h-none max-w-none cursor-zoom-out items-center justify-center bg-transparent p-4.5 backdrop:bg-gray-50/90 backdrop:backdrop-blur-sm open:flex dark:backdrop:bg-black/70">
				<div
					style={{ width: `min(${FULL_SIZE}px, 100vw - 2rem, 100dvh - 6rem)` }}
					className="flex flex-col gap-y-4">
					<div className="overflow-hidden rounded-xl">
						<div className="profile-picture-frame relative aspect-square w-full bg-gray-200 dark:bg-gray-700">
							{/* Reuses the thumbnail's cached file so the morph never lands on an empty frame */}
							<Image
								src={SRC}
								width={THUMBNAIL_SIZE}
								height={THUMBNAIL_SIZE}
								alt=""
								loading="eager"
								decoding="sync"
								className="absolute inset-0 size-full"
							/>
							{/* q90 keeps fabric and skin detail that q75 smooths away on retina screens */}
							<Image
								src={SRC}
								width={FULL_SIZE}
								height={FULL_SIZE}
								quality={90}
								alt=""
								decoding="sync"
								onLoad={() => setFullSizeLoaded(true)}
								className={`absolute inset-0 size-full transition-opacity duration-300 ${
									fullSizeLoaded ? "opacity-100" : "opacity-0"
								}`}
							/>
						</div>
					</div>
					<div className="flex items-center justify-between gap-x-4 px-1">
						<p
							id={captionId}
							className="text-4 font-light tracking-wide text-gray-500 dark:text-gray-400">
							{ALT}
						</p>
						{/* Closes through the dialog's click handler */}
						<button
							type="button"
							className="effect-pressing flex shrink-0 cursor-pointer items-center gap-x-1 rounded-md bg-gray-100 px-2 py-1 text-5 tracking-wide text-gray-500 transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
							<span className="h-4 w-4 rotate-180">
								<Icon name="right" />
							</span>
							Close
						</button>
					</div>
				</div>
			</dialog>
		</>
	)
}
