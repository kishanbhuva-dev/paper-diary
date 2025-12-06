/*
Owner service: property and owner-related API calls.
Centralizes all property/facility-related endpoints so the UI layer calls plain
methods and remains decoupled from HTTP details.

Methods included:
 - fetchProperties(params)
 - fetchPropertyById(id)
 - createProperty(payload)
 - updateProperty(id, payload)
 - deleteProperty(id)
 - fetchPropertyImages(propertyId)
 - addPropertyImages(propertyId, files)
 - setPropertyFacilities(propertyId, facilityIds)
 - fetchFacilities(params)

This mirrors the Laravel routes under /api/owner/* observed in the project.
Note: apiClient already has baseURL='/api', so we use relative paths.
*/

import apiClient from "./apiClient";

const ownerService = {
  // Fetch all properties for the owner
  async fetchProperties(params = {}) {
    const res = await apiClient.get("/owner/property", { params });
    // Backend returns { status, message, data: { current_page, data: [...], total, ... } }
    // Extract the paginated data object
    return res.data.data;
  },

  // Fetch a single property by ID
  async fetchPropertyById(id) {
    const res = await apiClient.get(`/owner/property/${atob(id)}`);
    return res.data.data;
  },

  // Create a new property
  async createProperty(payload) {
    const res = await apiClient.post("/owner/property", payload);
    // Backend returns { status, message, data: '' }, so response.data.data is empty string
    // We return the full response to allow caller to handle it
    return res.data;
  },

  // Update an existing property
  async updateProperty(id, payload) {
    const res = await apiClient.put(`/owner/property/${id}`, payload);
    return res.data.data;
  },

  // Delete a property
  async deleteProperty(id) {
    const res = await apiClient.delete(`/owner/property/${id}`);
    return res.data.data;
  },

  // Fetch images for a property
  async fetchPropertyImages(propertyId) {
    const res = await apiClient.get("/owner/property-wise-image", {
      params: { propertyId },
    });

    // Transform images: backend returns 'image' field (filename), need to convert to 'url'
    const images = res.data.data || [];
    const transformedImages = images.map((img) => ({
      id: img.id,
      image: img.image,
      url: `/storage/property/images/${img.image}`, // Construct full URL
      position: img.position,
    }));

    return transformedImages;
  },

  // Upload images one-by-one to avoid 413 payload too large errors
  async addPropertyImages(propertyId, files = []) {
    const created = [];
    for (const file of files) {
      const form = new FormData();
      form.append("propertyId", propertyId);
      form.append("images[]", file);
      const res = await apiClient.post("/owner/property-image-store", form, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      created.push(res.data.data);
    }
    return created;
  },

  // Sync property facilities (POST the selected facility IDs)
  async setPropertyFacilities(payload) {
    res = await apiClient.post("/owner/add-facility-property", payload);
    return res.data.data;
  },

  // Fetch all available facilities
  async fetchFacilities(params = {}) {
    const res = await apiClient.get("/owner/facility", { params });
    return res.data.data;
  },

  // Delete a property image
  async deletePropertyImage(imageId) {
    const res = await apiClient.post("/owner/single-property-image-delete", {
      id: imageId,
    });

    return res.data.data;
  },

  // Change property image position/order
  async changePropertyImagePosition(propertyId, imageIds = []) {
    const res = await apiClient.post("/owner/property-image-position-change", {
      ids: imageIds, // Backend expects 'ids', not 'imageIds'
    });

    return res.data.data;
  },
};

export default ownerService;
