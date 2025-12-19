import api from "./apiClient"; // Assuming your axios instance is in an api.js file

export const userService = {
  getPropertyDetails(slug) {
    return api.get("/user/property-details", {
      params: { slug },
    });
  },

  getAvailableResourcesTypes(params) {
    return api.get("/user/available-resources-types", {
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
    return api.get("/user/booking", { params });
  },
};

export default userService;
