import api from './api.js';
import { getErrorMessage } from '../helpers/response.js';

export const authService = {

    async login(email, password) {
        try {
            const response = await api.post('/login', { email, password }, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                withCredentials: true
            });

            const { data } = response.data;

            if (!data.token) {
                throw new Error(getErrorMessage('Token não encontrado na resposta do login.'));

            }

            localStorage.setItem('token', data.token);
            localStorage.setItem('user', JSON.stringify(data.user));

            return data;

        } catch (error) {
            throw new Error(getErrorMessage(error));
        }
    },

    async logout() {
        try {
            await api.post('/logout', {}, {
                withCredentials: true
            });
        } catch (error) {
            throw new Error(getErrorMessage(error));
        } finally {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
        }
    },

    async getToken() {
        return localStorage.getItem('token');
    },



};