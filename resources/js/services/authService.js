import apiClient from './apiClient';

const authService = {
  async login(credentials) {
    const response = await apiClient.post('/login', credentials);
    return response.data;
  },

  async register(payload) {
    const response = await apiClient.post('/register', payload);
    return response.data;
  },

  async logout() {
    const response = await apiClient.post('/logout');
    return response.data;
  },

  async forgotPassword(payload) {
    const response = await apiClient.post('/forget-password', payload);
    return response.data;
  },

  async resetPassword(data) {
    const response = await apiClient.post('/reset-password', data);
    return response.data;
  },

  async updateProfile(payload) {
    const response = await apiClient.post('/profile', payload);
    return response.data;
  },

  // Added/Verified for Change Password
  async changePassword(payload) {
    const response = await apiClient.post('/change-password', payload);
    return response.data;
  },
};

export default authService;
