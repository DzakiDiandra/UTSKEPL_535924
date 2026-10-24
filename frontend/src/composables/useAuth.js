import { computed, ref } from 'vue'
import api from '@/api'
import { bacaToken, bacaUser, hapusSesi, simpanSesi } from '@/utils/storage'

const token = ref(bacaToken())
const user = ref(bacaUser())

const isAuthenticated = computed(() => Boolean(token.value))
const isAdmin = computed(() => Boolean(user.value?.is_admin))

export function useAuth() {
  async function login(email, password) {
    const { data } = await api.post('/login', { email, password })

    simpanSesi(data.token, data.user)
    token.value = data.token
    user.value = data.user

    return data.user
  }

  async function logout() {
    try {
      if (token.value) {
        await api.post('/logout')
      }
    } catch {
      clearSession()
    } finally {
      clearSession()
    }
  }

  function clearSession() {
    hapusSesi()
    token.value = ''
    user.value = null
  }

  return {
    token,
    user,
    isAuthenticated,
    isAdmin,
    login,
    logout,
    clearSession,
  }
}
