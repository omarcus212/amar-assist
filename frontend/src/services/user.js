import api from './api.js';
import { getErrorMessage } from '../helpers/response.js';

export const userService = {

    async getAll(filters = {}) {
        try {
            const params = new URLSearchParams();
            if (filters.name) params.append('name', filters.name);
            if (filters.email) params.append('email', filters.email);
            if (filters.page) params.append('page', filters.page);

            const response = await api.get(`/users?${params.toString()}`);

            const { data } = response.data;
            return data;

        } catch (error) {
            throw new Error(getErrorMessage(error));
        }
    },

    async getById(id) {
        try {
            const response = await api.get(`/users/${id}`);

            const { data } = response.data;
            return data;

        } catch (error) {
            throw new Error(getErrorMessage(error));
        }
    },

    async create(user) {
        try {
            if (!user.name || !user.email || !user.password) {
                throw new Error(getErrorMessage('Nome, e-mail e senha são obrigatórios.'));
            }


            const response = await api.post('/users', {
                name: user.name,
                email: user.email,
                password: user.password,
            });

            const { data } = response.data;
            return data;

        } catch (error) {
            const backendErrors = error.response?.data?.errors;
            if (backendErrors) {
                throw new Error(Object.values(backendErrors).flat().join('\n'));
            }
            throw new Error(getErrorMessage(error));
        }
    },

    async update(id, user) {
        try {

            const payload = {};
            if (user.name !== undefined) payload.name = user.name;
            if (user.email !== undefined) payload.email = user.email;
            if (user.password !== undefined) payload.password = user.password;

            const response = await api.put(`/users/${id}`, payload);

            const { data } = response.data;
            return data;

        } catch (error) {
            throw new Error(getErrorMessage(error));
        }
    },

    async deactivate(id) {
        try {
            const response = await api.put(`/users/desactive/${id}`);
            return response.data;
        } catch (error) {
            throw new Error(getErrorMessage(error));
        }
    },

};