/*
Auth service: handles authentication API calls using the central apiClient.
This service isolates auth endpoints so the UI/composables can call high-level
methods and remain testable and decoupled from API implementation details.

Methods:
 - login(credentials): POST /login
 - register(payload): POST /register
 - logout(): POST /logout
 - refreshToken(): POST /refresh-token
 - getCurrentUser(): GET /user

Adjust endpoint paths if your backend uses different URIs.
*/

import apiClient from "./apiClient";

const authService = {
  // Login user with credentials (email, password)
  async login(credentials) {
    const response = await apiClient.post("/login", credentials);
    return response.data;
  },

  // Register a new user
  async register(payload) {
    const response = await apiClient.post("/register", payload);
    return response.data;
  },

  // Logout the current user
  async logout() {
    const response = await apiClient.post("/logout");
    return response.data;
  },

  // Refresh authentication token (if backend supports)
  async refreshToken() {
    const response = await apiClient.post("/refresh-token");
    return response.data;
  },

  // Get current user profile / user info
  async getCurrentUser() {
    const response = await apiClient.get("/user");
    return response.data;
  },
};

export default authService;
