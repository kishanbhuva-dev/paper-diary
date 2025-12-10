import apiClient from "./apiClient";

const authService = {
  async createOwner(payload) {
    const response = await apiClient.post("/admin/create-owner", payload);
    return response.data;
  },

  async fetchAllOwners(query) {
    const response = await apiClient.get("/admin/fetch-all-owner", {
      params: query,
    });
    return response.data;
  },

  async deleteOwner(id) {
    const response = await apiClient.post("/admin/delete-owner", id);
    return response.data;
  },
};

export default authService;
