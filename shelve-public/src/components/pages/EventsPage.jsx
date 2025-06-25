import React, { useState, useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { eventsApi } from '../../services/shelveApi';
import { useApi } from '../../hooks/useApi';
import Loading from '../common/Loading';
import ErrorMessage from '../common/ErrorMessage';
import { formatDate } from '../../utils/dateUtils';
import { truncateText } from '../../utils/helpers';
import { EVENT_TYPES } from '../../utils/constants';
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
    <PageContainer>
      <PageHeader>
        <PageTitle>Événements et Actualités</PageTitle>
        <PageSubtitle>
          Découvrez les derniers événements et actualités de nos archives
        </PageSubtitle>
      </PageHeader>

      {/* Filtres */}
      <FiltersSection>
        <form onSubmit={handleSearch}>
          <FiltersGrid>
            <FilterGroup>
              <FilterLabel htmlFor="search">Recherche</FilterLabel>
              <FilterInput
                type="text"
                id="search"
                value={filters.search}
                onChange={(e) => handleFilterChange('search', e.target.value)}
                placeholder="Mots-clés..."
              />
            </FilterGroup>

            <FilterGroup>
              <FilterLabel htmlFor="type">Type</FilterLabel>
              <FilterSelect
                id="type"
                value={filters.type}
                onChange={(e) => handleFilterChange('type', e.target.value)}
              >
                <option value="">Tous les types</option>
                {EVENT_TYPES.map(type => (
                  <option key={type.value} value={type.value}>
                    {type.label}
                  </option>
                ))}
              </FilterSelect>
            </FilterGroup>

            <FilterGroup>
              <FilterLabel htmlFor="date_from">Date début</FilterLabel>
              <FilterInput
                type="date"
                id="date_from"
                value={filters.date_from}
                onChange={(e) => handleFilterChange('date_from', e.target.value)}
              />
            </FilterGroup>

            <FilterGroup>
              <FilterLabel htmlFor="date_to">Date fin</FilterLabel>
              <FilterInput
                type="date"
                id="date_to"
                value={filters.date_to}
                onChange={(e) => handleFilterChange('date_to', e.target.value)}
              />
            </FilterGroup>
          </FiltersGrid>

          <FilterButton type="submit">
            Rechercher
          </FilterButton>
        </form>
      </FiltersSection>

      {/* Résultats */}
      {events.length === 0 ? (
        <EmptyState>
          <EmptyStateIcon>📅</EmptyStateIcon>
          <EmptyStateTitle>Aucun événement trouvé</EmptyStateTitle>
          <EmptyStateMessage>
            Aucun événement ne correspond à vos critères de recherche.
            Essayez de modifier vos filtres.
          </EmptyStateMessage>
        </EmptyState>
      ) : (
        <>
          <ContentGrid>
            {events.map(event => (
              <StyledLink key={event.id} to={`/events/${event.id}`}>
                <ContentCard onClick={() => handleEventClick(event.id)}>
                  <CardContent>
                    <CardTitle>{event.title}</CardTitle>
                    <CardDescription>
                      {truncateText(event.description, 150)}
                    </CardDescription>
                    <CardMeta>
                      <CardDate>{formatDate(event.event_date)}</CardDate>
                      {event.type && <CardTag>{event.type}</CardTag>}
                      {event.location && <span>📍 {event.location}</span>}
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
                onClick={() => handlePageChange(currentPage - 1)}
                disabled={currentPage === 1}
              >
                ← Précédent
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
                    onClick={() => handlePageChange(page)}
                  >
                    {page}
                  </PaginationButton>
                ))}

              <PaginationButton
                onClick={() => handlePageChange(currentPage + 1)}
                disabled={currentPage === pagination.last_page}
              >
                Suivant →
              </PaginationButton>
            </PaginationContainer>
          )}
        </>
      )}
    </PageContainer>
  );
};

export default EventsPage;
