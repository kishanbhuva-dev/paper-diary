import apiClient from './apiClient';

const authService = {
  async fetchAllOwners(query) {
    const response = await apiClient.get('/admin/fetch-all-owner', {
      params: query,
    });
    return response.data;
  },

  async createOwner(payload) {
    const response = await apiClient.post('/admin/create-owner', payload);
    return response.data;
  },

  async updateOwner(payload) {
    const response = await apiClient.post('/admin/update-owner', payload);
    return response.data;
  },

  async deleteOwner(id) {
    const response = await apiClient.post('/admin/delete-owner', id);
    return response.data;
  },

  async fetchAllUsers(query) {
    const response = await apiClient.get('/admin/fetch-all-user', {
      params: query,
    });
    return response.data;
  },

  async createUser(payload) {
    const response = await apiClient.post('/admin/create-user', payload);
    return response.data;
  },

  async updateUser(payload) {
    const response = await apiClient.post('/admin/update-user', payload);
    return response.data;
  },

  async deleteUser(id) {
    const response = await apiClient.post('/admin/delete-user', id);
    return response.data;
  },

  async fetchPropertLisings(query) {
    const response = await apiClient.get('admin/property', {
      params: query,
    });

    return response.data;
  },

  async ChangePropertyStatus(payload) {
    const response = await apiClient.put(`/admin/property/${payload.id}`, {
      id: payload.id,
      status: payload.status,
    });
    return response.data;
  },

  async addFacility(payload) {
    const response = await apiClient.post('/admin/facility', payload);
    return response.data;
  },

  async fetchFacilites(query) {
    const response = await apiClient.get('admin/facility', {
      params: query,
    });

    return response.data;
  },

  async updateFacility(payload) {
    const response = await apiClient.put(`/admin/facility/${payload.id}`, payload);
    return response.data;
  },

  async deleteFacility(id) {
    const response = await apiClient.delete(`/admin/facility/${id}`);
    return response.data;
  },

  async loginAsOwner(email) {
    const response = await apiClient.post('/admin/email-login', email);
    return response;
  },

  // admin dashboard
  async adminDashboard() {
    const response = await apiClient.get('/admin');
    return response;
  },

  async fetchSubscriptions(query) {
    const response = await apiClient.get('admin/subscriptions', {
      params: query,
    });

    return response.data;
  },

  async fetchAdminBookings(query) {
    const response = await apiClient.get('admin/booking', {
      params: query,
    });
    return response.data;
  },
};

export default authService;
