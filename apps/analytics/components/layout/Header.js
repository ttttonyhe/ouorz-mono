import { useRouter } from 'next/router'
import { FormattedMessage } from 'react-intl'
import classNames from 'classnames'
import dynamic from 'next/dynamic'
import Link from 'components/common/Link'
import LanguageButton from 'components/settings/LanguageButton'
import HamburgerButton from 'components/common/HamburgerButton'
import UserButton from 'components/settings/UserButton'
import styles from './Header.module.css'
import useUser from 'hooks/useUser'
import { HOMEPAGE_URL } from 'lib/constants'

const ThemeButton = dynamic(() => import('components/settings/ThemeButton'), {
	ssr: false,
})

export default function Header() {
	const { user } = useUser()
	const { pathname } = useRouter()

	return (
		<>
			<header className={classNames(styles.header, 'row')}>
				{user && (
					<>
						<HamburgerButton />
						<div className={styles.links}>
							<Link href={pathname.includes('/share') ? HOMEPAGE_URL : '/'}>
								<FormattedMessage id="label.home" defaultMessage="Home" />
							</Link>
							<Link href="/dashboard">
								<FormattedMessage
									id="label.dashboard"
									defaultMessage="Dashboard"
								/>
							</Link>
							<Link href="/realtime">
								<FormattedMessage
									id="label.realtime"
									defaultMessage="Realtime"
								/>
							</Link>
							<Link href="/settings">
								<FormattedMessage
									id="label.settings"
									defaultMessage="Settings"
								/>
							</Link>
						</div>
					</>
				)}
				<div className={styles.buttons}>
					<ThemeButton />
					{user && (
						<>
							<LanguageButton menuAlign="right" />
							<UserButton />
						</>
					)}
				</div>
			</header>
		</>
	)
}
