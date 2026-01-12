<template>
  <Loader ref="loaderRef" />
  <router-view />

  <Toaster
    position="top-right"
    :expand="true"
    rich-colors
    :style="{ zIndex: 99999 }"
  />
</template>

<script setup>
import { Toaster } from "vue-sonner";
import { ref, provide, onMounted } from "vue";
import Loader from "../js/components/global/Loader.vue";
import apiClient from "../js/services/apiClient";
import { useAuth } from "../js/composables/useAuth";

const loaderRef = ref(null);
const { checkAuth } = useAuth();

onMounted(() => {
  checkAuth();
});

provide("$loading", {
  show: () => loaderRef.value?.show(),
  hide: () => loaderRef.value?.hide(),
});

let requestCount = 0;

const updateLoaderVisibility = () => {
  if (requestCount <= 0) {
    requestCount = 0;
    loaderRef.value?.hide();
  }
};

apiClient.interceptors.request.use(
  (config) => {
    requestCount++;
    loaderRef.value?.show();

    return config;
  },
  (error) => {
    requestCount--;
    updateLoaderVisibility();

    return Promise.reject(error);
  }
);

apiClient.interceptors.response.use(
  (response) => {
    requestCount--;
    updateLoaderVisibility();

    return response;
  },
  (error) => {
    requestCount--;
    updateLoaderVisibility();

    return Promise.reject(error);
  }
);
</script>
