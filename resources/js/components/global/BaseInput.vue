<script setup>
import { ref, computed, watch, defineEmits } from 'vue'
import { Icon } from '@iconify/vue'
import clsx from 'clsx'

const props = defineProps({
    lnm: { type: String, default: 'lable' },
    modelValue: String,
    placeholder: { type: String, default: 'placeholder' },
    helpertext: { type: String, default: '' },
    icon: String,
    prefix: String,
    suffix: String,
    require: Boolean,
    variant: { type: String, default: 'light' },
    type: { type: String, default: 'text' },
    // custom regex
    custRule: { type: RegExp, default: null },
    custMsg: { type: String, default: '' },


    // SIZE
    width: { type: String, default: 'md' }, // 👈 width variant

    // input disable
    indisable: { type: Boolean, default: false },

    // border rouded
    rounded: Boolean,
    roundedf: Boolean,
    roundedmd: Boolean,
    roundedlg: Boolean,
    roundedxl: Boolean,

    // for input
    minlen: { type: Number },
    mxlen: { type: Number, default: null },
    invalue: String,


    // textarea
    multiline: { type: Boolean, default: false },
    rows: { type: Number, default: 3 },
})

const themes = {
    light: {
        bg: 'bg-white',
        txt: 'text-slate-400',
        inTxt: 'text-black',
        helper: 'text-slate-500 font-bold',
        border: 'border-gray-300',
    },
    gray: {
        bg: 'bg-slate-200',
        txt: 'text-slate-400',
        inTxt: 'text-black',
        helper: 'text-slate-500 font-bold',
        border: 'border-gray-300',
    },
    dark: {
        bg: 'bg-black',
        txt: 'text-slate-400',
        inTxt: 'text-white font-bold',
        helper: 'text-slate-500 font-bold',
        border: 'border-gray-600',
    },
    'dark-disabled': {
        bg: 'bg-black',
        txt: 'text-slate-500',
        inTxt: 'text-white font-bold',
        helper: 'text-slate-500 font-bold',
        border: 'border-gray-600',
    },
}

const theme = computed(() => themes[props.variant] || themes.light)


// Dynamic width classes
const widthClass = computed(() => {
    switch (props.width) {
        case 'full': return 'max-w-full'
        case '3/4': return 'max-w-3xl' // roughly 75% width of large container
        case 'half': return 'max-w-md'
        case 'sm': return 'max-w-xs'
        case 'md': return 'max-w-sm'
        case 'lg': return 'max-w-lg'
        default: return 'max-w-sm'
    }
})
// computed maxlength — depends on multiline
const maxlength = computed(() => {
    // If user passed a value, use that
    if (props.mxlen !== null && props.mxlen !== undefined) {
        return Number(props.mxlen)
    }
    // else use defaults
    return props.multiline ? 100 : 20
})

const showPassword = ref(false)
const isPasswordType = computed(() => props.type === 'password')


const emit = defineEmits(['update:modelValue'])



const invalue = ref(props.modelValue ?? '')

// watch CODE FOR V-MODEL
watch(() => props.modelValue, (newval) => {
    invalue.value = newval;

})

watch(invalue, (val) => {
    emit('update:modelValue', val)
})

// VALDATION AREA CODE
const touched = ref(false)
// console.log('touched ', touched.value)
const onBlur = () => { touched.value = true }

const onInput = () => {
    if (!touched.value) touched.value = true
}

// VALIDATION REGEX 
const validationPatterns = computed(() => {
    const max = maxlength.value;
    return {
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        password: new RegExp(`^(?=.*[A-Za-z])(?=.*\\d)[A-Za-z\\d]{6,${max}}$`),
        phone: new RegExp(`^[0-9]{${max}}$`),
        username: new RegExp(`^(?=.*[A-Za-z])(?=.*\\d)[A-Za-z\\d_]{3,${max}}$`)
    }
});

// VALIDATION MASSAGES
const validationMessage = computed(() => {
    if (props.require && touched.value && !invalue.value) return 'This Field is Required';

    // custom message takes priority
    if (props.custRule && touched.value && !props.custRule.test(invalue.value)) {
        return props.custMsg || 'Invalid input';
    }

    switch (props.type) {
        case 'email': return 'Invalid email format';
        case 'password': return `Password must be 6 to ${maxlength.value} chars, include letters & numbers`;
        case 'phone': return 'Phone number must be 10 digits';
        case 'username': return 'Username must be 3-16 chars, letters/numbers/_';
        default: return '';
    }
});


