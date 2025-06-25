import React, { useState, useCallback } from 'react';
import { useQuery } from 'react-query';
import { toast } from 'react-toastify';
import shelveApi from '../../services/shelveApi';
import { formatDate } from '../../utils/dateUtils';
import { PAGINATION_DEFAULTS } from '../../utils/constants';
import Loading from '../common/Loading';
import ErrorMessage from '../common/ErrorMessage';
import {
  PageContainer,
  PageHeader,
  PageTitle,
  PageSubtitle,
  FiltersSection,
  FiltersGrid,
  FilterGroup,
  FilterLabel,
  FilterInput,
  FilterSelect,
  FilterButton,
  ContentGrid,
  ContentCard,
  CardContent,
  CardTitle,
  CardDescription,
  CardMeta,
  CardTag,
  CardDate,
  PaginationContainer,
  PaginationButton,
  EmptyState,
  EmptyStateIcon,
  EmptyStateTitle,
  EmptyStateMessage,
  StyledLink
} from '../common/PageComponents';

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
    <PageContainer>
      <PageHeader>
        <PageTitle>Actualités</PageTitle>
        <PageSubtitle>
          Découvrez les dernières nouvelles et annonces
        </PageSubtitle>
      </PageHeader>

      {/* Filtres */}
      <FiltersSection>
        <FiltersGrid>
          {/* Search */}
          <FilterGroup className="lg:col-span-2">
            <FilterLabel htmlFor="search">Recherche</FilterLabel>
            <FilterInput
              type="text"
              id="search"
              value={searchTerm}
              onChange={(e) => handleSearch(e.target.value)}
              placeholder="Rechercher dans les actualités..."
            />
          </FilterGroup>

          {/* Category filter */}
          <FilterGroup>
            <FilterLabel htmlFor="category">Catégorie</FilterLabel>
            <FilterSelect
              id="category"
              value={category}
              onChange={(e) => handleCategoryChange(e.target.value)}
            >
              <option value="">Toutes les catégories</option>
              {categories.map(cat => (
                <option key={cat.slug} value={cat.slug}>
                  {cat.name}
                </option>
              ))}
            </FilterSelect>
          </FilterGroup>

          {/* Reset filters */}
          <FilterGroup>
            <FilterButton onClick={resetFilters} type="button">
              Réinitialiser
            </FilterButton>
          </FilterGroup>
        </FiltersGrid>

        {/* Sort options */}
        <div style={{ marginTop: '1rem', paddingTop: '1rem', borderTop: '1px solid #e5e7eb' }}>
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.5rem', alignItems: 'center' }}>
            <span style={{ fontSize: '0.875rem', fontWeight: '500', color: '#374151', marginRight: '0.5rem' }}>
              Trier par:
            </span>
            <FilterButton
              onClick={() => handleSortChange('date')}
              variant={sortBy === 'date' ? 'primary' : 'secondary'}
              size="small"
            >
              Date {sortBy === 'date' && (sortOrder === 'asc' ? '↑' : '↓')}
            </FilterButton>
            <FilterButton
              onClick={() => handleSortChange('title')}
              variant={sortBy === 'title' ? 'primary' : 'secondary'}
              size="small"
            >
              Titre {sortBy === 'title' && (sortOrder === 'asc' ? '↑' : '↓')}
            </FilterButton>
          </div>
        </div>
      </FiltersSection>

      {/* Results */}
      {!Array.isArray(news) || news.length === 0 ? (
        <EmptyState>
          <EmptyStateIcon>📰</EmptyStateIcon>
          <EmptyStateTitle>
            {!Array.isArray(news) ? 'Erreur de chargement des données' : 'Aucune actualité trouvée'}
          </EmptyStateTitle>
          <EmptyStateMessage>
            {!Array.isArray(news)
              ? 'Les données reçues ne sont pas dans le format attendu. Veuillez rafraîchir la page.'
              : (searchTerm || category
                ? 'Essayez de modifier vos critères de recherche.'
                : 'Aucune actualité n\'est disponible pour le moment.')
            }
          </EmptyStateMessage>
        </EmptyState>
      ) : (
        <>
          {/* News grid */}
          <ContentGrid>
            {news.map((article) => (
              <StyledLink key={article.id} to={`/news/${article.id}`}>
                <ContentCard>
                  {/* Image */}
                  {article.image && (
                    <div style={{
                      height: '12rem',
                      overflow: 'hidden',
                      backgroundColor: '#f3f4f6'
                    }}>
                      <img
                        src={article.image}
                        alt=""
                        style={{
                          width: '100%',
                          height: '100%',
                          objectFit: 'cover',
                          transition: 'transform 300ms ease'
                        }}
                        loading="lazy"
                        onMouseEnter={(e) => e.target.style.transform = 'scale(1.05)'}
                        onMouseLeave={(e) => e.target.style.transform = 'scale(1)'}
                      />
                    </div>
                  )}

                  <CardContent>
                    {/* Category */}
                    {article.category && (
                      <CardTag style={{ backgroundColor: '#dbeafe', color: '#1e40af' }}>
                        {article.category.name}
                      </CardTag>
                    )}

                    {/* Title */}
                    <CardTitle>{article.title}</CardTitle>

                    {/* Excerpt */}
                    {article.excerpt && (
                      <CardDescription>{article.excerpt}</CardDescription>
                    )}

                    {/* Meta */}
                    <CardMeta>
                      <CardDate>{formatDate(article.published_at)}</CardDate>
                      {article.read_time && (
                        <span>{article.read_time} min de lecture</span>
                      )}
                    </CardMeta>
                  </CardContent>
                </ContentCard>
              </StyledLink>
            ))}
          </ContentGrid>

          {/* Pagination */}
          {pagination.last_page > 1 && (
            <PaginationContainer>
              <PaginationButton
                onClick={() => goToPage(currentPage - 1)}
                disabled={currentPage === 1}
              >
                Précédent
              </PaginationButton>

              {Array.from({ length: pagination.last_page }, (_, i) => i + 1)
                .filter(page =>
                  page === 1 ||
                  page === pagination.last_page ||
                  Math.abs(page - currentPage) <= 2
                )
                .map(page => (
                  <PaginationButton
                    key={page}
                    current={page === currentPage}
                    onClick={() => goToPage(page)}
                  >
                    {page}
                  </PaginationButton>
                ))}

              <PaginationButton
                onClick={() => goToPage(currentPage + 1)}
                disabled={currentPage === pagination.last_page}
              >
                Suivant
              </PaginationButton>
            </PaginationContainer>
          )}
        </>
      )}

      {/* Loading overlay */}
      {isLoading && newsData && (
        <div style={{
          position: 'fixed',
          inset: 0,
          backgroundColor: 'rgba(0, 0, 0, 0.25)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          zIndex: 50
        }}>
          <Loading size="large" />
        </div>
      )}
    </PageContainer>
  );
};

export default NewsPage;
