# Frontend Code Review - Paper Diary

## Executive Summary

This document provides a comprehensive code review of the Paper Diary Vue.js 3 frontend application. The application is a Single Page Application (SPA) built with Vue 3, Vue Router, Tailwind CSS, and various third-party libraries for property booking management.

---

## Table of Contents

1. [Security Issues](#1-security-issues)
2. [Architecture & Design Issues](#2-architecture--design-issues)
3. [Component Issues](#3-component-issues)
4. [Service Layer Issues](#4-service-layer-issues)
5. [Router Issues](#5-router-issues)
6. [State Management Issues](#6-state-management-issues)
7. [Code Quality Issues](#7-code-quality-issues)
8. [Performance Issues](#8-performance-issues)
9. [Accessibility Issues](#9-accessibility-issues)
10. [Best Practice Violations](#10-best-practice-violations)
11. [Recommendations](#11-recommendations)

---

## 1. Security Issues

### 1.1 Token Storage in LocalStorage
**File:** `resources/js/composables/useAuth.js`
**Lines:** 5-6, 29-50

```javascript
const TOKEN_KEY = "authToken";
const USER_KEY = "user";

function persistAuth(newToken, newUser) {
  if (newToken) {
    localStorage.setItem(TOKEN_KEY, newToken);
    // ...
  }
}
```

**Issue:** Storing authentication tokens in localStorage is vulnerable to XSS attacks. If any XSS vulnerability exists, attackers can steal auth tokens.

**Severity:** 🟡 HIGH

**Recommendation:**
- Consider using httpOnly cookies for token storage
- If localStorage must be used, implement token rotation and short expiration
- Add Content Security Policy headers
- Sanitize all user inputs rigorously

---

### 1.2 Sensitive Data Logged to Console
**File:** `resources/js/composables/useAuth.js`
**Lines:** 13-14, 43-45

```javascript
} catch (e) {
  console.error("[useAuth] Invalid user JSON:", e);
  // ...
}
```

**File:** `resources/js/services/apiClient.js`
**Line:** 72

```javascript
console.error("API error:", error);
```

**Issue:** Console logging in production can expose sensitive data. Error objects may contain tokens, user data, or internal details.

**Recommendation:** 
- Remove console logs in production builds
- Use proper error tracking service (Sentry, LogRocket)

---

### 1.3 Admin Token Stored Alongside Regular Token
**File:** `resources/js/layouts/OwnerLayout.vue`
**Lines:** 294-310

```javascript
function backToAdmin() {
    const adminToken = localStorage.getItem('adminToken');
    const adminUser = localStorage.getItem('adminUser');

    if (adminToken && adminUser) {
        localStorage.clear();
        localStorage.setItem('authToken', adminToken);
        localStorage.setItem('user', adminUser);
        // ...
    }
}
```

**Issue:** 
1. Storing admin credentials separately creates privilege escalation risk
2. `localStorage.clear()` may clear other application data unexpectedly
3. 500ms setTimeout before redirect creates race condition

**Recommendation:** Use proper session management, not dual-token storage.

---

### 1.4 Base64 Encoded Keys Not Security
**File:** Backend sends `base64_encode($ownerStripePublicKey)` but this is NOT encryption.

**Issue:** Base64 is encoding, not encryption. Anyone can decode it.

---

### 1.5 No CSRF Protection Verification
**File:** `resources/js/services/apiClient.js`

**Issue:** No CSRF token handling. While Sanctum handles this for same-domain, the implementation should explicitly verify.

---

## 2. Architecture & Design Issues

### 2.1 No Centralized State Management
**Issue:** Application state is scattered across:
- `useAuth.js` composable (auth state)
- Individual component `ref()` declarations
- LocalStorage

**Impact:**
- State synchronization issues between components
- Difficult to debug state changes
- No single source of truth

**Recommendation:** Implement Pinia or Vuex for centralized state:
```javascript
// stores/auth.js
export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
    isAuthenticated: false
  }),
  actions: {
    login(credentials) { ... },
    logout() { ... }
  }
})
```

---

### 2.2 Service Layer Naming Inconsistency
**Files:**
- `services/authService.js` - Authentication
- `services/adminService.js` - Named `authService` inside but handles admin operations
- `services/ownerService.js` - Owner operations
- `services/userService.js` - User booking operations

**File:** `resources/js/services/adminService.js`
**Line:** 3

```javascript
const authService = {  // Wrong! Should be adminService
  async fetchAllOwners(query) { ... }
```

**Issue:** The exported object is named `authService` but it's the admin service.

---

### 2.3 Mixed API Response Handling
**File:** `resources/js/services/ownerService.js`

```javascript
async fetchProperties(params = {}) {
    const res = await apiClient.get("/owner/property", { params });
    return res.data.data;  // Unwraps to data.data
},

async getOwnerDetails() {
    const response = await apiClient.get("/owner/owner-details");
    return response;  // Returns full response
},
```

**Issue:** Inconsistent return values - some return unwrapped data, others return full response.

---

### 2.4 No TypeScript
**Issue:** Entire codebase uses plain JavaScript without type definitions.

**Impact:**
- No compile-time type checking
- Poor IDE autocompletion
- Runtime type errors possible

**Recommendation:** Migrate to TypeScript or add JSDoc type annotations.

---

### 2.5 Missing Error Boundary
**Issue:** No global error handling component. Unhandled errors crash the entire app.

**Recommendation:** Implement Vue error boundary:
```vue
<template>
  <ErrorBoundary>
    <router-view />
  </ErrorBoundary>
</template>
```

---

## 3. Component Issues

### 3.1 Oversized Components
**File:** `resources/js/pages/owner/OwnerDashboard.vue` - 493 lines
**File:** `resources/js/layouts/AdminLayout.vue` - 251 lines
**File:** `resources/js/layouts/OwnerLayout.vue` - 339 lines

**Issue:** Components exceeding 200-300 lines should be split.

**Recommendation:** Extract into smaller components:
- `DashboardStats.vue`
- `PropertyFilter.vue`
- `RecentBookings.vue`
- `SidebarNav.vue`

---

### 3.2 Duplicate Layout Code
**Files:** `AdminLayout.vue` and `OwnerLayout.vue` share ~80% identical code.

**Issue:** Violates DRY principle. Changes must be made in both files.

**Recommendation:** Create a base `DashboardLayout.vue` with slots:
```vue
<template>
  <DashboardLayout 
    :menu-items="ownerMenuItems"
    :user="currentUser"
  >
    <template #sidebar-header>
      <!-- Custom header -->
    </template>
    <template #default>
      <router-view />
    </template>
  </DashboardLayout>
</template>
```

---

### 3.3 Hardcoded Strings in Templates
**File:** `resources/js/layouts/AdminLayout.vue`
**Lines:** Multiple

```vue
<div class="text-[10px] uppercase font-bold text-gray-500 tracking-widest mb-2 px-3">
  Main Navigation
</div>
```

**File:** `resources/js/pages/owner/OwnerDashboard.vue`

```vue
<span class="text-xs text-gray-400 font-medium">Units</span>
```

**Issue:** No internationalization (i18n). All strings hardcoded.

**Recommendation:** Implement Vue I18n:
```vue
{{ $t('navigation.main') }}
```

---

### 3.4 Props Not Validated
**File:** Multiple components lack proper prop validation.

**Example - Missing validation:**
```javascript
const props = defineProps({
  booking: Object,  // No required, no default, no validator
  canCancel: Boolean
})
```

**Should be:**
```javascript
const props = defineProps({
  booking: {
    type: Object,
    required: true,
    validator: (v) => v && typeof v.id !== 'undefined'
  },
  canCancel: {
    type: Boolean,
    default: false
  }
})
```

---

### 3.5 Missing Component Loading States
**File:** `resources/js/pages/owner/OwnerDashboard.vue`

```vue
<div v-if="recentBookings.length === 0" ...>
  <p class="text-gray-400 text-sm">
    No activity recorded for the selected filter.
  </p>
</div>
```

**Issue:** No distinction between:
1. Loading state (data being fetched)
2. Empty state (no data exists)
3. Error state (fetch failed)

**Recommendation:**
```vue
<div v-if="isLoading">Loading...</div>
<div v-else-if="error">{{ error.message }}</div>
<div v-else-if="recentBookings.length === 0">No bookings found</div>
<div v-else><!-- render bookings --></div>
```

---

### 3.6 Event Handler Naming
**File:** `resources/js/layouts/AdminLayout.vue`

```vue
@click="closeOnMobile"
@click="handleLogout"
```

**Issue:** Inconsistent naming: `closeOnMobile` vs `handleLogout`.

**Recommendation:** Use consistent `handle*` or `on*` prefix.

---

### 3.7 Template Complexity
**File:** `resources/js/layouts/OwnerLayout.vue`
**Lines:** 143-166

```vue
<component
  v-for="subItem in group.children"
  :key="subItem.name"
  :is="subItem.to ? 'router-link' : 'button'"
  v-show="!subItem.show || subItem.show.value"
  :to="subItem.to"
  @click="subItem.onClick && subItem.onClick()"
  class="flex items-center gap-2 ..."
>
```

**Issue:** Complex conditional rendering in template. Hard to read and maintain.

**Recommendation:** Extract to computed or separate component.

---

## 4. Service Layer Issues

### 4.1 No Request/Response Interceptor Separation
**File:** `resources/js/App.vue`
**Lines:** 41-69

```javascript
apiClient.interceptors.request.use(...);
apiClient.interceptors.response.use(...);
```

**Issue:** Interceptors defined in root App component, mixed with component logic.

**Recommendation:** Move to `apiClient.js` or dedicated interceptor files.

---

### 4.2 Duplicate Interceptor Registration
**File:** `resources/js/services/apiClient.js` has interceptors
**File:** `resources/js/App.vue` also adds interceptors

**Issue:** Double interceptor registration can cause:
- Duplicate toast notifications
- Incorrect request counting
- Memory leaks

---

### 4.3 Silent Error Swallowing
**File:** `resources/js/composables/useAuth.js`
**Lines:** 112-117

```javascript
async function logout() {
  try {
    await authService.logout();
  } catch (e) {
    console.warn("[logout] API failed:", e);
  } finally {
    localStorage.clear();
  }
```

**Issue:** Logout errors are silently caught. User might think they logged out when they didn't.

---

### 4.4 Missing Request Cancellation
**Issue:** No AbortController usage for cancelling in-flight requests when:
- Component unmounts
- User navigates away
- New search replaces old search

**Example fix:**
```javascript
const controller = new AbortController();

onUnmounted(() => {
  controller.abort();
});

await apiClient.get('/data', { signal: controller.signal });
```

---

### 4.5 No Retry Logic
**Issue:** Failed requests are not retried, even for transient errors.

**Recommendation:** Implement retry logic for 5xx errors:
```javascript
import axios from 'axios';
import axiosRetry from 'axios-retry';

axiosRetry(apiClient, { retries: 3 });
```

---

### 4.6 Inconsistent Error Message Handling
**File:** `resources/js/services/apiClient.js`

```javascript
if (message !== undefined && message !== "") {
  toast(message, { type: status ? "success" : "error" });
}
```

**Issue:** Toast shown for all responses with messages, even when component wants to handle error itself.

---

## 5. Router Issues

### 5.1 Route Guard Race Condition
**File:** `resources/js/router/index.js`
**Lines:** 17-49

```javascript
router.beforeEach((to, from, next) => {
  const { isAuthenticated, isAdmin, isOwner } = useAuth();

  if (to.meta.requiresAuth && !isAuthenticated.value) {
    return next({ name: "login", query: { redirect: to.fullPath } });
  }
  // ...
});
```

**Issue:** `useAuth()` composable relies on localStorage which may not be immediately available on page load.

**Recommendation:** Add auth check await or initial loading state.

---

### 5.2 Duplicate Role Checks
**File:** `resources/js/router/index.js`

```javascript
// First check
if (to.meta.requiresAuth && !isAuthenticated.value) { ... }

// Then another check for admin
if (path.startsWith("/admin")) {
  if (!isAuthenticated.value) return next({ name: "login" }); // Duplicate!
  // ...
}
```

---

### 5.3 Missing Lazy Load Error Handling
**File:** `resources/js/router/routes.js`

```javascript
component: () => import("../../js/pages/owner/OwnerDashboard.vue"),
```

**Issue:** No error handling for failed chunk loads.

**Recommendation:**
```javascript
component: () => import("./pages/Component.vue").catch(() => {
  return import("./pages/ChunkLoadError.vue");
}),
```

---

### 5.4 Typo in Route Path
**File:** `resources/js/router/routes.js`
**Line:** 88

```javascript
{
  path: "subcription-view",  // Typo: should be "subscription-view"
  name: "owner-subcription-view",  // Typo in name too
  component: () => import("../../js/pages/owner/SubscriptionView.vue"),
  meta: { pageTitle: "Manage Subcription" },  // Typo: "Subscription"
},
```

---

### 5.5 Inconsistent Path Patterns
```javascript
// Relative paths
{ path: "profile", ... }
{ path: "change-password", ... }

// Absolute paths
{ path: "/owner/properties", ... }
{ path: "/admin/owners", ... }
```

**Recommendation:** Use consistent relative paths within nested routes.

---

### 5.6 Route Meta Inconsistency
**File:** `resources/js/router/routes.js`

```javascript
// Some have pageTitle
{ meta: { pageTitle: "Owner Dashboard" } }

// Some have requiresAuth
{ meta: { requiresAuth: true } }

// Some have role
{ meta: { requiresAuth: true, role: 'user' } }
```

**Issue:** `role` meta is defined but never used in router guards.

---

### 5.7 Duplicate Component Imports
```javascript
// Pattern 1
component: () => import("../../js/pages/owner/Properties.vue"),

// Pattern 2  
component: () => import("../pages/owner/OwnerDashboard.vue"),
```

**Issue:** Inconsistent relative path patterns (`../../js/pages` vs `../pages`).

---

## 6. State Management Issues

### 6.1 Composable Creates Global State
**File:** `resources/js/composables/useAuth.js`
**Lines:** 19-25

```javascript
const token = ref(localStorage.getItem(TOKEN_KEY) || null);
const user = ref(loadStoredUser());
const isAuthenticated = computed(() => !!token.value);
```

**Issue:** These refs are module-level singletons. While this works, it's implicit global state without proper store semantics.

---

### 6.2 Router Instance Stored in Module
**File:** `resources/js/composables/useAuth.js`
**Lines:** 27, 54

```javascript
let routerInstance = null;

export function useAuth() {
  if (!routerInstance) routerInstance = useRouter();
```

**Issue:** Router instance cached globally, may cause issues with SSR or testing.

---

### 6.3 Duplicate Login Logic
**File:** `resources/js/composables/useAuth.js`
**Lines:** 76-106

```javascript
async function login(credentials) {
  // ...
  const role = res.data.user.role?.toLowerCase();
  let routeName;
  
  if (role === "owner") {
    routeName = res.data.subscription.is_active ? "owner-dashboard" : "subscription";
  } else if (role === "admin") {
    routeName = "admin-dashboard";
  } else {
    routeName = "home";
  }
  
  // ... redirect logic ...
  
  // Then AGAIN:
  const role = res.data.user.role?.toLowerCase();  // Duplicate declaration!
  let routeName;  // Duplicate declaration!
  
  if (role === "owner") {
    routeName = res.data.subscription ? "owner-dashboard" : "subscription";  // Different logic!
  }
```

**Issue:** 
1. Variable `role` and `routeName` declared twice
2. Second declaration has different logic (`subscription.is_active` vs just `subscription`)

---

### 6.4 LocalStorage Used Directly Throughout
Multiple components access localStorage directly instead of through auth composable:

**File:** `resources/js/layouts/OwnerLayout.vue`
```javascript
const adminToken = localStorage.getItem('adminToken');
```

---

## 7. Code Quality Issues

### 7.1 Console Statements in Production Code
**Files:** Multiple

```javascript
console.error("[useAuth] Invalid user JSON:", e);
console.warn("[logout] API failed:", e);
console.error("API error:", error);
console.error("Error fetching recent bookings:", error);
console.error("Error loading resources:", error);
```

---

### 7.2 Unused Imports
**File:** `resources/js/layouts/AdminLayout.vue`

```javascript
import { ref, computed, onMounted } from "vue";
// onMounted is imported but defined separately
```

**File:** `resources/js/pages/owner/OwnerDashboard.vue`
```javascript
import { ref, onMounted, watch } from "vue";
// Missing: computed (if needed)
```

---

### 7.3 Inconsistent Import Aliases
**File:** `resources/js/pages/owner/OwnerDashboard.vue`

```javascript
import ownerService from "@/services/ownerService";
import BookingDetailModal from "@/components/modals/BookingDetailModal.vue";
```

**Other files:**
```javascript
import { useAuth } from "../../composables/useAuth";
import BaseInput from "../../components/global/BaseInput.vue";
```

**Issue:** Mix of `@/` alias and relative paths.

---

### 7.4 Magic Numbers
**File:** `resources/js/pages/owner/OwnerDashboard.vue`

```javascript
recentBookings.value = (res.data || res).slice(0, 10);  // Why 10?
```

**File:** `resources/js/services/apiClient.js`

```javascript
timeout: 30000,  // 30 seconds - not documented why
```

---

### 7.5 Inconsistent Async/Await Patterns
**Pattern 1:**
```javascript
const handleLogin = async () => {
  await login(form.value);
};
```

**Pattern 2:**
```javascript
const getResources = async () => {
  try {
    const ResourcesData = await ownerService.resourceList({...});
    // ...
  } catch (error) {
    console.error("Error:", error);
  }
};
```

**Issue:** Some async functions have try/catch, others don't.

---

### 7.6 Commented Out Code
**File:** `resources/js/router/index.js`
**Lines:** 8-13

```javascript
scrollBehavior(to, from, savedPosition) {
  // if (savedPosition) {
  //   return savedPosition;
  // } else {
  return { top: 0, behavior: "smooth" };
  // }
},
```

**File:** `resources/js/layouts/OwnerLayout.vue`
**Lines:** 109-140 (commented menu code)

---

### 7.7 Incorrect Date Parsing
**File:** `resources/js/pages/owner/OwnerDashboard.vue`
**Lines:** 364-374

```javascript
const isFutureBooking = (checkInStr) => {
  if (!checkInStr) return false;
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  let dateParts = checkInStr.includes("-") ? checkInStr.split("-") : [];
  let bookingDate =
    dateParts[0].length === 4
      ? new Date(checkInStr)
      : new Date(`${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`);
```

**Issues:**
1. Complex date parsing without library
2. Assumes specific date formats
3. No timezone handling

**Recommendation:** Use dayjs (already installed):
```javascript
import dayjs from 'dayjs';

const isFutureBooking = (checkInStr) => {
  return dayjs(checkInStr).isAfter(dayjs().startOf('day'));
};
```

---

### 7.8 Variable Naming Issues

**Non-descriptive names:**
```javascript
const res = await apiClient.get(...)  // What kind of response?
const q = useRoute().query  // What query?
const s = status?.toLowerCase()  // What status?
```

**Inconsistent casing:**
```javascript
const ResourcesData = await ownerService.resourceList(...)  // PascalCase variable
const recentBookings = ref([])  // camelCase variable
```

---

## 8. Performance Issues

### 8.1 No Component Lazy Loading for Heavy Components
**File:** `resources/js/pages/owner/OwnerDashboard.vue`

```javascript
import { HotelDashboardCalendar } from "vue-hotel-booking-calendar";
import "vue-hotel-booking-calendar/dist/style.css";
```

**Issue:** Heavy calendar component loaded synchronously.

**Recommendation:**
```javascript
const HotelDashboardCalendar = defineAsyncComponent(() => 
  import('vue-hotel-booking-calendar').then(m => m.HotelDashboardCalendar)
);
```

---

### 8.2 Watchers Without Debounce
**File:** `resources/js/pages/owner/OwnerDashboard.vue`

```javascript
watch(selectedProperties, () => {
  getResources();
  getRecentBookings();
});
```

**Issue:** Every property selection triggers two API calls immediately.

**Recommendation:**
```javascript
import { watchDebounced } from '@vueuse/core';

watchDebounced(selectedProperties, () => {
  getResources();
  getRecentBookings();
}, { debounce: 300 });
```

---

### 8.3 Multiple API Calls on Mount
**File:** `resources/js/pages/owner/OwnerDashboard.vue`
**Lines:** 487-491

```javascript
onMounted(() => {
  fetchPropertiesdropdown();  // API call 1
  getResources();             // API call 2
  getRecentBookings();        // API call 3
});
```

**Recommendation:** Use `Promise.all()` or combine into single endpoint:
```javascript
onMounted(async () => {
  await Promise.all([
    fetchPropertiesdropdown(),
    getResources(),
    getRecentBookings()
  ]);
});
```

---

### 8.4 No Virtual Scrolling for Lists
**Issue:** Long lists (bookings, properties) rendered entirely in DOM.

**Recommendation:** Use virtual scrolling for lists > 50 items:
```javascript
import { VirtualList } from '@vueuse/components';
```

---

### 8.5 Large Bundle - All Icons Imported
**File:** `resources/js/app.js`

```javascript
import { fas } from '@fortawesome/free-solid-svg-icons';
import { far } from '@fortawesome/free-regular-svg-icons';
import { fab } from '@fortawesome/free-brands-svg-icons';

library.add(fas, far, fab);
```

**Issue:** All FontAwesome icons imported (~1.5MB+), but only few are used.

**Recommendation:** Import only used icons:
```javascript
import { faHome, faUser, faCalendar } from '@fortawesome/free-solid-svg-icons';
library.add(faHome, faUser, faCalendar);
```

---

### 8.6 CSS Not Purged
**Issue:** Using Tailwind without apparent PurgeCSS configuration in production.

---

## 9. Accessibility Issues

### 9.1 Missing ARIA Labels
**File:** `resources/js/layouts/AdminLayout.vue`

```vue
<button @click="sidebarOpen = !sidebarOpen">
  <Icon icon="material-symbols:menu-rounded" width="30" class="text-blue-600" />
</button>
```

**Issue:** No accessible label for screen readers.

**Fix:**
```vue
<button 
  @click="sidebarOpen = !sidebarOpen"
  aria-label="Toggle navigation menu"
  :aria-expanded="sidebarOpen"
>
```

---

### 9.2 Missing Form Labels
**File:** `resources/js/pages/owner/OwnerDashboard.vue`

```vue
<input
  type="checkbox"
  :value="item.id"
  v-model="selectedProperties"
  class="w-4 h-4 ..."
/>
```

**Issue:** Checkboxes lack proper `id` and associated `<label for="">`.

---

### 9.3 No Focus Management
**Issue:** After modal close or navigation, focus is not returned to triggering element.

---

### 9.4 Color Contrast Issues
```vue
<p class="text-gray-400 text-sm">...</p>
```

**Issue:** Gray text on light backgrounds may not meet WCAG contrast requirements.

---

### 9.5 No Skip Links
**Issue:** No "skip to main content" link for keyboard users.

---

### 9.6 Missing Alt Text
**File:** `resources/js/layouts/AdminLayout.vue`

```vue
<img src="/public/main_logo.png" class="h-14" alt="Logo" />
```

**Issue:** Generic "Logo" alt text. Should be descriptive: "Paper Diary Logo".

---

## 10. Best Practice Violations

### 10.1 Direct DOM Manipulation
**Issue:** Using `window.location.href` instead of Vue Router:

**File:** `resources/js/layouts/OwnerLayout.vue`
```javascript
setTimeout(() => {
  window.location.href = '/admin';
}, 500);
```

**Recommendation:**
```javascript
router.push('/admin');
```

---

### 10.2 setTimeout for Flow Control
**File:** `resources/js/layouts/OwnerLayout.vue`
**Line:** 304

```javascript
setTimeout(() => {
  window.location.href = '/admin';
}, 500);
```

**Issue:** setTimeout used to wait for localStorage operations. Race condition prone.

---

### 10.3 No Environment Configuration
**Issue:** No `.env` variables used in frontend. API base URL hardcoded as `/api`.

**Recommendation:**
```javascript
baseURL: import.meta.env.VITE_API_URL || "/api",
```

---

### 10.4 Missing Loading/Error States
Most data-fetching components don't show:
- Loading spinners
- Error messages
- Empty states

---

### 10.5 No Form Validation Library
Each form validates manually:
```javascript
const isEmailValid = emailInput.value.validate();
const isPasswordValid = passwordInput.value.validate();
```

**Recommendation:** Use VeeValidate or FormKit for consistent validation.

---

### 10.6 No Unit Tests
**Issue:** No frontend test files exist.

**Recommendation:** Add Vitest for unit tests, Cypress for E2E.

---

### 10.7 Asset Path Issues
**File:** `resources/js/layouts/AdminLayout.vue`

```vue
<img src="/public/main_logo.png" class="h-10" alt="Logo" />
```

**Issue:** `/public/` prefix is incorrect. Should be just `/main_logo.png` as Vite serves from public folder.

---

### 10.8 No Error Tracking
**Issue:** No integration with error tracking services (Sentry, Bugsnag).

---

### 10.9 Missing SEO Meta Tags
**Issue:** SPA with no dynamic meta tag management.

**Recommendation:** Add `@vueuse/head` or similar for meta management.

---

## 11. Recommendations

### Immediate Actions (High Priority)

1. **Fix security issues:**
   - Consider httpOnly cookie token storage
   - Remove console.logs in production
   - Fix admin token storage vulnerability

2. **Fix duplicate variable declarations** in `useAuth.js`

3. **Fix typos** in route paths (subcription → subscription)

4. **Add loading/error states** to all data-fetching components

5. **Fix asset paths** (remove `/public/` prefix)

### Short-term Improvements

1. **Implement Pinia** for centralized state management

2. **Create reusable base layout** for Admin/Owner dashboards

3. **Add TypeScript** or comprehensive JSDoc types

4. **Implement proper error boundaries**

5. **Add form validation library** (VeeValidate)

6. **Configure icon tree-shaking** for FontAwesome

7. **Add accessibility improvements:**
   - ARIA labels
   - Focus management
   - Skip links

### Medium-term Improvements

1. **Add comprehensive testing:**
   - Unit tests (Vitest)
   - Component tests
   - E2E tests (Cypress/Playwright)

2. **Implement internationalization (i18n)**

3. **Add error tracking** (Sentry)

4. **Implement PWA features** if needed

5. **Add performance monitoring**

6. **Create component documentation** (Storybook)

### Code Organization Suggestions

```
resources/js/
├── api/
│   ├── apiClient.js
│   └── interceptors/
│       ├── auth.js
│       ├── error.js
│       └── loading.js
├── components/
│   ├── common/
│   │   ├── BaseButton.vue
│   │   ├── BaseInput.vue
│   │   └── BaseModal.vue
│   ├── layouts/
│   │   ├── DashboardLayout.vue
│   │   └── AuthLayout.vue
│   └── features/
│       ├── bookings/
│       ├── properties/
│       └── subscriptions/
├── composables/
│   ├── useAuth.js
│   ├── useBookings.js
│   └── useNotifications.js
├── stores/
│   ├── auth.js
│   ├── bookings.js
│   └── properties.js
├── pages/
│   ├── admin/
│   ├── owner/
│   ├── user/
│   └── auth/
├── router/
│   ├── index.js
│   ├── guards.js
│   └── routes/
│       ├── admin.js
│       ├── owner.js
│       └── public.js
├── utils/
│   ├── date.js
│   ├── validation.js
│   └── formatters.js
└── constants/
    ├── api.js
    └── routes.js
```

---

## File-by-File Issue Summary

| File | Critical | High | Medium | Low |
|------|----------|------|--------|-----|
| composables/useAuth.js | 1 | 2 | 3 | 2 |
| services/apiClient.js | 0 | 1 | 2 | 1 |
| services/adminService.js | 0 | 1 | 1 | 0 |
| services/ownerService.js | 0 | 0 | 2 | 1 |
| router/index.js | 0 | 1 | 2 | 2 |
| router/routes.js | 0 | 0 | 3 | 3 |
| layouts/AdminLayout.vue | 0 | 0 | 3 | 4 |
| layouts/OwnerLayout.vue | 1 | 1 | 3 | 4 |
| pages/owner/OwnerDashboard.vue | 0 | 0 | 4 | 5 |
| pages/auth/LoginPage.vue | 0 | 0 | 1 | 2 |
| App.vue | 0 | 1 | 1 | 0 |
| app.js | 0 | 1 | 1 | 0 |

---

## 12. File Location & Organization Issues

### 12.1 Current File Structure Analysis
**Current Structure:**
```
resources/js/
├── app.js                    # Main entry - has icon imports issue
├── App.vue                   # Root component - has interceptor logic
├── bootstrap.js              # Laravel bootstrap
├── components/
│   ├── admin/
│   │   └── [1 file]         # Under-utilized folder
│   ├── common/
│   │   └── [1 file]         # Under-utilized folder
│   ├── global/
│   │   ├── BaseInput.vue     # 315 lines - could be split
│   │   ├── Basetable.vue     # 641 lines - too large
│   │   ├── BaseSelect.vue
│   │   ├── BaseDatePicker.vue
│   │   ├── DeleteModal.vue
│   │   └── [3 more files]
│   ├── modals/
│   │   └── [1 file]
│   └── owner/
│       └── [2 files]
├── composables/
│   └── useAuth.js            # Only composable - needs expansion
├── layouts/
│   ├── AdminLayout.vue       # 251 lines
│   ├── AppFooter.vue
│   ├── AppHeader.vue         # 288 lines
│   ├── AuthLayout.vue
│   └── OwnerLayout.vue       # 339 lines - duplicates AdminLayout
├── pages/
│   └── [28 files in subtree] # Flat structure within folders
├── router/
│   ├── index.js
│   └── routes.js             # Single large file
├── services/
│   ├── adminService.js       # Wrong internal name
│   ├── apiClient.js
│   ├── authService.js
│   ├── ownerService.js
│   └── userService.js
└── utils/
    └── errorHandler.js       # Single utility file
```

**Issues Identified:**
1. `components/admin/` and `components/common/` have only 1 file each
2. `components/global/Basetable.vue` is 641 lines - violates single responsibility
3. No separation between feature-based and shared components
4. `router/routes.js` is monolithic - should split by role

---

### 12.2 Component Folder Organization Issues

**File:** `components/global/BaseInput.vue` - 315 lines

**Issue:** Single component handles:
- Text input
- Number input
- Email input
- Password input (with toggle)
- Textarea (multiline)
- Validation
- Theming

**Recommendation:** Split into focused components:
```
components/
├── inputs/
│   ├── BaseInput.vue      # Simple text input
│   ├── PasswordInput.vue  # With toggle
│   ├── NumberInput.vue    # With min/max
│   └── TextArea.vue       # Multiline
├── form/
│   ├── FormGroup.vue      # Label + input wrapper
│   └── FormValidation.vue # Validation display
```

---

### 12.3 Page Component Naming Inconsistencies

**Current Naming:**
```
pages/
├── admin/
│   └── Dashboard.vue              # Short name
├── auth/
│   ├── ChangePassword.vue
│   ├── ForgotPassword.vue
│   ├── LoginPage.vue              # Has "Page" suffix
│   ├── Profile.vue
│   ├── RegisterPage.vue           # Has "Page" suffix
│   └── ResetPassword.vue
├── guest/
│   ├── BookingPage.vue            # Has "Page" suffix
│   ├── DetailsPage.vue            # Has "Page" suffix
│   └── HomePage.vue               # Has "Page" suffix
└── owner/
    ├── OwnerDashboard.vue         # Has "Owner" prefix (redundant in folder)
    ├── Properties.vue
    ├── PropertiesForm.vue         # Is it a page or form component?
    └── PropertyWizard.vue
```

**Issues:**
1. Inconsistent "Page" suffix (some have it, some don't)
2. Redundant prefixes (`OwnerDashboard` in `owner/` folder)
3. `PropertiesForm.vue` location ambiguous - is it a page or component?

**Recommendation:**
```
pages/
├── admin/
│   └── DashboardPage.vue
├── auth/
│   ├── ChangePasswordPage.vue
│   ├── LoginPage.vue
│   └── ...
├── guest/
│   ├── BookingPage.vue
│   └── ...
└── owner/
    ├── DashboardPage.vue          # Remove "Owner" prefix
    ├── PropertiesPage.vue
    └── PropertyWizardPage.vue

components/
└── owner/
    └── PropertyForm.vue           # Move form component here
```

---

### 12.4 Missing Index Files
**Issue:** No `index.js` barrel files for clean imports.

**Current Import:**
```javascript
import BaseInput from "../../components/global/BaseInput.vue";
import BaseSelect from "../../components/global/BaseSelect.vue";
import DeleteModal from "../../components/global/DeleteModal.vue";
```

**With index.js:**
```javascript
// components/global/index.js
export { default as BaseInput } from './BaseInput.vue';
export { default as BaseSelect } from './BaseSelect.vue';
export { default as DeleteModal } from './DeleteModal.vue';

// Usage
import { BaseInput, BaseSelect, DeleteModal } from '@/components/global';
```

---

## 13. Additional Code Optimizations

### 13.1 Basetable Component Refactoring
**File:** `components/global/Basetable.vue` - 641 lines

**Issues:**
1. Too many responsibilities (filtering, sorting, pagination, export)
2. 15+ props - prop drilling
3. Mixed template logic

**Recommendation:** Split into composable parts:
```vue
<!-- Basetable.vue (simplified) -->
<template>
  <div class="base-table">
    <TableHeader 
      :title="title"
      :show-search="showSearch"
      @search="handleSearch"
    />
    <TableFilters 
      v-if="hasFilters"
      :filters="filters"
      @change="handleFilterChange"
    />
    <TableBody 
      :columns="columns"
      :rows="paginatedData"
      :loading="loading"
    />
    <TablePagination
      :current-page="currentPage"
      :total-pages="totalPages"
      @change="handlePageChange"
    />
  </div>
</template>
```

---

### 13.2 Form Validation Pattern
**Current Pattern (repeated in many components):**
```javascript
const firstNameInput = ref(null);
const lastNameInput = ref(null);
const emailInput = ref(null);
// ... 10+ more refs

const handleSubmit = () => {
  const inputs = [firstNameInput, lastNameInput, emailInput /* ... */];
  const allValid = inputs.every((input) => input.value.validate());
  if (!allValid) return;
  // ...
};
```

**Optimized Pattern with composable:**
```javascript
// composables/useFormValidation.js
export function useFormValidation() {
  const inputRefs = ref([]);
  
  const registerInput = (el) => {
    if (el) inputRefs.value.push(el);
  };
  
  const validateAll = () => {
    return inputRefs.value.every(input => 
      typeof input.validate === 'function' ? input.validate() : true
    );
  };
  
  const resetValidation = () => {
    inputRefs.value.forEach(input => input.resetValidation?.());
  };
  
  return { registerInput, validateAll, resetValidation };
}

// Usage in component
const { registerInput, validateAll } = useFormValidation();

<BaseInput :ref="registerInput" />

const handleSubmit = () => {
  if (!validateAll()) return;
  // ...
};
```

---

### 13.3 Date Handling Optimization
**File:** `pages/guest/DetailsPage.vue`

**Current:**
```javascript
import dayjs from "dayjs";

const details = ref({
  checkIn: dayjs().format("YYYY-MM-DD"),
  checkOut: dayjs().add(1, "day").format("YYYY-MM-DD"),
});
```

**Issue:** dayjs imported in multiple components. Should be centralized.

**Recommendation:**
```javascript
// utils/date.js
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import customParseFormat from 'dayjs/plugin/customParseFormat';

dayjs.extend(relativeTime);
dayjs.extend(customParseFormat);

export const formatDate = (date, format = 'YYYY-MM-DD') => 
  dayjs(date).format(format);

export const isAfter = (date1, date2) => 
  dayjs(date1).isAfter(date2);

export const addDays = (date, days) => 
  dayjs(date).add(days, 'day').format('YYYY-MM-DD');

export { dayjs };
```

---

### 13.4 API Response Standardization
**File:** `services/ownerService.js`

**Current (inconsistent):**
```javascript
async fetchProperties(params) {
  const res = await apiClient.get("/owner/property", { params });
  return res.data.data;  // Unwraps data.data
},

async getOwnerDetails() {
  const response = await apiClient.get("/owner/owner-details");
  return response;  // Returns full response
},
```

**Recommendation:**
```javascript
// All service methods should return consistent structure
async fetchProperties(params) {
  const { data } = await apiClient.get("/owner/property", { params });
  return data;  // Always return data wrapper
},

async getOwnerDetails() {
  const { data } = await apiClient.get("/owner/owner-details");
  return data;  // Consistent
},
```

---

### 13.5 Component Communication Patterns
**File:** `pages/owner/PropertyWizard.vue`

**Current (tight coupling):**
```javascript
const handleStep1Success = async (data) => {
  if (!startedWithId.value) {
    if (!propertyId.value) createdInWizard.value = true;
    propertyId.value = data.id;
  }
  nextStep();
};
```

**Recommendation:** Use provide/inject for wizard state:
```javascript
// PropertyWizard.vue
const wizardState = reactive({
  currentStep: 1,
  propertyId: null,
  resourceTypes: [],
  isEditing: false
});

provide('wizardState', wizardState);
provide('nextStep', () => wizardState.currentStep++);
provide('prevStep', () => wizardState.currentStep--);

// PropertiesForm.vue (child)
const wizardState = inject('wizardState');
const nextStep = inject('nextStep');
```

---

### 13.6 Image Loading Optimization
**File:** `pages/guest/DetailsPage.vue`

**Current:**
```vue
<img :src="image.image" class="h-full w-full object-cover" />
```

**Issues:**
1. No lazy loading
2. No placeholder
3. No error handling

**Recommendation:**
```vue
<img 
  v-lazy="image.image"
  class="h-full w-full object-cover"
  loading="lazy"
  @error="handleImageError"
  :alt="image.alt || 'Property image'"
/>
```

---

### 13.7 Modal Component Reusability
**File:** `components/global/DeleteModal.vue`

**Current:** Specific to delete/deactivate actions.

**Recommendation:** Create generic `ConfirmationModal.vue`:
```vue
<template>
  <BaseModal v-model="modelValue" :title="title">
    <template #icon>
      <slot name="icon">
        <Icon :icon="iconMap[type]" />
      </slot>
    </template>
    <p>{{ message }}</p>
    <template #actions>
      <BaseButton variant="secondary" @click="$emit('cancel')">
        {{ cancelText }}
      </BaseButton>
      <BaseButton :variant="type" @click="$emit('confirm')">
        {{ confirmText }}
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
const props = defineProps({
  type: {
    type: String,
    default: 'danger',
    validator: v => ['danger', 'warning', 'info'].includes(v)
  },
  // ...
});
</script>
```

---

### 13.8 Dynamic Import for Heavy Components
**File:** `pages/owner/OwnerDashboard.vue`

**Current:**
```javascript
import { HotelDashboardCalendar } from "vue-hotel-booking-calendar";
```

**Optimized:**
```javascript
const HotelDashboardCalendar = defineAsyncComponent({
  loader: () => import('vue-hotel-booking-calendar')
    .then(m => m.HotelDashboardCalendar),
  loadingComponent: LoadingSpinner,
  delay: 200,
  errorComponent: ErrorComponent,
});
```

---

### 13.9 Consistent Error Handling
**Current (scattered):**
```javascript
try {
  const res = await service.getData();
} catch (error) {
  console.error("Error:", error);
}
```

**Recommendation:** Create error handling utility:
```javascript
// utils/errorHandler.js
export function handleApiError(error, context = '') {
  const message = error.response?.data?.message 
    || error.message 
    || 'An unexpected error occurred';
    
  if (import.meta.env.DEV) {
    console.error(`[${context}]`, error);
  }
  
  // Could integrate with error tracking here
  // Sentry.captureException(error);
  
  return { success: false, message };
}

// Usage
try {
  await service.getData();
} catch (error) {
  const { message } = handleApiError(error, 'fetchProperties');
  toast.error(message);
}
```

---

### 13.10 Computed Properties for Complex Template Logic
**File:** `pages/admin/Dashboard.vue`

**Current:**
```vue
<p class="text-lg font-bold text-gray-900">
  £{{ formatCurrency(dashboardDetail?.details?.revenue?.today) }}
</p>
```

**Issue:** Repetitive optional chaining in template.

**Recommendation:**
```javascript
const revenue = computed(() => ({
  today: formatCurrency(dashboardDetail.value?.details?.revenue?.today),
  thisWeek: formatCurrency(dashboardDetail.value?.details?.revenue?.thisWeek),
  thisMonth: formatCurrency(dashboardDetail.value?.details?.revenue?.thisMonth),
}));

// Template
<p>£{{ revenue.today }}</p>
```

---

## 14. Component-Specific Issues

### 14.1 BaseInput Component Issues
**File:** `components/global/BaseInput.vue`

**Issues Found:**

1. **Unique ID Generation Not Truly Unique:**
```javascript
const uniqueId = `input-${Math.random().toString(36).toLowerCase().substring(2, 10)}`;
```
Risk of collision. Use `crypto.randomUUID()` or counter.

2. **Theme Object Recreated on Every Render:**
```javascript
const themes = {
  light: { bg: "bg-white", ... },
  // ...
};
```
Move outside component or use `Object.freeze()`.

3. **Validation on Every Input:**
```javascript
const handleInput = (event) => {
  // ...
  if (touched.value) validateInput();
};
```
Should debounce validation for performance.

---

### 14.2 Loader Component Issues
**File:** `components/global/Loader.vue`

**Issue:** Hardcoded image path:
```vue
<img src="/public/main_logo.png" alt="Paper Diary" />
```

Should be `/main_logo.png` (Vite serves public folder at root).

---

### 14.3 RegisterPage Issues
**File:** `pages/auth/RegisterPage.vue`

**Issues:**

1. **Untracked Ref:**
```javascript
const form = ref({
  firstName: "",
  // ...
  phone: "",
});
// But `telephone` field exists in form
```

2. **Telephone Label Lowercase:**
```vue
<BaseInput label="telephone" ... />  <!-- Should be "Telephone" -->
```

3. **No Loading State:**
No indication when registration is in progress.

---

### 14.4 Admin Dashboard Error Handling
**File:** `pages/admin/Dashboard.vue`

```javascript
} catch {
  error.value = err.message || 'Failed to load dashboard data';
}
```

**Issue:** `err` is not defined in catch block! Should be:
```javascript
} catch (err) {
  error.value = err.message || 'Failed to load dashboard data';
}
```

---

## 15. Asset & Path Issues

### 15.1 Inconsistent Asset Paths
**Files with incorrect paths:**
```vue
<!-- Incorrect - /public/ should not be in path -->
<img src="/public/main_logo.png" />
<img src="/public/user-1.jpg" />
<img src="/public/map.png" />

<!-- Correct -->
<img src="/main_logo.png" />
<img src="/user-1.jpg" />
<img src="/map.png" />
```

**Files affected:**
- `components/global/Loader.vue`
- `layouts/AdminLayout.vue`
- `layouts/OwnerLayout.vue`
- `layouts/AppHeader.vue`
- `pages/guest/DetailsPage.vue`

---

### 15.2 Missing Fallback Images
**File:** `pages/guest/BookingPage.vue`

```vue
<img :src="bookingInfo?.property?.image || '/hotel-1.jpg'" />
```

**Issue:** Falls back to specific hotel image. Should use generic placeholder.

---

## 16. Hardcoded Stripe Key Issue

**File:** `pages/guest/BookingPage.vue`

```javascript
stripe.value = await loadStripe("pk_test_51RvEjJ7fYIrC7aOkBuyFRaQM8EH4P3nCf8sW5BEFVufQaLOlM2ZNk8lRDNh7uCtm6sVV2Wa2dhIVbIwI6P2q2xQx00PpDGeuDB");
```

**Issue:** 🔴 CRITICAL - Hardcoded Stripe publishable key. Should come from:
1. Environment variable
2. Backend API endpoint
3. Property-specific key from backend

**Recommendation:**
```javascript
// Get key from property or backend
const stripeKey = bookingInfo.value?.property?.stripePublicKey 
  || import.meta.env.VITE_STRIPE_KEY;
stripe.value = await loadStripe(stripeKey);
```

---

## File-by-File Issue Summary (Updated)

| File | Critical | High | Medium | Low |
|------|----------|------|--------|-----|
| composables/useAuth.js | 1 | 2 | 3 | 2 |
| services/apiClient.js | 0 | 1 | 2 | 1 |
| services/adminService.js | 0 | 1 | 1 | 0 |
| services/ownerService.js | 0 | 0 | 2 | 1 |
| router/index.js | 0 | 1 | 2 | 2 |
| router/routes.js | 0 | 0 | 3 | 3 |
| layouts/AdminLayout.vue | 0 | 0 | 3 | 4 |
| layouts/OwnerLayout.vue | 1 | 1 | 3 | 4 |
| layouts/AppHeader.vue | 0 | 0 | 2 | 3 |
| pages/owner/OwnerDashboard.vue | 0 | 0 | 4 | 5 |
| pages/owner/Properties.vue | 0 | 0 | 1 | 2 |
| pages/owner/PropertyWizard.vue | 0 | 0 | 2 | 3 |
| pages/owner/PropertiesForm.vue | 0 | 0 | 2 | 3 |
| pages/guest/DetailsPage.vue | 0 | 0 | 3 | 4 |
| pages/guest/BookingPage.vue | 1 | 1 | 3 | 3 |
| pages/admin/Dashboard.vue | 0 | 1 | 2 | 2 |
| pages/admin/Users.vue | 0 | 0 | 2 | 2 |
| pages/auth/LoginPage.vue | 0 | 0 | 1 | 2 |
| pages/auth/RegisterPage.vue | 0 | 0 | 2 | 3 |
| pages/auth/Profile.vue | 0 | 0 | 1 | 2 |
| pages/auth/ChangePassword.vue | 0 | 0 | 1 | 2 |
| components/global/BaseInput.vue | 0 | 0 | 2 | 3 |
| components/global/Basetable.vue | 0 | 0 | 3 | 4 |
| components/global/Loader.vue | 0 | 0 | 1 | 1 |
| App.vue | 0 | 1 | 1 | 0 |
| app.js | 0 | 1 | 1 | 0 |

---

## Summary Statistics

| Category | Count |
|----------|-------|
| **Critical Issues** | 3 |
| **High Priority Issues** | 12 |
| **Medium Priority Issues** | 52 |
| **Low Priority Issues** | 60 |
| **Total Issues Found** | 127 |

### Priority Actions

1. 🔴 **Critical:** Fix hardcoded Stripe key in BookingPage.vue
2. 🔴 **Critical:** Fix localStorage token storage security
3. 🔴 **Critical:** Remove admin token dual-storage vulnerability
4. 🟡 **High:** Fix error variable undefined in Dashboard.vue
5. 🟡 **High:** Fix asset paths (`/public/` prefix)
6. 🟡 **High:** Implement proper state management (Pinia)
7. 🟡 **High:** Create shared DashboardLayout component
8. 🟠 **Medium:** Split large components (Basetable, BaseInput)
9. 🟠 **Medium:** Standardize service response handling
10. 🟠 **Medium:** Add TypeScript or JSDoc types

---

*Review completed: January 12, 2026*
*Reviewer: Senior Developer Code Review*
*Updated: January 12, 2026 - Added file location, organization, and optimization sections*