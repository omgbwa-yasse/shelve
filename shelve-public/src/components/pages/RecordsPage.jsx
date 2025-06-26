import React, { useState, useEffect, useCallback, useMemo, useRef } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { recordsApi } from '../../services/shelveApi';
import { useApi } from '../../hooks/useApi';
import { useDebounce } from '../../hooks/useDebounce';
import Loading from '../common/Loading';
import ErrorMessage from '../common/ErrorMessage';
import { formatDate } from '../../utils/dateUtils';
import { truncateText } from '../../utils/helpers';
import { RECORD_TYPES } from '../../utils/constants';
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
  ContentList,
  ContentCard,
  ListItem,
  ListItemHeader,
  ListItemTitle,
  ListItemDescription,
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
  ViewToggleButton,
  StyledLink
} from '../common/PageComponents';

const RecordsPage = () => {
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();
  const [filters, setFilters] = useState({
    search: searchParams.get('search') || '',
    type: searchParams.get('type') || '',
    status: searchParams.get('status') || 'published',
    date_from: searchParams.get('date_from') || '',
    date_to: searchParams.get('date_to') || '',
    classification: searchParams.get('classification') || '',
    sort_by: searchParams.get('sort_by') || 'published_at',
    sort_order: searchParams.get('sort_order') || 'desc'
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

  // Créer une clé stable pour les dépendances API
  const apiKey = useMemo(() =>
    `${JSON.stringify(apiFilters)}-${currentPage}`,
    [apiFilters, currentPage]
  );

  // Reset de la page quand les filtres changent (sauf la première fois)
  const isFirstRender = useRef(true);
  const prevApiFiltersKey = useRef(JSON.stringify(apiFilters));

  useEffect(() => {
    const currentKey = JSON.stringify(apiFilters);

    if (!isFirstRender.current &&
        prevApiFiltersKey.current !== currentKey &&
        currentPage > 1) {
      setCurrentPage(1);
    }

    prevApiFiltersKey.current = currentKey;
    isFirstRender.current = false;
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [apiFilters]); // currentPage intentionally omitted to avoid loop

  const {
    data: recordsData,
    loading,
    error,
    refetch
  } = useApi(
    () => recordsApi.getRecords({
      ...apiFilters,
      page: currentPage,
      per_page: 10 // Ajout du paramètre per_page
    }),
    null,
    { dependencies: [apiKey] }
  );

  const pagination = recordsData?.pagination || {};

  // Transformer les données de l'API pour correspondre à l'interface attendue
  const records = useMemo(() => {
    const rawRecords = recordsData?.data || [];
    return rawRecords.map(record => ({
      id: record.id,
      title: record.title,
      description: record.content, // L'API retourne 'content' au lieu de 'description'
      reference: record.code, // L'API retourne 'code' au lieu de 'reference_number'
      date: record.published_at,
      created_at: record.published_at,
      published_at: record.published_at,
      type: 'document', // Par défaut, peut être enrichi plus tard
      location: record.publisher?.name,
      digital_copy_available: record.is_available, // Utiliser le statut de disponibilité de l'API
      thumbnail_url: null, // À implémenter si nécessaire
      formatted_date_range: record.formatted_date_range, // Nouveau champ de l'API
      is_available: record.is_available,
      is_expired: record.is_expired,
      publication_notes: record.publication_notes,
      // Conserver les données originales pour des besoins futurs
      _original: record
    }));
  }, [recordsData?.data]);

  // Mise à jour des paramètres URL
  useEffect(() => {
    const params = new URLSearchParams();
    Object.entries(apiFilters).forEach(([key, value]) => {
      if (value) params.set(key, value);
    });
    if (currentPage > 1) params.set('page', currentPage);
    setSearchParams(params);
  }, [apiFilters, currentPage, setSearchParams]);

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
      classification: '',
      sort_by: 'published_at',
      sort_order: 'desc'
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

  if (loading && !records.length) return <Loading />;
  if (error) return <ErrorMessage message={error} onRetry={refetch} />;

  return (
    <PageContainer>
      <PageHeader>
        <PageTitle>Archives et Documents</PageTitle>
        <PageSubtitle>
          Découvrez notre collection d'archives et de documents historiques.
          Les documents les plus récemment publiés sont affichés en premier.
        </PageSubtitle>
        <div style={{
          marginTop: '1rem',
          padding: '1rem',
          backgroundColor: '#eff6ff',
          borderLeft: '4px solid #3b82f6',
          borderRadius: '0 0.5rem 0.5rem 0'
        }}>
          <p style={{ fontSize: '0.875rem', color: '#1e40af' }}>
            💡 <strong>Astuce :</strong> Utilisez Ctrl+K pour rechercher rapidement dans les archives.
            Les documents sont triés par date de publication pour vous montrer les dernières additions.
          </p>
        </div>
      </PageHeader>

      {/* Filtres */}
      <FiltersSection>
        <FiltersGrid>
          <FilterGroup className="md:col-span-3 lg:col-span-2">
            <FilterLabel htmlFor="search">
              Recherche dans les archives
              {loading && debouncedSearch !== filters.search && (
                <span style={{ marginLeft: '0.5rem', fontSize: '0.75rem', color: '#2563eb' }}>
                  Recherche...
                </span>
              )}
              <kbd style={{
                marginLeft: '0.5rem',
                display: 'inline-flex',
                alignItems: 'center',
                padding: '0.25rem 0.5rem',
                fontSize: '0.75rem',
                fontFamily: 'monospace',
                color: '#6b7280',
                backgroundColor: '#f3f4f6',
                border: '1px solid #d1d5db',
                borderRadius: '0.25rem'
              }}>
                Ctrl+K
              </kbd>
            </FilterLabel>
            <div style={{ position: 'relative' }}>
              <FilterInput
                type="text"
                id="search"
                value={filters.search}
                onChange={(e) => handleFilterChange('search', e.target.value)}
                placeholder="Titre, description, référence..."
                style={{ paddingLeft: '2.5rem' }}
              />
              <div style={{
                position: 'absolute',
                inset: '0 auto 0 0',
                paddingLeft: '0.75rem',
                display: 'flex',
                alignItems: 'center',
                pointerEvents: 'none'
              }}>
                <svg style={{ height: '1.25rem', width: '1.25rem', color: '#9ca3af' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
            </div>
          </FilterGroup>

          <FilterGroup>
            <FilterLabel htmlFor="type">Type de document</FilterLabel>
            <FilterSelect
              id="type"
              value={filters.type}
              onChange={(e) => handleFilterChange('type', e.target.value)}
            >
              <option value="">Tous les types</option>
              {RECORD_TYPES.map(type => (
                <option key={type.value} value={type.value}>
                  {type.label}
                </option>
              ))}
            </FilterSelect>
          </FilterGroup>

          <FilterGroup>
            <FilterLabel htmlFor="classification">Classification</FilterLabel>
            <FilterInput
              type="text"
              id="classification"
              value={filters.classification}
              onChange={(e) => handleFilterChange('classification', e.target.value)}
              placeholder="Cote, série..."
            />
          </FilterGroup>

          <FilterGroup>
            <FilterLabel htmlFor="date_from">Date de début</FilterLabel>
            <FilterInput
              type="date"
              id="date_from"
              value={filters.date_from}
              onChange={(e) => handleFilterChange('date_from', e.target.value)}
            />
          </FilterGroup>

          <FilterGroup>
            <FilterLabel htmlFor="date_to">Date de fin</FilterLabel>
            <FilterInput
              type="date"
              id="date_to"
              value={filters.date_to}
              onChange={(e) => handleFilterChange('date_to', e.target.value)}
            />
          </FilterGroup>

          <FilterGroup>
            <FilterLabel htmlFor="sort_by">Trier par</FilterLabel>
            <FilterSelect
              id="sort_by"
              value={filters.sort_by}
              onChange={(e) => handleFilterChange('sort_by', e.target.value)}
            >
              <option value="published_at">Date de publication</option>
              <option value="created_at">Date de création</option>
              <option value="name">Titre</option>
              <option value="code">Référence</option>
            </FilterSelect>
          </FilterGroup>

          <FilterGroup>
            <FilterLabel htmlFor="sort_order">Ordre</FilterLabel>
            <FilterSelect
              id="sort_order"
              value={filters.sort_order}
              onChange={(e) => handleFilterChange('sort_order', e.target.value)}
            >
              <option value="desc">Plus récent d'abord</option>
              <option value="asc">Plus ancien d'abord</option>
            </FilterSelect>
          </FilterGroup>
        </FiltersGrid>

        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: '1rem' }}>
          <div style={{ fontSize: '0.875rem', color: '#6b7280' }}>
            {loading && debouncedSearch !== filters.search ? (
              <span style={{ display: 'flex', alignItems: 'center' }}>
                <svg style={{ animation: 'spin 1s linear infinite', marginLeft: '-0.25rem', marginRight: '0.5rem', height: '1rem', width: '1rem', color: '#2563eb' }} fill="none" viewBox="0 0 24 24">
                  <circle style={{ opacity: 0.25 }} cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path style={{ opacity: 0.75 }} fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Recherche en cours...
              </span>
            ) : (
              <span>Recherche automatique activée</span>
            )}
          </div>

          <FilterButton
            type="button"
            onClick={resetFilters}
            variant="secondary"
          >
            🔄 Réinitialiser
          </FilterButton>
        </div>
      </FiltersSection>

      {/* Barre d'outils */}
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1.5rem' }}>
        <div>
          <p style={{ color: '#6b7280' }}>
            {pagination.total || 0} document(s) trouvé(s)
            {filters.search && ` pour "${filters.search}"`}
            {loading && (
              <span style={{ marginLeft: '0.5rem', display: 'inline-flex', alignItems: 'center' }}>
                <svg style={{ animation: 'spin 1s linear infinite', height: '1rem', width: '1rem', color: '#2563eb' }} fill="none" viewBox="0 0 24 24">
                  <circle style={{ opacity: 0.25 }} cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path style={{ opacity: 0.75 }} fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
            )}
          </p>
        </div>

        <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
          <span style={{ fontSize: '0.875rem', color: '#6b7280' }}>Affichage :</span>
          <ViewToggleButton
            onClick={() => setViewMode('grid')}
            active={viewMode === 'grid'}
            aria-label="Vue en grille"
          >
            ⊞
          </ViewToggleButton>
          <ViewToggleButton
            onClick={() => setViewMode('list')}
            active={viewMode === 'list'}
            aria-label="Vue en liste"
          >
            ☰
          </ViewToggleButton>
        </div>
      </div>

      {/* Résultats */}
      {records.length > 0 ? (
        <>
          {viewMode === 'grid' ? (
            <ContentGrid>
              {records.map(record => (
                <StyledLink key={record.id} to={`/records/${record.id}`}>
                  <ContentCard onClick={() => handleRecordClick(record.id)}>
                    <CardContent>
                      <CardTitle>{record.title}</CardTitle>
                      <CardDescription>
                        {truncateText(record.description, 150)}
                      </CardDescription>
                      <CardMeta>
                        <CardDate>
                          {record.formatted_date_range || formatDate(record.date)}
                        </CardDate>
                        {record.reference && <CardTag>{record.reference}</CardTag>}
                        {record.location && <span>📍 {record.location}</span>}
                        {record.is_available === false && (
                          <span style={{ color: '#dc2626', fontSize: '0.75rem' }}>
                            ⚠️ Non disponible
                          </span>
                        )}
                      </CardMeta>
                    </CardContent>
                  </ContentCard>
                </StyledLink>
              ))}
            </ContentGrid>
          ) : (
            <ContentList>
              {records.map(record => (
                <StyledLink key={record.id} to={`/records/${record.id}`}>
                  <ListItem onClick={() => handleRecordClick(record.id)}>
                    <ListItemHeader>
                      <ListItemTitle>{record.title}</ListItemTitle>
                      <div style={{ display: 'flex', gap: '0.5rem', fontSize: '0.875rem', color: '#6b7280' }}>
                        <span>{record.formatted_date_range || formatDate(record.date)}</span>
                        {record.reference && <span>Réf: {record.reference}</span>}
                        {record.is_available === false && (
                          <span style={{ color: '#dc2626' }}>⚠️ Non disponible</span>
                        )}
                      </div>
                    </ListItemHeader>
                    <ListItemDescription>
                      {truncateText(record.description, 200)}
                      {record.location && (
                        <span style={{ marginLeft: '1rem', color: '#6b7280' }}>
                          📍 {record.location}
                        </span>
                      )}
                    </ListItemDescription>
                  </ListItem>
                </StyledLink>
              ))}
            </ContentList>
          )}

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
      ) : (
        <EmptyState>
          <EmptyStateIcon>📄</EmptyStateIcon>
          <EmptyStateTitle>Aucun document trouvé</EmptyStateTitle>
          <EmptyStateMessage>
            Aucun document ne correspond à vos critères de recherche.
            Essayez de modifier vos filtres.
          </EmptyStateMessage>
        </EmptyState>
      )}
    </PageContainer>
  );
};

export default RecordsPage;
