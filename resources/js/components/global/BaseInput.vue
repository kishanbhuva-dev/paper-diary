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
      <Icon v-if="icon" :icon="icon" class="text-md text-slate-400 shrink-0" />

      <span
        v-if="prefix"
        class="text-sm text-slate-500 shrink-0 bg-gray-50 px-1 rounded"
        >{{ prefix }}</span
      >

      <textarea
        v-if="multiline"
        :id="label"
        ref="inputRef"
        v-model="innerValue"
        :placeholder="placeholder"
        :rows="rows"
        :disabled="disabled"
        :maxlength="computedMaxLength"
        :class="[inputClasses, disabled ? 'cursor-not-allowed opacity-60' : '']"
        @blur="onBlur"
        @input="handleInput"
      ></textarea>

      <input
        v-else
        :id="label"
        ref="inputRef"
        v-model="innerValue"
        :type="computedType"
        :placeholder="placeholder"
        :disabled="disabled"
        :maxlength="computedMaxLength"
        :min="type === 'number' ? 0 : undefined"
        :step="type === 'number' ? 'any' : undefined"
        :class="[inputClasses, disabled ? 'cursor-not-allowed opacity-60' : '']"
        @blur="onBlur"
        @input="handleInput"
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

    <div
      class="flex justify-between items-start"
      :class="{
        'mt-1': !isValid && touched,
      }"
    >
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
  variant: { type: String, default: "light" },
  width: { type: String, default: "md" },

  // Inputs
  type: { type: String, default: "text" }, // Added handling for 'number'

  // Validation & Constraints
  pattern: { type: RegExp, default: null },
  customError: { type: String, default: "" },
  minLength: { type: Number },
  maxLength: { type: Number, default: null },
  min: { type: Number }, // New: used for number type
  max: { type: Number }, // New: used for number type

  // UX Options
  showCount: { type: Boolean, default: false },
  multiline: { type: Boolean, default: false },
  rows: { type: Number, default: 3 },
});

const emit = defineEmits(["update:modelValue"]);

// --- THEME CONFIGURATION (Unchanged) ---
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
const theme = computed(() => themes[props.variant] || themes.light);

// --- WIDTH CLASSES (Unchanged) ---
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

// Determines the native input type (hides password if needed)
const computedType = computed(() => {
  if (isPasswordType.value && !showPassword.value) return "password";
  if (props.type === "number") return "number";
  return "text";
});

// Sync local value with prop
watch(
  () => props.modelValue,
  (newVal) => {
    innerValue.value = newVal;
  }
);

// --- INPUT HANDLER FOR NUMBER TYPE ---
const handleInput = (event) => {
  let val = event.target.value;
  // Handle number type to ensure proper numeric value is emitted
  if (props.type === "number") {
    // Convert to number, but allow empty string if not required
    if (val === "") {
      innerValue.value = null;
      emit("update:modelValue", null);
    } else {
      const numVal = Number(val);
      if (!isNaN(numVal)) {
        innerValue.value = numVal;
        emit("update:modelValue", numVal);
      } else {
        // Prevent non-numeric characters in number input field
        event.target.value = props.modelValue;
        innerValue.value = props.modelValue;
      }
    }
  } else {
    // For text/other types
    innerValue.value = val;
    emit("update:modelValue", val);
  }

  if (touched.value) validateInput(); // Re-validate on typing if already touched
};

// --- VALIDATION LOGIC (Enhanced for Min/Max number) ---
const patterns = {
  email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
  // ... (Other patterns)
};

const errorMessage = ref("");
const isValid = ref(true);

const validateInput = () => {
  touched.value = true;
  const val = String(innerValue.value || "").trim();
  const numericVal =
    props.type === "number"
      ? innerValue.value === null
        ? null
        : Number(innerValue.value)
      : NaN;

  // 1. Required Check
  if (
    props.required &&
    (!val || (props.type === "number" && numericVal === null))
  ) {
    isValid.value = false;
    errorMessage.value = "This field is required";
    return false;
  }

  // 2. Empty non-required fields are valid
  if (
    !props.required &&
    !val &&
    (props.type !== "number" || numericVal === null)
  ) {
    isValid.value = true;
    errorMessage.value = "";
    return true;
  }

  // 3. Number Type Checks
  if (props.type === "number" && !isNaN(numericVal)) {
    if (props.min !== undefined && numericVal < props.min) {
      isValid.value = false;
      errorMessage.value = `Must be ${props.min} or greater`;
      return false;
    }
    if (props.max !== undefined && numericVal > props.max) {
      isValid.value = false;
      errorMessage.value = `Must be ${props.max} or less`;
      return false;
    }
  }

  // 4. Custom Pattern (Prop) - Strict check (only for non-number types if number is controlled by min/max)
  if (props.pattern && !props.pattern.test(val)) {
    isValid.value = false;
    errorMessage.value = props.customError || "Invalid format";
    return false;
  }

  // 5. Type-based patterns (e.g., email)
  if (props.type === "email" && !patterns.email.test(val)) {
    isValid.value = false;
    errorMessage.value = "Invalid email address";
    return false;
  }

  // 6. Min Length (for strings)
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

// --- FOCUS LOGIC ---
const inputRef = ref(null);
const focus = () => {
  if (inputRef.value) {
    // Focus the native element
    inputRef.value.focus();
    // For text/number inputs, select all content for easier editing
    if (
      ["text", "email", "number", "password"].includes(props.type) &&
      inputRef.value.select
    ) {
      inputRef.value.select();
    }
  }
};

// --- EXPOSE TO PARENT ---
defineExpose({
  validate: validateInput,
  focus,
});

// --- CLASSES (Unchanged) ---
const containerClasses = computed(() =>
  clsx(
    "flex gap-2 border px-3 py-2 transition-all duration-200 rounded-xl", // Changed 'lg' to 'xl' to match resource forms
    props.multiline ? "items-start" : "items-center",
    theme.value.bg,
    // Conditional Border Color
    !isValid.value && touched.value
      ? "border-red-500 ring-1 ring-red-100"
      : `${theme.value.border} focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100`
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
