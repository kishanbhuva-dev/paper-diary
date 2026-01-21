<template>
  <main class="bg-gray-50 min-h-screen p-6">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">🗄️ Database Migration Manager</h1>
        <p class="text-gray-600">Manage your Laravel database migrations safely</p>
      </div>

      <!-- Warning Box -->
      <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <div class="w-5 h-5 bg-red-500 rounded-full flex items-center justify-center">
              <span class="text-white text-xs font-bold">!</span>
            </div>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Administrative Access Required</h3>
            <p class="text-sm text-red-700 mt-1">
              This tool requires administrator privileges. Only use this interface if you have
              proper authorization to run database migrations on this system.
            </p>
          </div>
        </div>
      </div>

      <!-- Alert Messages -->
      <div
        v-if="alert.message"
        :class="`alert alert-${alert.type} mb-6`"
      >
        <div class="flex items-center">
          <span class="alert-icon">{{ getAlertIcon(alert.type) }}</span>
          <span>{{ alert.message }}</span>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex gap-4">
          <button
            @click="runMigrations"
            :disabled="loading.runMigration"
            class="btn btn-primary"
          >
            <span
              v-if="loading.runMigration"
              class="loading-spinner"
            ></span>
            <span v-else>🚀</span>
            {{ loading.runMigration ? 'Running...' : 'Run Migrations' }}
          </button>

          <button
            @click="checkMigrationStatus"
            :disabled="loading.checkStatus"
            class="btn btn-secondary"
          >
            <span
              v-if="loading.checkStatus"
              class="loading-spinner"
            ></span>
            <span v-else>📊</span>
            {{ loading.checkStatus ? 'Checking...' : 'Check Status' }}
          </button>
        </div>
      </div>

      <!-- Status Section -->
      <div
        v-if="status.show"
        class="bg-white rounded-lg shadow-sm p-6"
      >
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Migration Status</h3>
          <span :class="`status-badge status-${status.type}`">
            {{ status.label }}
          </span>
        </div>

        <!-- Migration Status Table -->
        <div
          v-if="status.type === 'success' && migrationTable.length > 0"
          class="overflow-x-auto"
        >
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Migration
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Status
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Batch
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="(migration, index) in migrationTable"
                :key="index"
                :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
              >
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ migration.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="getMigrationStatusClass(migration.status)"
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                  >
                    {{ migration.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ migration.batch }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Output Box for non-table data -->
        <div
          v-else
          class="output-box"
        >
          <pre>{{ status.output }}</pre>
        </div>

        <div class="timestamp mt-3">Last updated: {{ status.timestamp }}</div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, reactive } from 'vue';
import adminService from '@/services/adminService';

// Reactive state
const loading = reactive({
  runMigration: false,
  checkStatus: false,
});

const alert = reactive({
  message: '',
  type: 'info',
});

const status = reactive({
  show: false,
  label: 'Ready',
  output: '',
  type: 'success',
  timestamp: '',
});

const migrationTable = ref([]);

// Alert management
const showAlert = (message, type = 'info') => {
  alert.message = message;
  alert.type = type;

  setTimeout(() => {
    alert.message = '';
  }, 5000);
};

const getAlertIcon = (type) => {
  const icons = {
    success: '✅',
    error: '❌',
    info: 'ℹ️',
    warning: '⚠️',
  };
  return icons[type] || icons.info;
};

// Status management
const updateStatus = (label, output, type = 'success') => {
  status.show = true;
  status.label = label;
  status.output = output;
  status.type = type;
  const now = new Date();
  const date = now.toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
  const time = now.toLocaleTimeString('en-US', {
    hour12: true,
    hour: '2-digit',
    minute: '2-digit',
  });
  status.timestamp = `${date}, ${time}`;

  // Parse migration status output into table format
  if (type === 'success' && output) {
    parseMigrationOutput(output);
  } else {
    migrationTable.value = [];
  }
};

