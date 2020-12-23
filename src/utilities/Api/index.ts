interface Parameters {
  sticky?: boolean
  cate?: string
  cateExclude?: string
  perPage?: number
  getCate?: boolean
  mark?: number
  page?: number
  visit?: number
  post?: number
}

export const getApi = ({
  sticky,
  cate,
  cateExclude,
  perPage,
  getCate,
  mark,
  page,
  post,
  visit,
}: Parameters) => {
  if (getCate) {
    return `https://blog.ouorz.com/wp-json/wp/v2/categories/${cate}`
  }

  if (mark) {
    return `https://blog.ouorz.com/wp-json/tony/v1/mark/${mark}`
  }

  if (page) {
    return `https://blog.ouorz.com/wp-json/wp/v2/pages/${page}`
  }

  if (post) {
    return `https://blog.ouorz.com/wp-json/wp/v2/posts/${post}`
  }

  if (visit) {
    return `https://blog.ouorz.com/wp-json/tony/v1/visit/${visit}`
  }

  const s = sticky ? 'sticky=1' : 'sticky=0'
  const ce = cateExclude ? `&categories_exclude=${cateExclude}` : ''
  const c = cate ? `&categories=${cate}` : ''
  const p = `&per_page=${perPage}`
  return `https://blog.ouorz.com/wp-json/wp/v2/posts?${s}${p}${c}${ce}`
}
