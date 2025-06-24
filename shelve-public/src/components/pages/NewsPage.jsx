import React, { useState, useCallback } from 'react';
import { Link } from 'react-router-dom';
import { useQuery } from 'react-query';
import { toast } from 'react-toastify';
import shelveApi from '../../services/shelveApi';
import { formatDate } from '../../utils/dateUtils';
import { PAGINATION_DEFAULTS } from '../../utils/constants';
import Loading from '../common/Loading';
import ErrorMessage from '../common/ErrorMessage';
import { SearchInput, Button, Select } from '../forms/FormComponents';

const NewsPage = () => {
  const [currentPage, setCurrentPage] = useState(1);
  const [searchTerm, setSearchTerm] = useState('');
  const [sortBy, setSortBy] = useState('date');
  const [sortOrder, setSortOrder] = useState('desc');
  const [category, setCategory] = useState('');

  // Fetch news data
  const {
    data: newsData,
    isLoading,
    error,
    refetch
  } = useQuery(
    ['news', { page: currentPage, search: searchTerm, sortBy, sortOrder, category }],
    async () => {
      const response = await shelveApi.getNews({
        page: currentPage,
        per_page: PAGINATION_DEFAULTS.PER_PAGE,
        search: searchTerm,
        sort_by: sortBy,
        sort_order: sortOrder,
        category
      });
      return response.data; // Extract data from axios response
    },
    {
      keepPreviousData: true,
      onError: (error) => {
        console.error('Error fetching news:', error);
        toast.error('Erreur lors du chargement des actualités');
      }
    }
  );

  // Handle search
  const handleSearch = useCallback((value) => {
    setSearchTerm(value);
    setCurrentPage(1);
  }, []);

  // Handle filters
  const handleSortChange = useCallback((field) => {
    if (sortBy === field) {
      setSortOrder(sortOrder === 'asc' ? 'desc' : 'asc');
    } else {
      setSortBy(field);
      setSortOrder('desc');
    }
    setCurrentPage(1);
  }, [sortBy, sortOrder]);

  const handleCategoryChange = useCallback((value) => {
    setCategory(value);
    setCurrentPage(1);
  }, []);

  // Reset filters
  const resetFilters = useCallback(() => {
    setSearchTerm('');
    setSortBy('date');
    setSortOrder('desc');
    setCategory('');
    setCurrentPage(1);
  }, []);

  // Pagination
  const goToPage = useCallback((page) => {
    setCurrentPage(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }, []);

  if (isLoading && !newsData) {
    return <Loading size="large" message="Chargement des actualités..." />;
  }

  if (error) {
    return (
      <ErrorMessage
        title="Erreur de chargement"
        message="Impossible de charger les actualités. Veuillez réessayer."
        onRetry={refetch}
        variant="error"
      />
    );
  }

  // Safely extract data with proper defaults
  const news = Array.isArray(newsData?.data) ? newsData.data : [];
  const pagination = newsData?.meta || {};
  const categories = newsData?.categories || [];

  // Debug logging
  console.log('NewsPage Debug:', {
    newsData,
    newsDataType: typeof newsData,
    newsDataData: newsData?.data,
    newsDataDataType: typeof newsData?.data,
    isArray: Array.isArray(newsData?.data),
    news,
    newsLength: news?.length
  });

  return (
    <div className="container mx-auto px-4 py-8">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-4">
          Actualités
        </h1>
        <p className="text-lg text-gray-600">
          Découvrez les dernières nouvelles et annonces
        </p>
      </div>

      {/* Filters */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          {/* Search */}
          <div className="lg:col-span-2">
            <SearchInput
              value={searchTerm}
              onChange={handleSearch}
              placeholder="Rechercher dans les actualités..."
              className="w-full"
              aria-label="Rechercher des actualités"
            />
          </div>

          {/* Category filter */}
          <div>
            <Select
              value={category}
              onChange={handleCategoryChange}
              options={[
                { value: '', label: 'Toutes les catégories' },
                ...categories.map(cat => ({
                  value: cat.slug,
                  label: cat.name
                }))
              ]}
              placeholder="Catégorie"
              aria-label="Filtrer par catégorie"
            />
          </div>

          {/* Reset filters */}
          <div>
            <Button
              onClick={resetFilters}
              variant="outline"
              className="w-full"
              aria-label="Réinitialiser les filtres"
            >
              Réinitialiser
            </Button>
          </div>
        </div>

        {/* Sort options */}
        <div className="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-200">
          <span className="text-sm font-medium text-gray-700 mr-2">Trier par:</span>
          <button
            onClick={() => handleSortChange('date')}
            className={`text-sm px-3 py-1 rounded-full border transition-colors ${
              sortBy === 'date'
                ? 'bg-blue-100 text-blue-800 border-blue-300'
                : 'bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200'
            }`}
            aria-label={`Trier par date ${sortBy === 'date' ? (sortOrder === 'asc' ? 'croissant' : 'décroissant') : ''}`}
          >
            Date {sortBy === 'date' && (sortOrder === 'asc' ? '↑' : '↓')}
          </button>
          <button
            onClick={() => handleSortChange('title')}
            className={`text-sm px-3 py-1 rounded-full border transition-colors ${
              sortBy === 'title'
                ? 'bg-blue-100 text-blue-800 border-blue-300'
                : 'bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200'
            }`}
            aria-label={`Trier par titre ${sortBy === 'title' ? (sortOrder === 'asc' ? 'croissant' : 'décroissant') : ''}`}
          >
            Titre {sortBy === 'title' && (sortOrder === 'asc' ? '↑' : '↓')}
          </button>
        </div>
      </div>

      {/* Results */}
      {!Array.isArray(news) || news.length === 0 ? (
        <div className="text-center py-12">
          <div className="text-gray-500 mb-4">
            <svg className="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3v6m0 0v6m0-6h6m-6 0H9" />
            </svg>
          </div>
          <h3 className="text-lg font-medium text-gray-900 mb-2">
            {!Array.isArray(news) ? 'Erreur de chargement des données' : 'Aucune actualité trouvée'}
          </h3>
          <p className="text-gray-500">
            {!Array.isArray(news)
              ? 'Les données reçues ne sont pas dans le format attendu. Veuillez rafraîchir la page.'
              : (searchTerm || category
                ? 'Essayez de modifier vos critères de recherche.'
                : 'Aucune actualité n\'est disponible pour le moment.')
            }
          </p>
        </div>
      ) : (
        <>
          {/* News grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            {news.map((article) => (
              <NewsCard key={article.id} article={article} />
            ))}
          </div>

          {/* Pagination */}
          {pagination.last_page > 1 && (
            <div className="flex justify-center">
              <Pagination
                currentPage={currentPage}
                totalPages={pagination.last_page}
                onPageChange={goToPage}
              />
            </div>
          )}
        </>
      )}

      {/* Loading overlay */}
      {isLoading && newsData && (
        <div className="fixed inset-0 bg-black bg-opacity-25 flex items-center justify-center z-50">
          <Loading size="large" />
        </div>
      )}
    </div>
  );
};

// News Card Component
const NewsCard = ({ article }) => {
  return (
    <Link
      to={`/news/${article.id}`}
      className="block bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group"
      aria-label={`Lire l'article: ${article.title}`}
    >
      {/* Image */}
      {article.image && (
        <div className="aspect-w-16 aspect-h-9 bg-gray-200">
          <img
            src={article.image}
            alt=""
            className="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300"
            loading="lazy"
          />
        </div>
      )}

      {/* Content */}
      <div className="p-6">
        {/* Category */}
        {article.category && (
          <span className="inline-block px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full mb-2">
            {article.category.name}
          </span>
        )}

        {/* Title */}
        <h3 className="text-xl font-semibold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
          {article.title}
        </h3>

        {/* Excerpt */}
        {article.excerpt && (
          <p className="text-gray-600 mb-4 line-clamp-3">
            {article.excerpt}
          </p>
        )}

        {/* Meta */}
        <div className="flex items-center justify-between text-sm text-gray-500">
          <time dateTime={article.published_at}>
            {formatDate(article.published_at)}
          </time>
          {article.read_time && (
            <span>{article.read_time} min de lecture</span>
          )}
        </div>
      </div>
    </Link>
  );
};

// Pagination Component
const Pagination = ({ currentPage, totalPages, onPageChange }) => {
  const getVisiblePages = () => {
    const delta = 2;
    const range = [];
    const rangeWithDots = [];

    for (let i = Math.max(2, currentPage - delta); i <= Math.min(totalPages - 1, currentPage + delta); i++) {
      range.push(i);
    }

    if (currentPage - delta > 2) {
      rangeWithDots.push(1, '...');
    } else {
      rangeWithDots.push(1);
    }

    rangeWithDots.push(...range);

    if (currentPage + delta < totalPages - 1) {
      rangeWithDots.push('...', totalPages);
    } else {
      rangeWithDots.push(totalPages);
    }

    return rangeWithDots;
  };

  const visiblePages = getVisiblePages();

  return (
    <nav className="flex items-center space-x-1" aria-label="Pagination">
      {/* Previous */}
      <button
        onClick={() => onPageChange(currentPage - 1)}
        disabled={currentPage === 1}
        className="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        aria-label="Page précédente"
      >
        Précédent
      </button>

      {/* Page numbers */}
      {visiblePages.map((page, index) => (
        <React.Fragment key={index}>
          {page === '...' ? (
            <span className="px-3 py-2 text-sm font-medium text-gray-500">...</span>
          ) : (
            <button
              onClick={() => onPageChange(page)}
              className={`px-3 py-2 text-sm font-medium rounded-md ${
                currentPage === page
                  ? 'text-blue-600 bg-blue-50 border border-blue-300'
                  : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50'
              }`}
              aria-label={`Page ${page}`}
              aria-current={currentPage === page ? 'page' : undefined}
            >
              {page}
            </button>
          )}
        </React.Fragment>
      ))}

      {/* Next */}
      <button
        onClick={() => onPageChange(currentPage + 1)}
        disabled={currentPage === totalPages}
        className="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        aria-label="Page suivante"
      >
        Suivant
      </button>
    </nav>
  );
};

export default NewsPage;
