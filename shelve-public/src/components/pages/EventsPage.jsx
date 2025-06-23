import React, { useState, useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { eventsApi } from '../../services/shelveApi';
import { useApi } from '../../hooks/useApi';
import Loading from '../common/Loading';
import ErrorMessage from '../common/ErrorMessage';
import { formatDate } from '../../utils/dateUtils';
import { truncateText } from '../../utils/helpers';
import { EVENT_TYPES } from '../../utils/constants';

const EventsPage = () => {
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();
  const [filters, setFilters] = useState({
    search: searchParams.get('search') || '',
    type: searchParams.get('type') || '',
    date_from: searchParams.get('date_from') || '',
    date_to: searchParams.get('date_to') || '',
    status: searchParams.get('status') || 'published'
  });
  const [currentPage, setCurrentPage] = useState(parseInt(searchParams.get('page')) || 1);

  const {
    data: eventsData,
    loading,
    error,
    refetch
  } = useApi(
    () => eventsApi.getEvents({ ...filters, page: currentPage }),
    [filters, currentPage]
  );

  const events = eventsData?.data || [];
  const pagination = eventsData?.meta || {};

  // Mise à jour des paramètres URL
  useEffect(() => {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([key, value]) => {
      if (value) params.set(key, value);
    });
    if (currentPage > 1) params.set('page', currentPage);
    setSearchParams(params);
  }, [filters, currentPage, setSearchParams]);

  const handleFilterChange = (field, value) => {
    setFilters(prev => ({ ...prev, [field]: value }));
    setCurrentPage(1);
  };

  const handleSearch = (e) => {
    e.preventDefault();
    refetch();
  };

  const handlePageChange = (page) => {
    setCurrentPage(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleEventClick = (eventId) => {
    navigate(`/events/${eventId}`);
  };

  if (loading && !events.length) return <Loading />;
  if (error) return <ErrorMessage message={error} onRetry={refetch} />;

  return (
    <div className="events-page">
      <div className="container mx-auto px-4 py-8">
        <div className="page-header mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-4">
            Événements et Actualités
          </h1>
          <p className="text-lg text-gray-600">
            Découvrez les derniers événements et actualités de nos archives
          </p>
        </div>

        {/* Filtres */}
        <div className="filters-section bg-white rounded-lg shadow-md p-6 mb-8">
          <form onSubmit={handleSearch} className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <div>
                <label htmlFor="search" className="block text-sm font-medium text-gray-700 mb-2">
                  Recherche
                </label>
                <input
                  type="text"
                  id="search"
                  value={filters.search}
                  onChange={(e) => handleFilterChange('search', e.target.value)}
                  placeholder="Mots-clés..."
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label htmlFor="type" className="block text-sm font-medium text-gray-700 mb-2">
                  Type
                </label>
                <select
                  id="type"
                  value={filters.type}
                  onChange={(e) => handleFilterChange('type', e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Tous les types</option>
                  {EVENT_TYPES.map(type => (
                    <option key={type.value} value={type.value}>
                      {type.label}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label htmlFor="date_from" className="block text-sm font-medium text-gray-700 mb-2">
                  Date de début
                </label>
                <input
                  type="date"
                  id="date_from"
                  value={filters.date_from}
                  onChange={(e) => handleFilterChange('date_from', e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label htmlFor="date_to" className="block text-sm font-medium text-gray-700 mb-2">
                  Date de fin
                </label>
                <input
                  type="date"
                  id="date_to"
                  value={filters.date_to}
                  onChange={(e) => handleFilterChange('date_to', e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <div className="flex justify-between items-center">
              <button
                type="submit"
                disabled={loading}
                className="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
              >
                {loading ? 'Recherche...' : 'Rechercher'}
              </button>

              <button
                type="button"
                onClick={() => {
                  setFilters({
                    search: '',
                    type: '',
                    date_from: '',
                    date_to: '',
                    status: 'published'
                  });
                  setCurrentPage(1);
                }}
                className="text-gray-600 hover:text-gray-800"
              >
                Réinitialiser
              </button>
            </div>
          </form>
        </div>

        {/* Résultats */}
        {events.length > 0 ? (
          <>
            <div className="results-info mb-6">
              <p className="text-gray-600">
                {pagination.total || 0} événement(s) trouvé(s)
                {filters.search && ` pour "${filters.search}"`}
              </p>
            </div>            <div className="events-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
              {events.map(event => {
                const getTypeClasses = (type) => {
                  if (type === 'event') return 'bg-blue-100 text-blue-800';
                  if (type === 'news') return 'bg-green-100 text-green-800';
                  return 'bg-gray-100 text-gray-800';
                };

                return (
                  <button
                    key={event.id}
                    onClick={() => handleEventClick(event.id)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        handleEventClick(event.id);
                      }
                    }}
                    className="event-card bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow cursor-pointer text-left w-full"
                    aria-label={`Voir l'événement: ${event.title}`}
                  >
                    {event.image_url && (
                      <div className="event-image h-48 overflow-hidden">
                        <img
                          src={event.image_url}
                          alt={event.title}
                          className="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                        />
                      </div>
                    )}

                    <div className="event-content p-6">
                      <div className="event-meta mb-3">
                        <span className={`inline-block px-2 py-1 text-xs font-semibold rounded-full ${getTypeClasses(event.type)}`}>
                          {EVENT_TYPES.find(t => t.value === event.type)?.label || event.type}
                        </span>
                        <span className="text-sm text-gray-500 ml-2">
                          {formatDate(event.date || event.created_at)}
                        </span>
                      </div>

                      <h3 className="event-title text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                        {event.title}
                      </h3>

                      {event.description && (
                        <p className="event-description text-gray-600 text-sm line-clamp-3 mb-4">
                          {truncateText(event.description, 150)}
                        </p>
                      )}

                      <div className="event-footer flex justify-between items-center">
                        <span className="text-blue-600 text-sm font-medium hover:text-blue-800">
                          Lire la suite →
                        </span>

                        {event.location && (
                          <span className="text-xs text-gray-500">
                            📍 {event.location}
                          </span>
                        )}
                      </div>
                    </div>
                  </button>
                );
              })}
            </div>

            {/* Pagination */}
            {pagination.last_page > 1 && (
              <div className="pagination flex justify-center items-center space-x-2">
                <button
                  onClick={() => handlePageChange(currentPage - 1)}
                  disabled={currentPage === 1}
                  className="px-3 py-2 text-sm border border-gray-300 rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                  Précédent
                </button>

                {[...Array(Math.min(5, pagination.last_page))].map((_, index) => {
                  const page = Math.max(1, currentPage - 2) + index;
                  if (page > pagination.last_page) return null;

                  return (
                    <button
                      key={page}
                      onClick={() => handlePageChange(page)}
                      className={`px-3 py-2 text-sm border border-gray-300 rounded-md ${
                        page === currentPage
                          ? 'bg-blue-600 text-white border-blue-600'
                          : 'hover:bg-gray-50'
                      }`}
                    >
                      {page}
                    </button>
                  );
                })}

                <button
                  onClick={() => handlePageChange(currentPage + 1)}
                  disabled={currentPage === pagination.last_page}
                  className="px-3 py-2 text-sm border border-gray-300 rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                  Suivant
                </button>
              </div>
            )}
          </>
        ) : (
          <div className="no-results text-center py-12">
            <div className="text-gray-400 text-6xl mb-4">📅</div>
            <h3 className="text-xl font-semibold text-gray-900 mb-2">
              Aucun événement trouvé
            </h3>
            <p className="text-gray-600 mb-4">
              Essayez de modifier vos critères de recherche
            </p>
            <button
              onClick={() => {
                setFilters({
                  search: '',
                  type: '',
                  date_from: '',
                  date_to: '',
                  status: 'published'
                });
                setCurrentPage(1);
              }}
              className="text-blue-600 hover:text-blue-800 font-medium"
            >
              Voir tous les événements
            </button>
          </div>
        )}
      </div>
    </div>
  );
};

export default EventsPage;
