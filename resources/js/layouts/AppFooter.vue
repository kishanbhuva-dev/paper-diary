<template>
  <footer class="bg-blue-100 text-gray-700 border-t border-gray-100">
    <div class="container mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-5 gap-y-12 lg:gap-x-16">
        <div class="flex flex-col space-y-4 lg:col-span-3">
          <div class="flex flex-col space-y-4">
            <router-link
              to="/"
              class="w-16"
            >
              <img
                src="/main_logo.png"
                alt="Paper Diary"
              />
            </router-link>

            <h2 class="text-3xl font-extrabold text-gray-900 leading-tight">
              Your Complex Booking World, Made
              <span class="text-primary">Effortlessly Simple.</span>
            </h2>
          </div>

          <p class="text-base text-gray-500 max-w-xl">
            Lorem ipsum dolor, sit amet consectetur adipisicing elit. Ab nam quae ipsa ad est
            provident veritatis laboriosam eveniet ipsum aut, consectetur ratione, reiciendis
            aspernatur! Sapiente totam sed molestias ullam atque beatae voluptatibus nemo soluta?
          </p>

          <div class="flex space-x-3 pt-2">
            <a
              v-for="social in socialLinks"
              :key="social.icon"
              :href="social.href"
              target="_blank"
              class="p-2 rounded-full text-primary bg-white hover:bg-primary hover:text-white shadow-md transition duration-300 ease-in-out transform hover:scale-105"
            >
              <Icon
                :icon="social.icon"
                class="w-5 h-5"
              />
            </a>
          </div>
        </div>

        <div class="flex flex-col space-y-6 lg:col-span-2 lg:mt-3">
          <div class="flex flex-col space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 border-b border-blue-200 pb-2">
              Contact Us
            </h3>

            <div class="flex items-start space-x-3">
              <Icon
                icon="lucide:map-pin"
                class="w-6 h-6 text-primary flex-shrink-0 mt-1"
              />
              <p class="text-base text-gray-700">
                <span class="font-semibold text-gray-800">Location:</span><br />
                8502 Preston Rd. Inglewood, Maine 98380, USA
              </p>
            </div>

            <div class="flex items-center space-x-3">
              <Icon
                icon="lucide:mail"
                class="w-6 h-6 text-primary flex-shrink-0"
              />
              <a
                href="mailto:support@fintech.co"
                class="text-base text-gray-700 hover:text-primary font-medium transition"
              >
                support@paperdiary.co
              </a>
            </div>
          </div>

          <form class="flex w-full max-w-md shadow-lg rounded-lg">
            <input
              type="email"
              placeholder="Enter your email address."
              class="flex-grow h-12 px-4 text-sm text-gray-700 bg-white border-none rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <button
              type="submit"
              class="h-12 px-6 text-white text-sm font-semibold bg-primary rounded-r-lg hover:bg-primary-dark transition duration-300 whitespace-nowrap"
            >
              Get in touch
            </button>
          </form>
        </div>
      </div>

      <hr class="my-6 border-gray-200" />

      <div
        class="flex flex-col md:flex-row justify-between items-center text-sm space-y-4 md:space-y-0"
      >
        <nav class="flex space-x-8">
          <a
            v-for="link in footerLinks"
            :key="link.text"
            :href="link.href"
            class="text-gray-600 hover:text-primary font-medium transition duration-150"
          >
            {{ link.text }}
          </a>
        </nav>

        <p class="text-gray-500">© Copyright 2026. All rights reserved.</p>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { Icon } from '@iconify/vue';

const footerLinks = [
  { text: 'Home', href: '/' },
  { text: 'About us', href: '/' },
  { text: 'Blog', href: '/' },
  { text: 'Center', href: '/' },
];

const socialLinks = [
  { icon: 'lucide:twitter', href: 'https://twitter.com' },
  { icon: 'lucide:facebook', href: 'https://facebook.com' },
  { icon: 'lucide:instagram', href: 'https://instagram.com' },
  { icon: 'lucide:github', href: 'https://github.com' },
];
</script>
