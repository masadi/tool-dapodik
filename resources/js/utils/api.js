import { ofetch } from 'ofetch'

export const $api = ofetch.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  async onRequest({ options }) {
    const accessToken = useCookie('accessToken').value
    if (accessToken)
      options.headers.append('Authorization', `Bearer ${accessToken}`)
  },
  async onResponseError({ request, response, options }) {
    if (response.status === 401) {
      useCookie('userAbilityRules').value = null
      useCookie('userData').value = null
      useCookie('accessToken').value = null
      window.location.replace('/login')
    }
  },
})
