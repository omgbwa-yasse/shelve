import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useQuery, useMutation } from 'react-query';
import { toast } from 'react-toastify';
import { useAuth } from '../../hooks/useAuth';
import shelveApi from '../../services/shelveApi';
import { formatDate } from '../../utils/dateUtils';
import Loading from '../common/Loading';
import ErrorMessage from '../common/ErrorMessage';
import { Button, Input, Select } from '../forms/FormComponents';

const UserDashboard = () => {
  const { user, logout } = useAuth();
  const [activeTab, setActiveTab] = useState('overview');
  const [profileData, setProfileData] = useState({
    name: user?.name || '',
    email: user?.email || '',
    phone: user?.phone || '',
    notifications: user?.preferences?.notifications || true,
    language: user?.preferences?.language || 'fr'
  });

  // Fetch user dashboard data
  const {
    data: dashboardData,
    isLoading: isDashboardLoading,
    error: dashboardError,
    refetch: refetchDashboard
  } = useQuery(
    ['user-dashboard', user?.id],
    () => shelveApi.getUserDashboard(),
    {
      enabled: !!user,
      onError: (error) => {
        console.error('Error fetching dashboard:', error);
        toast.error('Erreur lors du chargement du tableau de bord');
      }
    }
  );

  // Update profile mutation
  const updateProfileMutation = useMutation(
    (data) => shelveApi.updateUserProfile(data),
    {
      onSuccess: () => {
        toast.success('Profil mis à jour avec succès');
        refetchDashboard();
      },
      onError: (error) => {
        console.error('Error updating profile:', error);
        toast.error('Erreur lors de la mise à jour du profil');
      }
    }
  );

  // Delete account mutation
  const deleteAccountMutation = useMutation(
    () => shelveApi.deleteUserAccount(),
    {
      onSuccess: () => {
        toast.success('Compte supprimé avec succès');
        logout();
      },
      onError: (error) => {
        console.error('Error deleting account:', error);
        toast.error('Erreur lors de la suppression du compte');
      }
    }
  );

  // Update page title
  useEffect(() => {
    document.title = 'Mon espace - Shelve';
    return () => {
      document.title = 'Shelve';
    };
  }, []);

  // Handle profile update
  const handleProfileUpdate = (e) => {
    e.preventDefault();
    updateProfileMutation.mutate(profileData);
  };

  // Handle profile data change
  const handleProfileChange = (field, value) => {
    setProfileData(prev => ({
      ...prev,
      [field]: value
    }));
  };

  // Handle account deletion
  const handleDeleteAccount = () => {
    if (window.confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.')) {
      deleteAccountMutation.mutate();
    }
  };

  if (!user) {
    return (
      <ErrorMessage
        title="Accès non autorisé"
        message="Vous devez être connecté pour accéder à cette page."
        variant="warning"
        size="large"
      />
    );
  }

  if (isDashboardLoading) {
    return <Loading size="large" message="Chargement de votre espace..." />;
  }

  if (dashboardError) {
    return (
      <ErrorMessage
        title="Erreur de chargement"
        message="Impossible de charger votre espace. Veuillez réessayer."
        onRetry={refetchDashboard}
        variant="error"
      />
    );
  }

  const data = dashboardData || {};
  const stats = data.stats || {};
  const recentActivities = data.recent_activities || [];
  const documentRequests = data.document_requests || [];
  const favoriteRecords = data.favorite_records || [];

  const tabs = [
    { id: 'overview', label: 'Vue d\'ensemble', icon: '📊' },
    { id: 'requests', label: 'Mes demandes', icon: '📋' },
    { id: 'favorites', label: 'Mes favoris', icon: '❤️' },
    { id: 'profile', label: 'Mon profil', icon: '👤' },
    { id: 'settings', label: 'Paramètres', icon: '⚙️' }
  ];

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2">
            Bonjour, {user.name}
          </h1>
          <p className="text-lg text-gray-600">
            Gérez vos demandes, favoris et paramètres de compte
          </p>
        </div>

        {/* Navigation tabs */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
          <div className="border-b border-gray-200">
            <nav className="-mb-px flex space-x-8 px-6" aria-label="Onglets">
              {tabs.map((tab) => (
                <button
                  key={tab.id}
                  onClick={() => setActiveTab(tab.id)}
                  className={`py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors ${
                    activeTab === tab.id
                      ? 'border-blue-500 text-blue-600'
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  }`}
                  aria-current={activeTab === tab.id ? 'page' : undefined}
                >
                  <span className="mr-2">{tab.icon}</span>
                  {tab.label}
                </button>
              ))}
            </nav>
          </div>

          {/* Tab content */}
          <div className="p-6">
            {/* Overview Tab */}
            {activeTab === 'overview' && (
              <div className="space-y-6">
                {/* Statistics */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                  <StatCard
                    title="Demandes en cours"
                    value={stats.pending_requests || 0}
                    icon="📋"
                    color="blue"
                  />
                  <StatCard
                    title="Documents consultés"
                    value={stats.viewed_documents || 0}
                    icon="📄"
                    color="green"
                  />
                  <StatCard
                    title="Favoris"
                    value={stats.favorite_count || 0}
                    icon="❤️"
                    color="red"
                  />
                  <StatCard
                    title="Recherches"
                    value={stats.search_count || 0}
                    icon="🔍"
                    color="purple"
                  />
                </div>

                {/* Recent activities */}
                <div>
                  <h3 className="text-lg font-semibold text-gray-900 mb-4">
                    Activités récentes
                  </h3>
                  {recentActivities.length > 0 ? (
                    <div className="space-y-3">
                      {recentActivities.slice(0, 5).map((activity) => (
                        <ActivityItem key={activity.id} activity={activity} />
                      ))}
                    </div>
                  ) : (
                    <p className="text-gray-500">Aucune activité récente</p>
                  )}
                </div>

                {/* Quick actions */}
                <div>
                  <h3 className="text-lg font-semibold text-gray-900 mb-4">
                    Actions rapides
                  </h3>
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Link
                      to="/documents/request"
                      className="block p-4 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors"
                    >
                      <div className="flex items-center">
                        <span className="text-2xl mr-3">📋</span>
                        <div>
                          <h4 className="font-medium text-blue-900">Nouvelle demande</h4>
                          <p className="text-sm text-blue-700">Demander l'accès à un document</p>
                        </div>
                      </div>
                    </Link>

                    <Link
                      to="/records"
                      className="block p-4 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors"
                    >
                      <div className="flex items-center">
                        <span className="text-2xl mr-3">🔍</span>
                        <div>
                          <h4 className="font-medium text-green-900">Explorer les archives</h4>
                          <p className="text-sm text-green-700">Découvrir les documents</p>
                        </div>
                      </div>
                    </Link>

                    <Link
                      to="/feedback"
                      className="block p-4 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 transition-colors"
                    >
                      <div className="flex items-center">
                        <span className="text-2xl mr-3">💬</span>
                        <div>
                          <h4 className="font-medium text-purple-900">Donner son avis</h4>
                          <p className="text-sm text-purple-700">Partager vos commentaires</p>
                        </div>
                      </div>
                    </Link>
                  </div>
                </div>
              </div>
            )}

            {/* Requests Tab */}
            {activeTab === 'requests' && (
              <div>
                <h3 className="text-lg font-semibold text-gray-900 mb-4">
                  Mes demandes de consultation
                </h3>
                {documentRequests.length > 0 ? (
                  <div className="space-y-4">
                    {documentRequests.map((request) => (
                      <RequestItem key={request.id} request={request} />
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-8">
                    <p className="text-gray-500 mb-4">Aucune demande en cours</p>
                    <Link to="/documents/request">
                      <Button variant="primary">
                        Faire une nouvelle demande
                      </Button>
                    </Link>
                  </div>
                )}
              </div>
            )}

            {/* Favorites Tab */}
            {activeTab === 'favorites' && (
              <div>
                <h3 className="text-lg font-semibold text-gray-900 mb-4">
                  Mes documents favoris
                </h3>
                {favoriteRecords.length > 0 ? (
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {favoriteRecords.map((record) => (
                      <FavoriteItem key={record.id} record={record} />
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-8">
                    <p className="text-gray-500 mb-4">Aucun document en favori</p>
                    <Link to="/records">
                      <Button variant="primary">
                        Explorer les archives
                      </Button>
                    </Link>
                  </div>
                )}
              </div>
            )}

            {/* Profile Tab */}
            {activeTab === 'profile' && (
              <div>
                <h3 className="text-lg font-semibold text-gray-900 mb-4">
                  Informations personnelles
                </h3>
                <form onSubmit={handleProfileUpdate} className="max-w-md space-y-4">
                  <Input
                    label="Nom complet"
                    value={profileData.name}
                    onChange={(value) => handleProfileChange('name', value)}
                    required
                  />

                  <Input
                    label="Adresse email"
                    type="email"
                    value={profileData.email}
                    onChange={(value) => handleProfileChange('email', value)}
                    required
                  />

                  <Input
                    label="Téléphone"
                    type="tel"
                    value={profileData.phone}
                    onChange={(value) => handleProfileChange('phone', value)}
                  />

                  <div className="flex items-center space-x-3">
                    <Button
                      type="submit"
                      variant="primary"
                      disabled={updateProfileMutation.isLoading}
                    >
                      {updateProfileMutation.isLoading ? 'Mise à jour...' : 'Mettre à jour'}
                    </Button>
                  </div>
                </form>
              </div>
            )}

            {/* Settings Tab */}
            {activeTab === 'settings' && (
              <div className="space-y-6">
                <div>
                  <h3 className="text-lg font-semibold text-gray-900 mb-4">
                    Préférences
                  </h3>
                  <div className="max-w-md space-y-4">
                    <Select
                      label="Langue"
                      value={profileData.language}
                      onChange={(value) => handleProfileChange('language', value)}
                      options={[
                        { value: 'fr', label: 'Français' },
                        { value: 'en', label: 'English' }
                      ]}
                    />

                    <div className="flex items-center space-x-3">
                      <input
                        type="checkbox"
                        id="notifications"
                        checked={profileData.notifications}
                        onChange={(e) => handleProfileChange('notifications', e.target.checked)}
                        className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                      />
                      <label htmlFor="notifications" className="text-sm font-medium text-gray-700">
                        Recevoir les notifications par email
                      </label>
                    </div>
                  </div>
                </div>

                {/* Danger zone */}
                <div className="border-t border-gray-200 pt-6">
                  <h3 className="text-lg font-semibold text-red-900 mb-4">
                    Zone de danger
                  </h3>
                  <div className="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h4 className="text-red-800 font-medium mb-2">
                      Supprimer mon compte
                    </h4>
                    <p className="text-red-700 text-sm mb-4">
                      Cette action est irréversible. Toutes vos données seront définitivement supprimées.
                    </p>
                    <Button
                      onClick={handleDeleteAccount}
                      variant="danger"
                      disabled={deleteAccountMutation.isLoading}
                    >
                      {deleteAccountMutation.isLoading ? 'Suppression...' : 'Supprimer mon compte'}
                    </Button>
                  </div>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

// Stat Card Component
const StatCard = ({ title, value, icon, color }) => {
  const colorClasses = {
    blue: 'bg-blue-50 text-blue-600',
    green: 'bg-green-50 text-green-600',
    red: 'bg-red-50 text-red-600',
    purple: 'bg-purple-50 text-purple-600'
  };

  return (
    <div className="bg-white border border-gray-200 rounded-lg p-6">
      <div className="flex items-center">
        <div className={`p-2 rounded-md ${colorClasses[color]}`}>
          <span className="text-xl">{icon}</span>
        </div>
        <div className="ml-4">
          <p className="text-sm font-medium text-gray-600">{title}</p>
          <p className="text-2xl font-bold text-gray-900">{value}</p>
        </div>
      </div>
    </div>
  );
};

// Activity Item Component
const ActivityItem = ({ activity }) => {
  return (
    <div className="flex items-center space-x-3 py-2">
      <div className="flex-shrink-0">
        <span className="text-lg">{activity.icon || '📝'}</span>
      </div>
      <div className="flex-1 min-w-0">
        <p className="text-sm text-gray-900">{activity.description}</p>
        <p className="text-xs text-gray-500">{formatDate(activity.created_at)}</p>
      </div>
    </div>
  );
};

// Request Item Component
const RequestItem = ({ request }) => {
  const statusColors = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    processing: 'bg-blue-100 text-blue-800'
  };

  return (
    <div className="bg-white border border-gray-200 rounded-lg p-4">
      <div className="flex items-center justify-between mb-2">
        <h4 className="font-medium text-gray-900">{request.subject}</h4>
        <span className={`px-2 py-1 text-xs font-medium rounded-full ${statusColors[request.status]}`}>
          {request.status_label}
        </span>
      </div>
      <p className="text-sm text-gray-600 mb-2">{request.description}</p>
      <p className="text-xs text-gray-500">
        Demandé le {formatDate(request.created_at)}
      </p>
    </div>
  );
};

// Favorite Item Component
const FavoriteItem = ({ record }) => {
  return (
    <Link
      to={`/records/${record.id}`}
      className="block bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
    >
      <h4 className="font-medium text-gray-900 mb-1 line-clamp-2">{record.title}</h4>
      <p className="text-sm text-gray-600 line-clamp-2">{record.description}</p>
      <p className="text-xs text-gray-500 mt-2">{formatDate(record.date)}</p>
    </Link>
  );
};

export default UserDashboard;
