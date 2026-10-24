export const TOKEN_KEY = 'toko_hp_token'
export const USER_KEY = 'toko_hp_user'

export function bacaToken() {
  return localStorage.getItem(TOKEN_KEY) ?? ''
}

export function bacaUser() {
  try {
    const data = localStorage.getItem(USER_KEY)
    return data ? JSON.parse(data) : null
  } catch {
    return null
  }
}

export function simpanSesi(token, user) {
  localStorage.setItem(TOKEN_KEY, token)
  localStorage.setItem(USER_KEY, JSON.stringify(user))
}

export function hapusSesi() {
  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(USER_KEY)
}
