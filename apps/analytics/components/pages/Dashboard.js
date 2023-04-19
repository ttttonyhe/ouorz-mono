import { useState } from "react"
import { FormattedMessage } from "react-intl"
import { useRouter } from "next/router"
import Page from "components/layout/Page"
import WebsiteList from "components/pages/WebsiteList"
import Button from "components/common/Button"
import useFetch from "hooks/useFetch"
import useStore from "store/app"
import styles from "./WebsiteList.module.css"

const selector = (state) => state.dashboard

export default function Dashboard() {
	const router = useRouter()
	const { id } = router.query
	const userId = id?.[0]
	const store = useStore(selector)
	const { limit } = store
	const [max, setMax] = useState(limit)
	const { data } = useFetch("/websites", { params: { user_id: userId } })

	function handleMore() {
		setMax(max + limit)
	}

	if (!data) {
		return null
	}

	return (
		<Page>
			<WebsiteList websites={data} limit={max} />
			{max < data.length && (
				<Button className={styles.button} onClick={handleMore}>
					<FormattedMessage id="label.more" defaultMessage="More" />
				</Button>
			)}
		</Page>
	)
}
