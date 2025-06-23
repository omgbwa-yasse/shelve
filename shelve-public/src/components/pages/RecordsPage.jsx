import React, { useState, useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { recordsApi } from '../../services/shelveApi';
import { useApi } from '../../hooks/useApi';
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

  const {
    data: recordsData,
    loading,
    error,
    refetch
  } = useApi(
    () => recordsApi.getRecords({ ...filters, page: currentPage }),
    [filters, currentPage]
  );

  const records = recordsData?.data || [];
  const pagination = recordsData?.meta || {};

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

  const handleRecordClick = (recordId) => {
    navigate(`/records/${recordId}`);
  };
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
          <form onSubmit={handleSearch} className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div className="md:col-span-2 lg:col-span-1">
                <label htmlFor="search" className="block text-sm font-medium text-gray-700 mb-2">
                  Recherche
                </label>
                <input
                  type="text"
                  id="search"
                  value={filters.search}
                  onChange={(e) => handleFilterChange('search', e.target.value)}
                  placeholder="Titre, description, référence..."
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
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
                    status: 'published',
                    date_from: '',
                    date_to: '',
                    classification: ''
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

        {/* Barre d'outils */}
        <div className="toolbar flex justify-between items-center mb-6">
          <div className="results-info">
            <p className="text-gray-600">
              {pagination.total || 0} document(s) trouvé(s)
              {filters.search && ` pour "${filters.search}"`}
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
              {records.map(record => (                <RecordCard
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
            <div className="text-gray-400 text-6xl mb-4">📄</div>
            <h3 className="text-xl font-semibold text-gray-900 mb-2">
              Aucun document trouvé
            </h3>
            <p className="text-gray-600 mb-4">
              Essayez de modifier vos critères de recherche
            </p>
            <button
              onClick={() => {
                setFilters({
                  search: '',
                  type: '',
                  status: 'published',
                  date_from: '',
                  date_to: '',
                  classification: ''
                });
                setCurrentPage(1);
              }}
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
