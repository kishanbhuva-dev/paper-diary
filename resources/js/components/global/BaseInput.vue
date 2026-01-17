<template>
  <div :class="[widthClass]">
    <label
      v-if="label"
      :for="uniqueId"
      class="block text-sm font-semibold mb-1 text-slate-700"
    >
      {{ label }}
      <span
        v-if="required"
        class="text-red-500"
        >*</span
      >
    </label>

    <div :class="containerClasses">
      <Icon
        v-if="icon"
        :icon="icon"
        class="text-md shrink-0"
        :class="theme.txt"
      />

      <span
        v-if="prefix"
        class="text-sm text-slate-500 shrink-0 bg-gray-50 px-1 rounded"
        >{{ prefix }}</span
      >

      <textarea
        v-if="multiline"
        :id="uniqueId"
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
        :id="uniqueId"
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

      <span
        v-if="suffix"
        class="text-sm shrink-0"
        :class="theme.txt"
        >{{ suffix }}</span
      >

      <button
        v-if="isPasswordType && !disabled"
        type="button"
        class="focus:outline-none text-slate-400 hover:text-slate-600"
        @click="showPassword = !showPassword"
      >
        <Icon
          :icon="showPassword ? 'mdi:eye-off-outline' : 'mdi:eye-outline'"
          class="text-lg"
        />
      </button>
    </div>

    <div
      class="flex justify-between items-start min-h-[20px]"
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

      <span
        v-if="showCount && !disabled"
        class="text-xs text-gray-400 ml-2"
      >
        {{ innerValue?.toString().length || 0 }}/{{ computedMaxLength }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Icon } from '@iconify/vue';
import clsx from 'clsx';

const props = defineProps({
  label: { type: String, default: '' },
  modelValue: {
    type: [String, Number],
    default: null,
  },
  placeholder: { type: String, default: '' },
  helperText: { type: String, default: '' },
  icon: { type: String, default: '' },
  prefix: { type: String, default: '' },
  suffix: { type: String, default: '' },
  required: Boolean,
  disabled: { type: Boolean, default: false },
  variant: { type: String, default: 'light' },
  width: { type: String, default: 'md' },
  type: { type: String, default: 'text' },
  pattern: { type: RegExp, default: null },
  customError: { type: String, default: '' },
  minLength: { type: Number, default: 0 },
  maxLength: { type: Number, default: 5000 },
  min: { type: Number, default: 0 },
  max: { type: Number, default: Infinity },
  showCount: { type: Boolean, default: false },
  multiline: { type: Boolean, default: false },
  rows: { type: Number, default: 3 },
});

const emit = defineEmits(['update:modelValue']);

// Generate a unique ID to prevent label focus conflicts
const uniqueId = `input-${Math.random().toString(36).toLowerCase().substring(2, 10)}`;

const themes = {
  light: {
    bg: 'bg-white',
    txt: 'text-slate-400',
    inTxt: 'text-gray-900',
    border: 'border-gray-300',
    helper: 'text-slate-500',
  },
  gray: {
    bg: 'bg-gray-200',
    txt: 'text-slate-400',
    inTxt: 'text-gray-900',
    border: 'border-gray-300',
    helper: 'text-slate-500',
  },
  dark: {
    bg: 'bg-black',
    txt: 'text-slate-400',
    inTxt: 'text-white font-bold',
    border: 'border-gray-600',
    helper: 'text-slate-500 font-bold',
  },
  'dark-disabled': {
    bg: 'bg-black',
    txt: 'text-slate-500',
    inTxt: 'text-gray-500 font-bold',
    border: 'border-gray-700',
    helper: 'text-slate-600 font-bold',
  },
};
const theme = computed(() => themes[props.variant] || themes.light);

const widthClass = computed(() => {
  const widths = {
    full: 'w-full',
    '3/4': 'max-w-3xl w-full',
    half: 'max-w-md w-full',
    sm: 'max-w-xs w-full',
    md: 'max-w-sm w-full',
    lg: 'max-w-lg w-full',
  };
  return widths[props.width] || 'max-w-sm';
});

const innerValue = ref(props.modelValue ?? '');
const touched = ref(false);
const showPassword = ref(false);
const isPasswordType = computed(() => props.type === 'password');

const computedType = computed(() => {
  if (isPasswordType.value && !showPassword.value) {
    return 'password';
  }
  if (props.type === 'number') {
    return 'number';
  }
  return 'text';
});

watch(
  () => props.modelValue,
  (newVal) => {
    innerValue.value = newVal;
  }
);

const handleInput = (event) => {
  const val = event.target.value;
  if (props.type === 'number') {
    if (val === '') {
      innerValue.value = null;
      emit('update:modelValue', null);
    } else {
      const numVal = Number(val);
      if (!isNaN(numVal)) {
        innerValue.value = numVal;
        emit('update:modelValue', numVal);
      } else {
        event.target.value = props.modelValue;
        innerValue.value = props.modelValue;
      }
    }
  } else {
    innerValue.value = val;
    emit('update:modelValue', val);
  }
  if (touched.value) {
    validateInput();
  }
};

const patterns = { email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/ };
const errorMessage = ref('');
const isValid = ref(true);

const validateInput = () => {
  touched.value = true;
  let numericVal = NaN;
  const val = String(innerValue.value || '').trim();
  if (props.type === 'number') {
    numericVal = innerValue.value === null ? null : Number(innerValue.value);
  }

  if (props.required && (!val || (props.type === 'number' && numericVal === null))) {
    isValid.value = false;
    errorMessage.value = 'This field is required';
    return false;
  }
  if (!props.required && !val && (props.type !== 'number' || numericVal === null)) {
    isValid.value = true;
    errorMessage.value = '';
    return true;
  }
  if (props.type === 'number' && !isNaN(numericVal)) {
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
  if (props.pattern && !props.pattern.test(val)) {
    isValid.value = false;
    errorMessage.value = props.customError || 'Invalid format';
    return false;
  }
  if (props.type === 'email' && !patterns.email.test(val)) {
    isValid.value = false;
    errorMessage.value = 'Invalid email address';
    return false;
  }
  if (props.minLength && val.length < props.minLength) {
    isValid.value = false;
    errorMessage.value = `Minimum ${props.minLength} characters required`;
    return false;
  }
  isValid.value = true;
  errorMessage.value = '';
  return true;
};

const onBlur = () => validateInput();
const inputRef = ref(null);
const focus = () => {
  if (inputRef.value) {
    inputRef.value.focus();
    if (['text', 'email', 'number', 'password'].includes(props.type) && inputRef.value.select) {
      inputRef.value.select();
    }
  }
};

defineExpose({ validate: validateInput, focus });

const containerClasses = computed(() =>
  clsx(
    'flex gap-2 border px-3 py-2 transition-all duration-200 rounded-xl',
    props.multiline ? 'items-start' : 'items-center',
    theme.value.bg,
    !isValid.value && touched.value
      ? 'border-red-500 ring-1 ring-red-100'
      : `${theme.value.border} focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100`
  )
);

const inputClasses = computed(() =>
  clsx(
    'flex-1 min-w-0 outline-none border-none bg-transparent placeholder:text-slate-400 text-sm',
    theme.value.inTxt
  )
);

const computedMaxLength = computed(() => props.maxLength || (props.multiline ? 250 : 50));
</script>
