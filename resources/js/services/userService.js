import api from "./apiClient"; // Assuming your axios instance is in an api.js file

export const userService = {
  getPropertyDetails(slug) {
    return api.get("/property-detail", {
      params: { slug },
    });
  },

  getAvailableResourcesTypes(params) {
    return api.get("/available-resources-types", {
      params: {
        slug: params.slug,
        arrivalDateTime: params.arrivalDateTime,
        departureDateTime: params.departureDateTime,
        totalResources: params.totalResources,
      },
    });
  },

  createBooking(bookingData) {
    return api.post("/user/booking", bookingData);
  },

  getBookings(params) {
    return api.get("/user/bookings", { params });
  },

  createPaymentIntent(data) {
    return api.post("/user/create-payment-intent", data);
  },

  completePayment(data) {
    return api.post("/user/complete-payment", data);
  },
  bookingStatusUpdate(data) {
    return api.post("/user/booking-status-update", data);
  },
  cancelBooking(data) {
    return api.post("/user/booking-cancel", data);
  },
};

export default userService;
