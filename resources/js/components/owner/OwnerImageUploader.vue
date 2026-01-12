<template>
  <div class="mb-4">
    <label class="block text-sm font-semibold text-gray-700 mb-2">{{
      label
    }}</label>

    <div
      :class="{
        'border-blue-500 bg-blue-50': isDragging,
        'border-gray-300 bg-gray-50 hover:bg-gray-100': !isDragging,
      }"
      class="w-full p-6 text-center border-2 border-dashed rounded-lg cursor-pointer transition duration-200"
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
      <Icon
        icon="mdi:cloud-upload"
        class="w-8 h-8 mx-auto text-gray-400 mb-2"
      />
      <p class="text-sm text-gray-600">
        <span class="font-medium text-blue-600">Click to upload</span> or drag
        and drop
      </p>
      <p v-if="accept" class="text-xs text-gray-500 mt-1">
        {{
          accept
            .split(",")
            .map((ext) => ext.replace("image/", "."))
            .join(", ")
            .toUpperCase()
        }}
        up to {{ maxFiles }} files
      </p>
    </div>
    <!-- image preview -->
    <div
      v-if="images.length"
      class="mt-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4"
    >
      <div
        v-for="(image, index) in images"
        :key="image.id || image.file.name"
        draggable="true"
        class="relative group aspect-square rounded-lg overflow-hidden shadow-md border border-gray-200 cursor-grab"
        @dragstart="onDragStart(index, $event)"
        @dragover.prevent="onDragOver(index, $event)"
        @drop.prevent="onDrop(index, $event)"
      >
        <img
          :src="image.url"
          :alt="image.file ? image.file.name : 'Uploaded Property Image'"
          class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
        />

        <button
          type="button"
          class="absolute top-1 right-1 p-1 bg-red-600 text-white rounded-full opacity-0 group-hover:opacity-100 transition duration-300 transform group-hover:scale-100 scale-75 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50"
          title="Delete Image"
          @click.stop="deleteImage(index)"
        >
          <Icon icon="mdi:close" class="w-4 h-4" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { Icon } from "@iconify/vue";
import { toast } from "vue-sonner";

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  label: {
    type: String,
    default: "Property Images",
  },
  accept: {
    type: String,
    default: "image/jpeg,image/png,image/webp",
  },
  maxFiles: {
    type: Number,
    default: 50,
  },
  maxFileSize: {
    type: Number,
    default: 2 * 1024 * 1024, // 2MB default to align with backend
  },
  maxTotalFileSize: {
    type: Number,
    default: 50 * 1024 * 1024, // 20MB combined default
  },
});

const emit = defineEmits(["update:modelValue", "reorder"]);

// Local state for the list of images (files and URLs)
const images = ref([...props.modelValue]);
const dragIndex = ref(null);

const onDragStart = (index, e) => {
  dragIndex.value = index;
  // set data to allow drop
  e.dataTransfer.effectAllowed = "move";
  try {
    // Some browsers require data to be set to allow drops between elements
    e.dataTransfer.setData("text/plain", "drag-image");
    // Use the dragged element as the drag image for better UX
    if (e.target && e.dataTransfer.setDragImage) {
      e.dataTransfer.setDragImage(e.target, 16, 16);
    }
  } catch (err) {
    // ignore
  }
};

const onDragOver = (index, e) => {
  e.preventDefault();
  e.dataTransfer.dropEffect = "move";
};

const onDrop = (index, e) => {
  e.preventDefault();
  const from = dragIndex.value;
  const to = index;
  if (from === null || from === to) {return;}
  const newImages = [...images.value];
  const [moved] = newImages.splice(from, 1);
  newImages.splice(to, 0, moved);
  images.value = newImages;
  emit("update:modelValue", images.value);
  // Also emit a reorder event with the new list
  emit("reorder", images.value);
  dragIndex.value = null;
};
const isDragging = ref(false);
const fileInputRef = ref(null);

// Sync local state with external modelValue
watch(
  () => props.modelValue,
  (newVal) => {
    images.value = [...newVal];
  },
  { deep: true }
);

// Triggers the hidden file input when the drop area is clicked
const triggerFileInput = () => {
  fileInputRef.value.click();
};

const handleFileChange = (event) => {
  const newFiles = Array.from(event.target.files);
  processFiles(newFiles);
  // Clear the input value so the same file can be selected again
  if (fileInputRef.value) {
    fileInputRef.value.value = "";
  }
};

const handleDrop = (event) => {
  isDragging.value = false;
  const newFiles = Array.from(event.dataTransfer.files);
  processFiles(newFiles);
};

const onParentDragOver = (e) => {
  // Only show drag indicator if dragging files from outside (not when reordering existing thumbnails)
  const types =
    e.dataTransfer && e.dataTransfer.types
      ? Array.from(e.dataTransfer.types)
      : [];
  if (types.includes("Files") || types.includes("application/x-moz-file")) {
    e.preventDefault();
    isDragging.value = true;
  }
};

const onParentDragLeave = (e) => {
  isDragging.value = false;
};

const onParentDrop = (e) => {
  isDragging.value = false;
  // Only process if files are present (file upload), otherwise let child drop handle reorder
  const files =
    e.dataTransfer && e.dataTransfer.files
      ? Array.from(e.dataTransfer.files)
      : [];
  if (files.length) {
    e.preventDefault();
    handleDrop(e);
  }
};

const processFiles = (newFiles) => {
  if (images.value.length + newFiles.length > props.maxFiles) {
    toast.error(`You can only upload a maximum of ${props.maxFiles} images.`);
    return;
  }

  // Filter for accepted types and process
  const acceptedFiles = newFiles.filter((file) =>
    props.accept.includes(file.type)
  );

  if (acceptedFiles.length < newFiles.length) {
    toast.warning(
      "Some files were ignored because they are not valid image types."
    );
  }

  // Check combined file sizes: current new file sizes + existing new file sizes
  const existingNewSize = images.value.reduce(
    (acc, item) => acc + (item.file ? item.file.size : 0),
    0
  );
  const incomingTotalSize = acceptedFiles.reduce((acc, f) => acc + f.size, 0);
  if (existingNewSize + incomingTotalSize > props.maxTotalFileSize) {
    toast.error(
      `The combined total size of selected images exceeds ${Math.round(
        props.maxTotalFileSize / 1024 / 1024
      )}MB. Please choose smaller images or upload in smaller batches.`
    );
    return;
  }

  // Filter out files exceeding single-file size limit
  const sizeFiltered = acceptedFiles.filter((file) => {
    if (file.size > props.maxFileSize) {
      toast.error(
        `File ${file.name} is too large (max ${Math.round(
          props.maxFileSize / 1024 / 1024
        )}MB).`
      );
      return false;
    }
    return true;
  });

  const processedImages = sizeFiltered.map((file) => ({
    file,
    url: URL.createObjectURL(file), // Create a temporary URL for preview
    // The 'id' property is left null for new uploads until they are saved to the API
    id: null,
  }));

  images.value = [...images.value, ...processedImages];
  emit("update:modelValue", images.value);
};

const deleteImage = (index) => {
  // Revoke the temporary URL to free up memory
  if (images.value[index].url) {
    URL.revokeObjectURL(images.value[index].url);
  }

  images.value.splice(index, 1);
  emit("update:modelValue", images.value);
  toast.info("Image deleted successfully (will be removed upon save).");
};
</script>

<script>
export default {
  methods: {
    // Keep for default export compatibility with Options API usage in some parts
  },
};
</script>
