const siteUrl = import.meta.env.VITE_SITE_URL || 'https://desarrimage.prigent.tech'
const defaultDescription = 'Des alternatives françaises et européennes et des guides concrets pour migrer sans perdre vos données.'

function upsertMeta(selector, attribute, value, content) {
  let element = document.head.querySelector(selector)
  if (!element) {
    element = document.createElement('meta')
    element.setAttribute(attribute, value)
    document.head.appendChild(element)
  }
  element.setAttribute('content', content)
}

export function setSeo({ title, description = defaultDescription, path = '/', image = '/images/social-home.png', type = 'website', schema = null }) {
  const url = `${siteUrl}${path}`
  const imageUrl = image.startsWith('http') ? image : `${siteUrl}${image}`
  document.title = title
  upsertMeta('meta[name="description"]', 'name', 'description', description)
  upsertMeta('meta[property="og:title"]', 'property', 'og:title', title)
  upsertMeta('meta[property="og:description"]', 'property', 'og:description', description)
  upsertMeta('meta[property="og:type"]', 'property', 'og:type', type)
  upsertMeta('meta[property="og:url"]', 'property', 'og:url', url)
  upsertMeta('meta[property="og:image"]', 'property', 'og:image', imageUrl)
  upsertMeta('meta[property="og:locale"]', 'property', 'og:locale', 'fr_FR')
  upsertMeta('meta[property="og:site_name"]', 'property', 'og:site_name', 'Désarrimage')
  upsertMeta('meta[name="twitter:card"]', 'name', 'twitter:card', 'summary_large_image')
  upsertMeta('meta[name="twitter:title"]', 'name', 'twitter:title', title)
  upsertMeta('meta[name="twitter:description"]', 'name', 'twitter:description', description)
  upsertMeta('meta[name="twitter:image"]', 'name', 'twitter:image', imageUrl)

  let canonical = document.head.querySelector('link[rel="canonical"]')
  if (!canonical) {
    canonical = document.createElement('link')
    canonical.setAttribute('rel', 'canonical')
    document.head.appendChild(canonical)
  }
  canonical.setAttribute('href', url)

  const structuredData = schema || {
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    name: 'Désarrimage',
    url: siteUrl,
    description: defaultDescription,
    inLanguage: 'fr-FR',
  }
  let script = document.head.querySelector('#seo-schema')
  if (!script) {
    script = document.createElement('script')
    script.id = 'seo-schema'
    script.type = 'application/ld+json'
    document.head.appendChild(script)
  }
  script.textContent = JSON.stringify(structuredData)
}
