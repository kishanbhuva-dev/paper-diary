import apiClient from "./apiClient";

export const userService = {
  // Fetch property details for the DetailsPage
  getPropertyDetails(slug) {
    return apiClient.get(`/user/property-details`, {
      params: { slug },
    });
  },

  // Fetch the logged-in user's booking history
  getBookings(params) {
    return apiClient.get("/user/booking", { params });
  },

  // Create a new booking
  createBooking(bookingData) {
    return apiClient.post("/user/booking", bookingData);
  },
};
