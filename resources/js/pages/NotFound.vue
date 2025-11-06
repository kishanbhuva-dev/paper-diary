<template>
    <div class="py-20 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <!-- 404 Number with Elegant Design -->
            <div class="relative mb-12">
                <div class="relative">
                    <h1
                        class="text-8xl md:text-[10rem] font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-purple-600 to-green-600 leading-none tracking-tight"
                    >
                        404
                    </h1>
                    <!-- Subtle glow effect -->
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-blue-600/20 via-purple-600/20 to-green-600/20 blur-3xl rounded-full scale-150"
                    ></div>
                </div>

                <!-- Decorative elements -->
                <div
                    class="absolute -top-4 -left-4 w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full opacity-60 animate-pulse"
                ></div>
                <div
                    class="absolute -top-2 -right-2 w-6 h-6 bg-gradient-to-br from-green-400 to-green-600 rounded-full opacity-60 animate-pulse"
                    style="animation-delay: 1s"
                ></div>
                <div
                    class="absolute -bottom-4 left-1/2 transform -translate-x-1/2 w-4 h-4 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full opacity-60 animate-pulse"
                    style="animation-delay: 2s"
                ></div>
            </div>

            <!-- Error Message -->
            <div class="mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-6 font-serif">Page Not Found</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto leading-relaxed">
                    The page you're looking for seems to have wandered off into the wilderness. Don't worry, even the
                    best explorers sometimes take a wrong turn!
                </p>
            </div>
            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-6 justify-center items-center mb-16">
                <router-link
                    to="/"
                    class="group relative inline-flex items-center px-10 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 ease-out overflow-hidden"
                >
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-blue-700 to-blue-800 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                    ></div>
                    <svg
                        class="w-6 h-6 mr-3 relative z-10 group-hover:rotate-12 transition-transform duration-300"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                        ></path>
                    </svg>
                    <span class="relative z-10">Back to Home</span>
                </router-link>

                <button
                    @click="goBack"
                    class="group relative inline-flex items-center px-10 py-4 border-2 border-gray-300 text-gray-700 font-semibold rounded-2xl hover:border-gray-400 hover:bg-gray-50 transform hover:-translate-y-1 transition-all duration-300 ease-out overflow-hidden"
                >
                    <div
                        class="absolute inset-0 bg-gray-100 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                    ></div>
                    <svg
                        class="w-6 h-6 mr-3 relative z-10 group-hover:-translate-x-1 transition-transform duration-300"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"
                        ></path>
                    </svg>
                    <span class="relative z-10">Go Back</span>
                </button>
            </div>

            <!-- Helpful Links Section -->
            <div class="bg-white/50 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-white/20">
                <h3 class="text-xl font-semibold text-gray-700 mb-6">Looking for something specific?</h3>
                <div class="flex flex-wrap justify-center gap-6">
                    <router-link
                        to="/search"
                        class="group flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300"
                    >
                        <svg
                            class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform duration-300"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            ></path>
                        </svg>
                        Search Properties
                    </router-link>

                    <router-link
                        to="/contact"
                        class="group flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300"
                    >
                        <svg
                            class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform duration-300"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                            ></path>
                        </svg>
                        Contact Support
                    </router-link>

                    <router-link
                        to="/faq"
                        class="group flex items-center px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300"
                    >
                        <svg
                            class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform duration-300"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            ></path>
                        </svg>
                        FAQ
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { useRouter } from 'vue-router';

    const router = useRouter();

    const goBack = () => {
        if (window.history.length > 1) {
            router.go(-1);
        } else {
            router.push('/');
        }
    };
</script>

<style scoped>
    /* Custom animations */
    @keyframes float {
        0%,
        100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-20px);
        }
    }

    .animate-float {
        animation: float 6s ease-in-out infinite;
    }

    /* Gradient text animation */
    @keyframes gradient-shift {
        0%,
        100% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
    }

    .bg-gradient-to-r {
        background-size: 200% 200%;
        animation: gradient-shift 3s ease infinite;
    }

    /* Smooth transitions for all interactive elements */
    * {
        transition-property: all;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>
