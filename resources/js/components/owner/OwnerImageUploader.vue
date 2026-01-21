<template>
  <div class="mb-4">
    <label class="block text-sm font-bold text-slate-700 mb-2 tracking-tight">{{ label }}</label>

    <div
      :class="{
        'border-blue-500 bg-blue-50/50 scale-[0.99]': isDragging,
        'border-slate-200 bg-white hover:bg-slate-50 hover:border-blue-300': !isDragging,
      }"
      class="w-full p-6 sm:p-10 text-center border-2 border-dashed rounded-2xl cursor-pointer transition-all duration-200 group"
      @dragover="onParentDragOver"
      @dragleave="onParentDragLeave"
      @drop="onParentDrop"
      @click="triggerFileInput"
    >
      <input
        ref="fileInputRef"
        type="file"
        :accept="accept"
        multiple
        class="hidden"
        @change="handleFileChange"
      />
      <div class="flex flex-col items-center">
        <div
          class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300"
        >
          <Icon
            icon="mdi:cloud-upload"
            class="w-6 h-6"
          />
        </div>
        <p class="text-sm text-slate-600">
          <span class="font-bold text-blue-600">Click to upload</span> or drag and drop
        </p>
        <p
          v-if="accept"
          class="text-[10px] text-slate-400 mt-2 font-bold uppercase tracking-widest"
        >
          {{
            accept
              .split(',')
              .map((ext) => ext.replace('image/', '.'))
              .join(' / ')
          }}
          • Max {{ maxFiles }} Files
        </p>
      </div>
    </div>

    <div
      v-if="images.length"
      class="mt-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4"
    >
      <div
        v-for="(image, index) in images"
        :key="image.id || (image.file ? image.file.name + index : index)"
        draggable="true"
        :data-index="index"
        class="relative group aspect-square rounded-xl overflow-hidden shadow-sm border border-slate-200 bg-slate-100 cursor-grab active:cursor-grabbing touch-none select-none transition-all duration-300"
        :class="{ 'opacity-50 scale-95 ring-2 ring-blue-500': dragIndex === index }"
        @dragstart="onDragStart(index, $event)"
        @dragover.prevent="onDragOver(index, $event)"
        @drop.prevent="onDrop(index, $event)"
        @touchstart="onTouchStart(index, $event)"
        @touchmove="onTouchMove($event)"
        @touchend="onTouchEnd"
      >
        <img
          :src="image.url"
          :alt="image.file ? image.file.name : 'Property'"
          class="w-full h-full object-cover pointer-events-none"
        />

        <div
          class="absolute bottom-2 left-2 px-2 py-0.5 bg-black/50 backdrop-blur-md rounded-md text-[10px] font-bold text-white opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity"
        >
          #{{ index + 1 }}
        </div>

        <button
          type="button"
          class="absolute top-2 right-2 p-1.5 bg-white/90 text-red-600 rounded-lg shadow-lg opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-all hover:bg-red-600 hover:text-white"
          @click.stop="deleteImage(index)"
        >
          <Icon
            icon="mdi:close"
            class="w-4 h-4"
          />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Icon } from '@iconify/vue';
import { toast } from 'vue-sonner';

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  label: { type: String, default: 'Property Images' },
  accept: { type: String, default: 'image/jpeg,image/png,image/webp' },
  maxFiles: { type: Number, default: 50 },
  maxFileSize: { type: Number, default: 2 * 1024 * 1024 },
  maxTotalFileSize: { type: Number, default: 50 * 1024 * 1024 },
});

const emit = defineEmits(['update:modelValue', 'reorder']);

const images = ref([...props.modelValue]);
const dragIndex = ref(null);
const isDragging = ref(false);
const fileInputRef = ref(null);

watch(
  () => props.modelValue,
  (newVal) => {
    images.value = [...newVal];
  },
  { deep: true }
);

const triggerFileInput = () => fileInputRef.value.click();

