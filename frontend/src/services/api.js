import axios from 'axios'
import Swal from 'sweetalert2'

// Configuração base da API
const api = axios.create({
    baseURL: 'http://localhost:8000/api',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
})

// Interceptor para adicionar token automaticamente
api.interceptors.request.use(
    (config) => {
        // Exibe o loading antes de cada requisição
        Swal.fire({
            title: 'Carregando...',
            text: 'Aguarde um momento',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        })

        const token = localStorage.getItem('token')

        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        }

        return config
    },
    (error) => {
        Swal.close()
        return Promise.reject(error)
    }
)


api.interceptors.response.use(
    (response) => {
        Swal.close()
        return response
    },
    (error) => {
        Swal.close()
        // Se receber 401 (não autorizado), limpa token e redireciona
        if (error.response?.status === 401) {
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            window.location.href = '/login'
        }
        return Promise.reject(error)
    }
)

export default api
