<template>
  <div class="min-h-screen flex items-center justify-center px-4">
    <div
      class="bg-white rounded-2xl shadow-xl w-full max-w-6xl overflow-hidden flex flex-col md:flex-row"
    >
      <!-- Left: Form -->
      <div class="w-full p-10 flex flex-col justify-center">
        <header class="flex justify-between items-center mb-8">
          <div class="flex items-center space-x-2">
            <img src="/public/logo.jpg" alt="Logo" class="w-8 h-8" />
            <h1 class="text-2xl font-bold text-gray-800">Paper Diary</h1>
          </div>
          <router-link
            v-if="$route.name === 'register'"
            to="/login"
            class="border border-gray-300 text-sm px-4 py-1 rounded-full text-gray-600 hover:bg-gray-100 transition"
          >
            Login
          </router-link>
          <router-link
            v-else
            to="/register"
            class="border border-gray-300 text-sm px-4 py-1 rounded-full text-gray-600 hover:bg-gray-100 transition"
          >
            Register
          </router-link>
        </header>

        <main>
          <router-view />
        </main>
      </div>

      <!-- Right: Illustration -->
      <!-- <div
        class="hidden md:flex md:w-1/2 items-center justify-center bg-gray-50"
      >
        <img
          src="/public/image.png"
          alt="Illustration"
          class="max-w-md w-full"
        />
      </div> -->
    </div>
  </div>
</template>
