// src/utils/response.js

/**
 * Extrai a mensagem de erro da resposta da API
 * @param {Error} error 
 * @returns {string}
 */
export const getErrorMessage = (error) => {
    return error?.response?.data?.message || error?.message || 'Ocorreu um erro inesperado.';
};

export const getErrors = (error) => {
    return error?.response?.data?.errors || null;
};