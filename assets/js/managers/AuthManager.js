import {AUTH_TOKEN} from "../vue-apollo"
import jwtDecode from "jwt-decode"

class AuthManager {
    isTokenValid() {
        try {
            const decoded = jwtDecode(this.getToken())
            const expirationDate = new Date(decoded.exp * 1000)
            return expirationDate > Date.now()
        } catch (e) {
            this.removeToken()
            return false
        }
    }

    hasToken() {
        return (
            localStorage.getItem(AUTH_TOKEN) !== "undefined" &&
            localStorage.getItem(AUTH_TOKEN) !== undefined &&
            localStorage.getItem(AUTH_TOKEN) !== null
        )
    }

    removeToken() {
        localStorage.removeItem(AUTH_TOKEN)
    }

    login(token) {
        localStorage.setItem(AUTH_TOKEN, token)
    }

    async loginForm(data, locale = 'en') {
        const res = (await fetch(`/${locale}/login`, {
            method: 'POST',
            body: JSON.stringify(data),
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
            },
        }))
        const json = await res.json()
        if (json.success) {
            window.location.reload()
            return true
        }
        return json
    }

    clearToken() {
        localStorage.removeItem(AUTH_TOKEN)
    }

    getToken() {
        return localStorage.getItem(AUTH_TOKEN)
    }

    async askNewToken() {
        if (this.isLoggedIn()) {
            return Promise.resolve(this.getToken())
        } else {
            let request = await fetch('/get_api_token', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    "Content-Type": "application/json",
                }
            })
            if (request.ok) {
                return (await request.json()).token
            }

            return Promise.reject()
        }
    }

    logout() {
        localStorage.removeItem(AUTH_TOKEN)
    }

    isLoggedIn() {
        return this.hasToken()
    }
}

const instance = new AuthManager()
export default instance