// Parse migration output into table data
const parseMigrationOutput = (output) => {
  const lines = output.split('\n');
  const migrations = [];

  lines.forEach((line) => {
    // Match patterns like "2024_01_15_000000_create_users_table" and status info
    const migrationMatch = line.match(/(\d{4}_\d{2}_\d{2}_\d{6}_\w+)/);
    if (migrationMatch) {
      migrations.push({
        name: migrationMatch[1],
        status: 'Ran',
        batch: extractBatch(line),
      });
    }
  });

  migrationTable.value = migrations;
};

// Extract batch number from line
const extractBatch = (line) => {
  const batchMatch = line.match(/\[(\d+)\]/);
  return batchMatch ? batchMatch[1] : 'N/A';
};

// Get status class for styling
const getMigrationStatusClass = (status) => {
  const classes = {
    Ran: 'bg-green-100 text-green-800',
    Pending: 'bg-yellow-100 text-yellow-800',
    Failed: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

// API calls
const runMigrations = async () => {
  loading.runMigration = true;
  updateStatus('Running...', 'Executing database migrations...', 'loading');

  try {
    const response = await adminService.runMigrations();

    if (response.success) {
      showAlert('Migrations completed successfully!', 'success');
      updateStatus('Success', response.output || 'No output available', 'success');
    } else {
      showAlert(response.message || 'Migration failed', 'error');
      updateStatus('Error', response.error || 'Unknown error occurred', 'error');
    }
  } catch (error) {
    const errorMessage = error.response?.data?.message || error.message || 'Migration failed';
    showAlert(errorMessage, 'error');
    updateStatus('Error', errorMessage, 'error');
  } finally {
    loading.runMigration = false;
  }
};

const checkMigrationStatus = async () => {
  loading.checkStatus = true;
  updateStatus('Checking...', 'Retrieving migration status...', 'loading');

  try {
    const response = await adminService.checkMigrationStatus();

    if (response.success) {
      showAlert('Migration status retrieved successfully!', 'success');
      updateStatus('Status', response.status || 'No status available', 'success');
    } else {
      showAlert(response.error || 'Failed to get status', 'error');
      updateStatus('Error', response.error || 'Unknown error occurred', 'error');
    }
  } catch (error) {
    const errorMessage = error.response?.data?.message || error.message || 'Failed to get status';
    showAlert(errorMessage, 'error');
    updateStatus('Error', errorMessage, 'error');
  } finally {
    loading.checkStatus = false;
  }
};
</script>

<style scoped>
.alert {
  padding: 12px 16px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.alert-success {
  background: #c6f6d5;
  color: #22543d;
  border: 1px solid #9ae6b4;
}

.alert-error {
  background: #fed7d7;
  color: #742a2a;
  border: 1px solid #fc8181;
}

.alert-info {
  background: #bee3f8;
  color: #2c5282;
  border: 1px solid #90cdf4;
}

.alert-warning {
  background: #fef5e7;
  color: #744210;
  border: 1px solid #f6e05e;
}

.alert-icon {
  font-size: 16px;
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #5a67d8;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
  background: #48bb78;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background: #38a169;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(72, 187, 120, 0.4);
}

.loading-spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid #ffffff;
  border-radius: 50%;
  border-top-color: transparent;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.status-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.status-success {
  background: #c6f6d5;
  color: #22543d;
}

.status-error {
  background: #fed7d7;
  color: #742a2a;
}

.status-loading {
  background: #bee3f8;
  color: #2c5282;
}

.output-box {
  background: #1a202c;
  color: #e2e8f0;
  border-radius: 6px;
  padding: 16px;
  font-family: 'Monaco', 'Menlo', 'Consolas', monospace;
  font-size: 13px;
  line-height: 1.5;
  max-height: 400px;
  overflow-y: auto;
}

.output-box pre {
  margin: 0;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.timestamp {
  font-size: 12px;
  color: #a0aec0;
}
</style>
