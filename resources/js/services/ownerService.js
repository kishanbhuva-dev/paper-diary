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
 - fetchBookings(params)
 - fetchBookingById(id)
 - updateBooking(id, payload)
 - deleteBooking(id)
*/

import apiClient from "./apiClient";

const ownerService = {
  // --- PROPERTY METHODS ---

  // Fetch all properties for the owner
  async fetchProperties(params = {}) {
    const res = await apiClient.get("/owner/property", { params });
    return res.data.data;
  },

  // Fetch a single property by ID
  async fetchPropertyById(id) {
    // The caller should resolve/decode the ID (if needed). Service is a thin
    // wrapper and will use the id as provided.
    const res = await apiClient.get(`/owner/property/${id}`);
    return res.data.data;
  },

  // Create a new property
  async createProperty(payload) {
    const res = await apiClient.post("/owner/property", payload);
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

  // Delete multiple property images by ids
  async deletePropertyImages(ids = []) {
    const res = await apiClient.post("/owner/property-image-delete", { ids });
    return res.data.data;
  },

  // Fetch images for a property
  async fetchPropertyImages(propertyId) {
    const res = await apiClient.get("/owner/property-wise-image", {
      params: { propertyId },
    });

    // Return raw server data; caller will handle any transformation (e.g. building URLs)
    return res.data.data || [];
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
    // Backend expects { data: { propertyId, facilityId } }
    const res = await apiClient.post("/owner/add-facility-property", {
      data: payload,
    });
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

  // --- RESOURCE TYPE METHODS ---

  // Create multiple resource types (room types) in bulk
  async resourceTypeMultipleStore(payload) {
    const res = await apiClient.post(
      "/owner/resource-type-multiple-store",
      payload
    );
    return res.data;
  },

  // Update multiple resource types in bulk
  async resourceTypeMultipleUpdate(payload) {
    const res = await apiClient.post(
      "/owner/resource-type-multiple-update",
      payload
    );
    return res.data;
  },

  // Fetch resource types for a property
  async fetchResourceTypes(propertyId) {
    // Controller expects query param 'id' containing property id
    const res = await apiClient.get("/owner/resource-type", {
      params: { id: propertyId },
    });
    return res.data.data;
  },

  // Delete a resource type by id
  async deleteResourceType(id) {
    const res = await apiClient.delete(`/owner/resource-type/${id}`);
    return res.data;
  },

  // --- RESOURCE METHODS ---

  // Create multiple resources (rooms) in bulk
  async resourceList() {
    const res = await apiClient.get("/owner/resource-wise-list");
    return res.data.data;
  },

  // Create multiple resources (rooms) in bulk
  async resourceMultipleStore(payload) {
    const res = await apiClient.post("/owner/resource-multiple-store", payload);
    return res.data;
  },

  // Update multiple resources in bulk
  async resourceMultipleUpdate(payload) {
    const res = await apiClient.post(
      "/owner/resource-multiple-update",
      payload
    );
    return res.data;
  },

  // Fetch resources for a property
  async fetchResources(propertyId) {
    const res = await apiClient.get("/owner/resource", {
      params: { propertyId },
    });
    return res.data.data;
  },

  // Delete a single resource by id
  async deleteResource(id) {
    const res = await apiClient.delete(`/owner/resource/${id}`);
    return res.data;
  },

  // --- BOOKINGS METHODS (NEW) ---

  // Fetch all bookings for the owner's properties
  async fetchBookings(params = {}) {
    // Corresponds to the route: GET /owner/bookings
    const res = await apiClient.get("/owner/bookings", { params });
    // Backend returns { status, message, data: { current_page, data: [...], total, ... } }
    return res.data.data;
  },

  // Fetch a single booking by ID
  async fetchBookingById(id) {
    // Corresponds to the route: GET /owner/bookings/{id}
    const res = await apiClient.get(`/owner/bookings/${id}`);
    return res.data.data;
  },

  // Update a booking
  async updateBooking(id, payload) {
    // Corresponds to the route: PUT/PATCH /owner/bookings/{id}
    const res = await apiClient.put(`/owner/bookings/${id}`, payload);
    return res.data.data;
  },

  // Delete a booking
  async deleteBooking(id) {
    // Corresponds to the route: DELETE /owner/bookings/{id}
    const res = await apiClient.delete(`/owner/bookings/${id}`);
    return res.data.data;
  },
};

export default ownerService;
