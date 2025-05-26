
<template>
  <div class="ollama-dashboard p-6 space-y-6">
    <!-- Header avec status global -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard Ollama</h1>
        <div class="flex items-center space-x-4">
          <div class="flex items-center">
            <div :class="`w-3 h-3 rounded-full mr-2 ${healthStatus === 'healthy' ? 'bg-green-500' : 'bg-red-500'}`"></div>
            <span class="text-sm font-medium">{{ healthStatus === 'healthy' ? 'En ligne' : 'Hors ligne' }}</span>
          </div>
          <button
            @click="refreshData"
            :disabled="loading"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50"
          >
            <svg v-if="loading" class="animate-spin w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"/>
            </svg>
            <span v-else>Actualiser</span>
          </button>
          <button
            @click="syncModels"
            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
          >
            Sync Modèles
          </button>
        </div>
      </div>
    </div>

    <!-- Statistiques globales -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-2 bg-blue-100 rounded">
            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
              <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Modèles Actifs</p>
            <p class="text-2xl font-semibold text-gray-900">{{ dashboardData.active_models || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-2 bg-green-100 rounded">
            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Interactions Réussies</p>
            <p class="text-2xl font-semibold text-gray-900">{{ dashboardData.stats?.successful_interactions || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-2 bg-red-100 rounded">
            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Interactions Échouées</p>
            <p class="text-2xl font-semibold text-gray-900">{{ dashboardData.stats?.failed_interactions || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-2 bg-purple-100 rounded">
            <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
              <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Interactions</p>
            <p class="text-2xl font-semibold text-gray-900">{{ dashboardData.stats?.total_interactions || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Liste des modèles -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">Modèles Ollama</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modèle</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taille</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paramètres</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dernière utilisation</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="model in models" :key="model.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                      <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ model.name }}</div>
                    <div class="text-sm text-gray-500">{{ model.model_family || 'Unknown' }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ model.formatted_size || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ model.parameter_size_formatted || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${model.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`">
                  {{ model.is_active ? 'Actif' : 'Inactif' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(model.updated_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                    <div
                      class="bg-blue-600 h-2 rounded-full"
                      :style="`width: ${getSuccessRate(model)}%`"
                    ></div>
                  </div>
                  <span class="text-sm text-gray-500">{{ getSuccessRate(model) }}%</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Interactions récentes -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">Interactions Récentes</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modèle</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prompt</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tokens</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durée</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="interaction in recentInteractions" :key="interaction.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ interaction.user?.name || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ interaction.ai_model?.name || 'N/A' }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                {{ truncateText(interaction.input, 50) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(interaction.status)}`">
                  {{ interaction.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ interaction.tokens_used || 0 }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDuration(interaction.total_duration) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(interaction.created_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OllamaDashboard',
  data() {
    return {
      loading: false,
      healthStatus: 'unknown',
      dashboardData: {},
      models: [],
      recentInteractions: []
    }
  },

  mounted() {
    this.loadDashboardData();
  },

  methods: {
    async loadDashboardData() {
      this.loading = true;
      try {
        const response = await fetch('/api/ai/ollama/dashboard');
        const data = await response.json();

        this.healthStatus = data.health?.status || 'unknown';
        this.dashboardData = data;
        this.models = data.models || [];
        this.recentInteractions = data.recent_interactions || [];
      } catch (error) {
        console.error('Erreur lors du chargement du dashboard:', error);
      } finally {
        this.loading = false;
      }
    },

    async refreshData() {
      await this.loadDashboardData();
    },

    async syncModels() {
      try {
        const response = await fetch('/api/ai/ollama/models/sync', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        });

        const data = await response.json();
        if (data.success) {
          alert(`${data.synced_count} modèles synchronisés avec succès`);
          await this.refreshData();
        } else {
          alert('Erreur lors de la synchronisation: ' + data.message);
        }
      } catch (error) {
        alert('Erreur lors de la synchronisation: ' + error.message);
      }
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleString('fr-FR');
    },

    formatDuration(duration) {
      if (!duration) return 'N/A';
      const ms = Math.round(duration / 1000000);
      return ms + 'ms';
    },

    truncateText(text, maxLength) {
      if (!text) return 'N/A';
      return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    },

    getSuccessRate(model) {
      // Calculer le taux de succès basé sur les statistiques du modèle
      // Cette logique devrait être adaptée selon votre structure de données
      return Math.floor(Math.random() * 100); // Placeholder
    },

    getStatusColor(status) {
      const colors = {
        'completed': 'bg-green-100 text-green-800',
        'failed': 'bg-red-100 text-red-800',
        'processing': 'bg-yellow-100 text-yellow-800',
        'pending': 'bg-gray-100 text-gray-800'
      };
      return colors[status] || 'bg-gray-100 text-gray-800';
    }
  }
}
</script>

<style scoped>
.ollama-dashboard {
  min-height: 100vh;
  background-color: #f9fafb;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

table {
  font-size: 0.875rem;
}

.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>