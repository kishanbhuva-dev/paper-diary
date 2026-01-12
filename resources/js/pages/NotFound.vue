<template>
  <div
    class="flex items-center justify-center min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100"
  >
    <div class="py-20 px-4 text-center">
      <div class="max-w-lg mx-auto">
        <div class="relative">
          <h1
            class="text-[160px] md:text-[200px] font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-indigo-600 leading-none -mt-10 -mb-4 relative z-10"
          >
            404
          </h1>
          <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-blue-200 rounded-full blur-3xl opacity-50"
          ></div>
        </div>

        <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mt-8 mb-4">
          Oops! Page Not Found
        </h2>

        <p class="text-lg text-gray-600 leading-relaxed mb-12 max-w-md mx-auto">
          It seems the page you were looking for has taken a detour. Don't
          worry, we'll help you find your way back.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link
            to="/"
            class="inline-flex items-center justify-center rounded-full bg-blue-600 px-8 py-3.5 font-semibold text-white shadow-lg transition-all duration-300 ease-in-out hover:scale-105 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300"
          >
            <Icon icon="lucide:home" class="mr-2 h-5 w-5" />
            <span>Back to Home</span>
          </router-link>

          <button
            class="cursor-pointer inline-flex items-center justify-center rounded-full border-2 border-gray-300 bg-white px-8 py-3.5 font-semibold text-gray-700 shadow-md transition-all duration-300 ease-in-out hover:scale-105 hover:border-gray-400 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-200"
            @click="goBack"
          >
            <Icon icon="lucide:arrow-left" class="mr-2 h-5 w-5" />
            <span>Go Back</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from "vue-router";
import { Icon } from "@iconify/vue";

const router = useRouter();

const goBack = () => {
  if (window.history.length > 1) {
    router.go(-1);
  } else {
    router.push("/");
  }
};
</script>
