import api from './api.js';
import { getErrorMessage } from '../helpers/response.js';

export const productService = {

    async getAll(filters = {}) {
        try {
            const params = new URLSearchParams();
            if (filters.title) params.append('title', filters.title);
            if (filters.price_min) params.append('min_price', filters.price_min);
            if (filters.price_max) params.append('max_price', filters.price_max);
            if (filters.active !== undefined) params.append('active', filters.active);
            if (filters.page) params.append('page', filters.page);

            const response = await api.get(`/products?${params}`);

            const { data } = response.data;
            return data;

        } catch (error) {
            throw new Error(getErrorMessage(error));
        }
    },

    async getById(id) {
        try {
            const response = await api.get(`/products/${id}`);

            const { data } = response.data;
            return data;

        } catch (error) {
            throw new Error(getErrorMessage(error));
        }
    },

    async create(product, images = null) {
        try {

            const MAX_IMAGES = 4;

            if (images.length > MAX_IMAGES) {
                throw new Error(getErrorMessage('Máximo de 4 imagens permitido.'));
            }

            const formData = new FormData();
            formData.append('title', product.title);
            formData.append('description', product.description || '');
            formData.append('cost', product.cost);
            formData.append('price', product.price);
            formData.append('active', product.active ?? true);

            if (images && images.length > 0) {
                for (let i = 0; i < images.length; i++) {
                    formData.append('images[]', images[i]);
                }
            }

            const response = await api.post('/products', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            const { data } = response.data;
            return data;

        } catch (error) {
            throw new Error(getErrorMessage(error));
        }
    },

    async update(id, product, newImages = null) {

        try {
            const formData = new FormData();

            // Constrói o FormData apenas com campos definidos para evitar sobrescrever com null
            if (product.title !== undefined) formData.append('title', product.title);
            if (product.description !== undefined) formData.append('description', product.description || '');
            if (product.cost !== undefined) formData.append('cost', product.cost);
            if (product.price !== undefined) formData.append('price', product.price);
            if (product.active !== undefined) formData.append('active', product.active ?? true);

            if (newImages && newImages.length > 0) {
                for (let i = 0; i < newImages.length; i++) {
                    formData.append('images[]', newImages[i]);
                }
            }

            // PHP nativo tem problemas para ler multipart/form-data em requisições PUT.
            // A solução padrão é enviar como POST e usar o _method spoofing.
            formData.append('_method', 'PUT');

            const response = await api.post(`/products/${id}`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });

            const { data } = response.data;
            return data;

        } catch (error) {
            throw new Error(getErrorMessage(error));
        }
    },

    async deleteImage(productId, imageId) {
        try {
            await api.delete(`/products/${productId}/images/${imageId}`);
            return true;
        } catch (error) {
            throw new Error(getErrorMessage(error));
        }
    },

    async deactivate(id) {
        try {
            await api.put(`/products/desactive/${id}`, {
                withCredentials: true
            });
            return true;
        } catch (error) {
            throw new Error(getErrorMessage(error));
        }
    },

    async reactivate(id) {
        try {
            await api.put(`/products/restore/${id}`, {});
            return true;
        } catch (error) {
            throw new Error(getErrorMessage(error));
        }
    }
};