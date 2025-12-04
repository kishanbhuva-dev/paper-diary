import apiClient from "./apiClient";

const authService = {
  async login(credentials) {
    const response = await apiClient.post("/login", credentials);
    return response.data;
  },

  async register(payload) {
    const response = await apiClient.post("/register", payload);
    return response.data;
  },

  async logout() {
    const response = await apiClient.post("/logout");
    return response.data;
  },

  async refreshToken() {
    const response = await apiClient.post("/refresh-token");
    return response.data;
  },

  async getCurrentUser() {
    const response = await apiClient.get("/user");
    return response.data;
  },
};

export default authService;
