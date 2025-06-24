import React, { useState, useEffect, useCallback, useMemo } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { recordsApi } from '../../services/shelveApi';
import { useApi } from '../../hooks/useApi';
import { useDebounce } from '../../hooks/useDebounce';
import Loading from '../common/Loading';
import ErrorMessage from '../common/ErrorMessage';
import { formatDate } from '../../utils/dateUtils';
import { truncateText } from '../../utils/helpers';
import { RECORD_TYPES } from '../../utils/constants';

const RecordsPage = () => {
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();
  const [filters, setFilters] = useState({
    search: searchParams.get('search') || '',
    type: searchParams.get('type') || '',
    status: searchParams.get('status') || 'published',
    date_from: searchParams.get('date_from') || '',
    date_to: searchParams.get('date_to') || '',
    classification: searchParams.get('classification') || ''
  });
  const [currentPage, setCurrentPage] = useState(parseInt(searchParams.get('page')) || 1);
  const [viewMode, setViewMode] = useState('grid'); // 'grid' ou 'list'

  // Débounce pour la recherche en temps réel
  const debouncedSearch = useDebounce(filters.search, 500);

  // Paramètres finaux pour l'API (avec le search débounced)
  const apiFilters = useMemo(() => ({
    ...filters,
    search: debouncedSearch
  }), [filters, debouncedSearch]);

  const {
    data: recordsData,
    loading,
    error,
    refetch
  } = useApi(
    () => recordsApi.getRecords({ ...apiFilters, page: currentPage }),
    [apiFilters, currentPage]
  );

  const records = recordsData?.data || [];
  const pagination = recordsData?.meta || {};

  // Mise à jour des paramètres URL (seulement pour les filtres non-search)
  useEffect(() => {
    const params = new URLSearchParams();
    Object.entries(apiFilters).forEach(([key, value]) => {
      if (value) params.set(key, value);
    });
    if (currentPage > 1) params.set('page', currentPage);
    setSearchParams(params);
  }, [apiFilters, currentPage, setSearchParams]);

  // Reset de la page quand les filtres changent
  useEffect(() => {
    if (currentPage > 1) {
      setCurrentPage(1);
    }
  }, [apiFilters, currentPage]);

  const handleFilterChange = useCallback((field, value) => {
    setFilters(prev => ({ ...prev, [field]: value }));
  }, []);

  const handlePageChange = useCallback((page) => {
    setCurrentPage(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }, []);

  const handleRecordClick = useCallback((recordId) => {
    navigate(`/records/${recordId}`);
  }, [navigate]);

  const resetFilters = useCallback(() => {
    setFilters({
      search: '',
      type: '',
      status: 'published',
      date_from: '',
      date_to: '',
      classification: ''
    });
    setCurrentPage(1);
  }, []);

  // Raccourci clavier pour focus sur la recherche (Ctrl+K ou Cmd+K)
  useEffect(() => {
    const handleKeyDown = (event) => {
      if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
        event.preventDefault();
        const searchInput = document.getElementById('search');
        if (searchInput) {
          searchInput.focus();
          searchInput.select();
        }
      }
    };

    document.addEventListener('keydown', handleKeyDown);
    return () => document.removeEventListener('keydown', handleKeyDown);
  }, []);
  const getTypeInfo = (type) => {
    return RECORD_TYPES.find(t => t.value === type) || { label: type, value: type };
  };

  if (loading && !records.length) return <Loading />;
  if (error) return <ErrorMessage message={error} onRetry={refetch} />;

  return (
    <div className="records-page">
      <div className="container mx-auto px-4 py-8">
        <div className="page-header mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-4">
            Archives et Documents
          </h1>
          <p className="text-lg text-gray-600">
            Explorez notre collection d'archives et de documents historiques
          </p>
        </div>

        {/* Filtres */}
        <div className="filters-section bg-white rounded-lg shadow-md p-6 mb-8">
          <div className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div className="md:col-span-2 lg:col-span-1">
                <label htmlFor="search" className="block text-sm font-medium text-gray-700 mb-2">
                  Recherche
                  {loading && debouncedSearch !== filters.search && (
                    <span className="ml-2 text-xs text-blue-600">Recherche...</span>
                  )}
                  <kbd className="ml-2 inline-flex items-center px-2 py-1 text-xs font-mono text-gray-500 bg-gray-100 border border-gray-300 rounded">
                    Ctrl+K
                  </kbd>
                </label>
                <div className="relative">
                  <input
                    type="text"
                    id="search"
                    value={filters.search}
                    onChange={(e) => handleFilterChange('search', e.target.value)}
                    placeholder="Titre, description, référence..."
                    className="w-full px-3 py-2 pl-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                  <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg className="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                  </div>
                </div>
              </div>

              <div>
                <label htmlFor="type" className="block text-sm font-medium text-gray-700 mb-2">
                  Type de document
                </label>
                <select
                  id="type"
                  value={filters.type}
                  onChange={(e) => handleFilterChange('type', e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Tous les types</option>
                  {RECORD_TYPES.map(type => (
                    <option key={type.value} value={type.value}>
                      {type.label}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label htmlFor="classification" className="block text-sm font-medium text-gray-700 mb-2">
                  Classification
                </label>
                <input
                  type="text"
                  id="classification"
                  value={filters.classification}
                  onChange={(e) => handleFilterChange('classification', e.target.value)}
                  placeholder="Cote, série..."
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
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
              <div className="text-sm text-gray-600">
                {loading && debouncedSearch !== filters.search ? (
                  <span className="flex items-center">
                    <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24">
                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Recherche en cours...
                  </span>
                ) : (
                  <span>Recherche automatique activée</span>
                )}
              </div>

              <button
                type="button"
                onClick={resetFilters}
                className="flex items-center text-gray-600 hover:text-gray-800 hover:bg-gray-50 px-3 py-2 rounded-md transition-colors"
              >
                <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Réinitialiser
              </button>
            </div>
          </div>
        </div>

        {/* Barre d'outils */}
        <div className="toolbar flex justify-between items-center mb-6">
          <div className="results-info">
            <p className="text-gray-600">
              {pagination.total || 0} document(s) trouvé(s)
              {filters.search && ` pour "${filters.search}"`}
              {loading && (
                <span className="ml-2 inline-flex items-center">
                  <svg className="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
              )}
            </p>
          </div>

          <div className="view-controls flex items-center gap-2">
            <span className="text-sm text-gray-600">Affichage :</span>
            <button
              onClick={() => setViewMode('grid')}
              className={`p-2 rounded ${viewMode === 'grid' ? 'bg-blue-100 text-blue-600' : 'text-gray-600 hover:bg-gray-100'}`}
              aria-label="Vue en grille"
            >
              ⊞
            </button>
            <button
              onClick={() => setViewMode('list')}
              className={`p-2 rounded ${viewMode === 'list' ? 'bg-blue-100 text-blue-600' : 'text-gray-600 hover:bg-gray-100'}`}
              aria-label="Vue en liste"
            >
              ☰
            </button>
          </div>
        </div>

        {/* Résultats */}
        {records.length > 0 ? (
          <>
            <div className={`records-container ${
              viewMode === 'grid'
                ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6'
                : 'space-y-4'
            } mb-8`}>
              {records.map(record => (
                <RecordCard
                  key={record.id}
                  record={record}
                  viewMode={viewMode}
                  onClick={() => handleRecordClick(record.id)}
                  getTypeInfo={getTypeInfo}
                />
              ))}
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
            <div className="text-gray-400 text-6xl mb-4">
              {filters.search || filters.type || filters.classification || filters.date_from || filters.date_to ? '🔍' : '📄'}
            </div>
            <h3 className="text-xl font-semibold text-gray-900 mb-2">
              {filters.search || filters.type || filters.classification || filters.date_from || filters.date_to
                ? 'Aucun résultat pour cette recherche'
                : 'Aucun document trouvé'
              }
            </h3>
            <p className="text-gray-600 mb-4">
              {filters.search || filters.type || filters.classification || filters.date_from || filters.date_to
                ? 'Essayez de modifier vos critères de recherche ou d\'élargir votre recherche'
                : 'Il n\'y a actuellement aucun document disponible dans les archives'
              }
            </p>
            <button
              onClick={resetFilters}
              className="text-blue-600 hover:text-blue-800 font-medium"
            >
              Voir tous les documents
            </button>
          </div>
        )}
      </div>
    </div>
  );
};

// Composant pour afficher une carte de document
const RecordCard = ({ record, viewMode, onClick, getTypeInfo }) => {
  const typeInfo = getTypeInfo(record.type);

  if (viewMode === 'list') {
    return (
      <button
        onClick={onClick}
        className="record-card-list w-full text-left bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
      >
        <div className="flex items-start gap-4">
          {record.thumbnail_url && (
            <div className="record-thumbnail flex-shrink-0 w-16 h-20 overflow-hidden rounded">
              <img
                src={record.thumbnail_url}
                alt={record.title}
                className="w-full h-full object-cover"
              />
            </div>
          )}

          <div className="record-content flex-1">
            <div className="record-meta flex flex-wrap items-center gap-3 mb-2">
              <span className="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                {typeInfo.label}
              </span>

              {record.reference && (
                <span className="text-sm text-gray-500 font-mono">
                  {record.reference}
                </span>
              )}

              <span className="text-sm text-gray-500">
                {formatDate(record.date || record.created_at)}
              </span>
            </div>

            <h3 className="record-title text-lg font-semibold text-gray-900 mb-2">
              {record.title}
            </h3>

            {record.description && (
              <p className="record-description text-gray-600 text-sm mb-2">
                {truncateText(record.description, 200)}
              </p>
            )}

            <div className="record-footer flex items-center gap-4 text-sm text-gray-500">
              {record.location && (
                <span>📍 {record.location}</span>
              )}
              {record.digital_copy_available && (
                <span className="text-green-600">✓ Copie numérique</span>
              )}
            </div>
          </div>
        </div>
      </button>
    );
  }

  return (
    <button
      onClick={onClick}
      className="record-card bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow text-left w-full"
    >
      {record.thumbnail_url && (
        <div className="record-image h-48 overflow-hidden">
          <img
            src={record.thumbnail_url}
            alt={record.title}
            className="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
          />
        </div>
      )}

      <div className="record-content p-6">
        <div className="record-meta mb-3">
          <span className="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
            {typeInfo.label}
          </span>
          {record.reference && (
            <span className="text-sm text-gray-500 ml-2 font-mono">
              {record.reference}
            </span>
          )}
        </div>

        <h3 className="record-title text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
          {record.title}
        </h3>

        {record.description && (
          <p className="record-description text-gray-600 text-sm line-clamp-3 mb-4">
            {truncateText(record.description, 150)}
          </p>
        )}

        <div className="record-footer">
          <div className="text-sm text-gray-500 mb-2">
            {formatDate(record.date || record.created_at)}
          </div>

          <div className="flex justify-between items-center">
            <span className="text-blue-600 text-sm font-medium">
              Consulter →
            </span>

            {record.digital_copy_available && (
              <span className="text-xs text-green-600">
                Copie numérique
              </span>
            )}
          </div>
        </div>
      </div>
    </button>
  );
};

export default RecordsPage;