const handleFileChange = (event) => {
  const newFiles = Array.from(event.target.files);
  processFiles(newFiles);
  if (fileInputRef.value) {
    fileInputRef.value.value = '';
  }
};

// Desktop Drag and Drop Logic
const onDragStart = (index, e) => {
  dragIndex.value = index;
  if (e.dataTransfer) {
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', index);
  }
};

const onDragOver = (index, e) => {
  if (dragIndex.value === null) {
    return;
  }
  e.preventDefault();
};

const onDrop = (index) => {
  reorderImages(dragIndex.value, index);
  dragIndex.value = null;
};

// Mobile Touch Reordering Logic
let startTouchX = 0;
let startTouchY = 0;

const onTouchStart = (index, e) => {
  dragIndex.value = index;
  startTouchX = e.touches[0].clientX;
  startTouchY = e.touches[0].clientY;
};

const onTouchMove = (e) => {
  if (dragIndex.value === null) {
    return;
  }

  // Prevent page scrolling while dragging
  if (
    Math.abs(e.touches[0].clientY - startTouchY) > 10 ||
    Math.abs(e.touches[0].clientX - startTouchX) > 10
  ) {
    if (e.cancelable) {
      e.preventDefault();
    }
  }

  const touch = e.touches[0];
  const target = document.elementFromPoint(touch.clientX, touch.clientY);
  const container = target?.closest('[data-index]');

  if (container) {
    const targetIndex = parseInt(container.getAttribute('data-index'), 10);
    if (targetIndex !== dragIndex.value) {
      reorderImages(dragIndex.value, targetIndex);
      dragIndex.value = targetIndex;
    }
  }
};

const onTouchEnd = () => {
  dragIndex.value = null;
};

const reorderImages = (from, to) => {
  if (from === null || from === to) {
    return;
  }
  const newImages = [...images.value];
  const [moved] = newImages.splice(from, 1);
  newImages.splice(to, 0, moved);
  images.value = newImages;
  emit('update:modelValue', images.value);
  emit('reorder', images.value);
};

const onParentDragOver = (e) => {
  const types = e.dataTransfer?.types ? Array.from(e.dataTransfer.types) : [];
  if (types.includes('Files')) {
    e.preventDefault();
    isDragging.value = true;
  }
};

const onParentDragLeave = () => (isDragging.value = false);

const onParentDrop = (e) => {
  isDragging.value = false;
  const files = e.dataTransfer?.files ? Array.from(e.dataTransfer.files) : [];
  if (files.length) {
    e.preventDefault();
    processFiles(files);
  }
};

const processFiles = (newFiles) => {
  if (images.value.length + newFiles.length > props.maxFiles) {
    toast.error(`Max ${props.maxFiles} images allowed.`);
    return;
  }

  const acceptedFiles = newFiles.filter((file) => props.accept.includes(file.type));

  const existingNewSize = images.value.reduce(
    (acc, item) => acc + (item.file ? item.file.size : 0),
    0
  );
  const incomingTotalSize = acceptedFiles.reduce((acc, f) => acc + f.size, 0);

  if (existingNewSize + incomingTotalSize > props.maxTotalFileSize) {
    toast.error('Total size exceeds limit.');
    return;
  }

  const processedImages = acceptedFiles
    .filter((file) => {
      if (file.size > props.maxFileSize) {
        toast.error(`${file.name} is too large.`);
        return false;
      }
      return true;
    })
    .map((file) => ({
      file,
      url: URL.createObjectURL(file),
      id: null,
    }));

  images.value = [...images.value, ...processedImages];
  emit('update:modelValue', images.value);
};

const deleteImage = (index) => {
  if (images.value[index].url && images.value[index].file) {
    URL.revokeObjectURL(images.value[index].url);
  }
  images.value.splice(index, 1);
  emit('update:modelValue', images.value);
  toast.success('Image removed.');
};
</script>
