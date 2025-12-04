<script setup>
import { ref, computed, watch } from "vue";
import { Icon } from "@iconify/vue";
import clsx from "clsx";

const props = defineProps({
  label: { type: String, default: "" },
  modelValue: [String, Number],
  placeholder: { type: String, default: "" },
  helperText: { type: String, default: "" },
  icon: String,
  prefix: String,
  suffix: String,

  // States
  required: Boolean,
  disabled: { type: Boolean, default: false },

  // Styling
  variant: { type: String, default: "light" }, // light, gray, dark, dark-disabled
  width: { type: String, default: "md" },

  // Inputs
  type: { type: String, default: "text" },

  // Validation & Constraints
  pattern: { type: RegExp, default: null },
  customError: { type: String, default: "" },
  minLength: { type: Number },
  maxLength: { type: Number, default: null },

  // UX Options
  showCount: { type: Boolean, default: false },
  multiline: { type: Boolean, default: false },
  rows: { type: Number, default: 3 },
});

const emit = defineEmits(["update:modelValue"]);

// --- THEME CONFIGURATION (Restored Dark Themes) ---
const themes = {
  light: {
    bg: "bg-white",
    txt: "text-slate-400",
    inTxt: "text-gray-900",
    border: "border-gray-300",
    helper: "text-slate-500",
  },
  gray: {
    bg: "bg-gray-200",
    txt: "text-slate-400",
    inTxt: "text-gray-900",
    border: "border-gray-300",
    helper: "text-slate-500",
  },
  dark: {
    bg: "bg-black",
    txt: "text-slate-400",
    inTxt: "text-white font-bold",
    border: "border-gray-600",
    helper: "text-slate-500 font-bold",
  },
  "dark-disabled": {
    bg: "bg-black",
    txt: "text-slate-500",
    inTxt: "text-gray-500 font-bold",
    border: "border-gray-700",
    helper: "text-slate-600 font-bold",
  },
};

// Fallback to light if theme not found
const theme = computed(() => themes[props.variant] || themes.light);

// --- WIDTH CLASSES ---
const widthClass = computed(() => {
  const widths = {
    full: "w-full",
    "3/4": "max-w-3xl w-full",
    half: "max-w-md w-full",
    sm: "max-w-xs w-full",
    md: "max-w-sm w-full",
    lg: "max-w-lg w-full",
  };
  return widths[props.width] || "max-w-sm";
});

// --- STATE MANAGEMENT ---
const innerValue = ref(props.modelValue ?? "");
const touched = ref(false);
const showPassword = ref(false);
const isPasswordType = computed(() => props.type === "password");

// Sync local value with prop
watch(
  () => props.modelValue,
  (newVal) => {
    innerValue.value = newVal;
  }
);

// Emit updates
watch(innerValue, (val) => {
  emit("update:modelValue", val);
  if (touched.value) validateInput(); // Re-validate on typing if already touched
});

// --- VALIDATION LOGIC ---

// Default Regex Patterns
const patterns = {
  email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
  password: /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/,
  phone: /^[0-9+\-\s()]{7,15}$/,
  username: /^[a-zA-Z0-9_]{3,16}$/,
};

const errorMessage = ref("");
const isValid = ref(true);

const validateInput = () => {
  touched.value = true;
  const val = String(innerValue.value || "").trim();

  // 1. Required Check
  if (props.required && !val) {
    isValid.value = false;
    errorMessage.value = "This field is required";
    return false;
  }

  // 2. Empty non-required fields are valid
  if (!props.required && !val) {
    isValid.value = true;
    errorMessage.value = "";
    return true;
  }

  // 3. Custom Pattern (Prop) - Strict check
  if (props.pattern && !props.pattern.test(val)) {
    isValid.value = false;
    errorMessage.value = props.customError || "Invalid format";
    return false;
  }

  // 4. Type-based patterns
  if (props.type === "email" && !patterns.email.test(val)) {
    isValid.value = false;
    errorMessage.value = "Invalid email address";
    return false;
  }

  // 5. Min Length
  if (props.minLength && val.length < props.minLength) {
    isValid.value = false;
    errorMessage.value = `Minimum ${props.minLength} characters required`;
    return false;
  }

  isValid.value = true;
  errorMessage.value = "";
  return true;
};

const onBlur = () => validateInput();

// --- EXPOSE TO PARENT ---
// This allows the parent component to call inputRef.value.validate()
defineExpose({
  validate: validateInput,
});

// --- CLASSES ---
const containerClasses = computed(() =>
  clsx(
    "flex gap-2 border px-3 py-2 transition-all duration-200 rounded-lg overflow-hidden",
    props.multiline ? "items-start" : "items-center",
    theme.value.bg,
    // Conditional Border Color
    !isValid.value && touched.value
      ? "border-red-500 ring-1 ring-red-100"
      : `${theme.value.border} focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-100`
  )
);

const inputClasses = computed(() =>
  clsx(
    "flex-1 min-w-0 outline-none border-none bg-transparent placeholder:text-slate-400 text-sm",
    theme.value.inTxt
  )
);

const computedMaxLength = computed(
  () => props.maxLength || (props.multiline ? 250 : 50)
);
</script>

<template>
  <div :class="[widthClass]">
    <label
      v-if="label"
      :for="label"
      class="block text-sm font-semibold text-gray-700 mb-1"
    >
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>

    <div :class="containerClasses">
      <Icon v-if="icon" :icon="icon" class="text-lg text-slate-400 shrink-0" />

      <span
        v-if="prefix"
        class="text-sm text-slate-500 shrink-0 bg-gray-50 px-1 rounded"
        >{{ prefix }}</span
      >

      <textarea
        v-if="multiline"
        :id="label"
        v-model="innerValue"
        :placeholder="placeholder"
        :rows="rows"
        :disabled="disabled"
        :maxlength="computedMaxLength"
        :class="[inputClasses, disabled ? 'cursor-not-allowed opacity-60' : '']"
        @blur="onBlur"
        @input="touched ? validateInput() : null"
      ></textarea>

      <input
        v-else
        :id="label"
        v-model="innerValue"
        :type="isPasswordType && !showPassword ? 'password' : 'text'"
        :placeholder="placeholder"
        :disabled="disabled"
        :maxlength="computedMaxLength"
        :class="[inputClasses, disabled ? 'cursor-not-allowed opacity-60' : '']"
        @blur="onBlur"
        @input="touched ? validateInput() : null"
      />

      <span v-if="suffix" class="text-sm text-slate-500 shrink-0">{{
        suffix
      }}</span>

      <button
        v-if="isPasswordType && !disabled"
        type="button"
        @click="showPassword = !showPassword"
        class="focus:outline-none text-slate-400 hover:text-slate-600"
      >
        <Icon
          :icon="showPassword ? 'mdi:eye-off-outline' : 'mdi:eye-outline'"
          class="text-lg"
        />
      </button>
    </div>

    <div class="flex justify-between items-start mt-1">
      <span
        class="text-xs transition-colors duration-200 font-medium"
        :class="!isValid && touched ? 'text-red-500' : theme.helper"
      >
        {{ !isValid && touched ? errorMessage : helperText }}
      </span>

      <span v-if="showCount && !disabled" class="text-xs text-gray-400 ml-2">
        {{ innerValue?.toString().length || 0 }}/{{ computedMaxLength }}
      </span>
    </div>
  </div>
</template>