const isvalid = computed(() => {
    if (props.require && !invalue.value) return false; // required check

    if (!invalue.value) return true; // if not required, empty is valid
    // custom rule takes priority
    if (props.custRule) {
        return !invalue.value ? !props.require : props.custRule.test(invalue.value)
    }

    switch (props.type) {
        case 'email':
            return validationPatterns.value.email.test(invalue.value);
        case 'password':
            return validationPatterns.value.password.test(invalue.value);
        case 'phone':
            return validationPatterns.value.phone.test(invalue.value);
        case 'username':
            return validationPatterns.value.username.test(invalue.value);
        default:
            return true;
    }
})

const ismxlenpass = computed(() => (invalue.value?.length ?? 0) > maxlength.value);

const classes = computed(() =>
    clsx(
        'flex gap-2 border px-2.5 py-2 transition-all duration-200 overflow-hidden',
        props.multiline ? 'items-start' : 'items-center',
        theme.value.bg,
        theme.value.border,
        {
            'rounded': props.rounded,
            'rounded-full': props.roundedf,
            'rounded-md': props.roundedmd,
            'rounded-lg': props.roundedlg,
            'rounded-xl': props.roundedxl,
        },
        {
            'focus-within:border-red-500 focus-within:ring-4 focus-within:ring-red-200': !isvalid.value && touched.value,
            'focus-within:border-indigo-500 focus-within:ring-4 focus-within:ring-indigo-200': isvalid.value || !touched.value,
        }
    )
)

const tclasses = computed(() => clsx('shrink-0', theme.value.txt))

const inclasses = computed(() =>
    clsx(
        'flex-1 min-w-0 outline-none border-none bg-transparent placeholder:text-slate-400 text-sm',
        theme.value.inTxt
    )
)

</script>


<template>
    <div class="input-wrapper">
        <div :class="['w-full', widthClass]">
            <label :for="props.lnm" :class="['text-sm', theme.helper]">{{ props.lnm }}</label>
            <div :class="classes">
                <!-- Left Icon -->
                <Icon v-if="icon" :icon="icon" :class="[tclasses, 'text-lg']" />

                <!-- Prefix -->
                <span v-if="prefix" :class="[tclasses, 'text-sm']">
                    {{ prefix }}
                </span>

                <template v-if="multiline">
                    <!-- Text area -->
                    <textarea :id="props.lnm" v-model="invalue" :placeholder="props.placeholder"
                        :class="[inclasses, props.indisable ? 'cursor-not-allowed select-none' : '']"
                        :disabled="props.indisable" :rows="props.rows" @input="onInput" @blur="onBlur"
                        :maxlength="maxlength" :minlength="props.minlen"></textarea>
                </template>
                <template v-else>
                    <!-- Input -->
                    <input v-model="invalue" :id="props.lnm"
                        :type="isPasswordType && !showPassword ? 'password' : 'text'" :placeholder="props.placeholder"
                        :class="[inclasses, props.indisable ? 'cursor-not-allowed select-none' : '']"
                        :disabled="props.indisable" @input="onInput" @blur="onBlur" :maxlength="maxlength" />

                    <!-- Suffix -->
                    <span v-if="suffix" :class="[tclasses, 'text-sm']">
                        {{ suffix }}
                    </span>

                    <!-- Eye toggle -->
                    <button v-if="isPasswordType && !props.indisable" type="button"
                        @click="showPassword = !showPassword" class="focus:outline-none" :class="tclasses"
                        :disabled="props.indisable" tabindex="0">
                        <Icon :icon="showPassword ? 'mdi:eye-off-outline' : 'mdi:eye-outline'"
                            :class="[tclasses, 'text-lg hover:text-slate-500']" />
                    </button>
                </template>
            </div>
            <!-- footer of component -->
            <div class="input-footer text-xs flex justify-between px-3 mt-1 transition-colors duration-200"
                :class="(!isvalid && touched) || ismxlenpass ? 'text-red-500' : theme.helper">


                <!-- Show validation message if validation fails -->
                <label v-if="!isvalid && touched">{{ validationMessage }}</label>

                <!-- Show helpertext if input is valid or if no validation error -->
                <label v-else-if="helpertext">{{ helpertext }}</label>

                <!-- Default empty string if no helpertext or validation message -->
                <label v-else></label>

                <!-- Input length counter -->
                <div>
                    <span>{{ invalue?.length ?? 0 }}</span>
                    <span>/{{ maxlength }}</span>
                </div>
            </div>

        </div>
    </div>
</template>
