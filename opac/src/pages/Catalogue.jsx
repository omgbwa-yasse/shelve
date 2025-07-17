import React, { useState, useEffect } from 'react';
import { toast } from 'react-toastify';
import {
  FaFilter,
  FaSort,
  FaEye,
  FaDownload,
  FaExternalLinkAlt,
  FaArrowLeft,
  FaArrowRight
} from 'react-icons/fa';
import { recordsApi } from '../services/AllServices';
import {
  CatalogueContainer,
  CatalogueHeader,
  CatalogueControls,
  SortControls,
  SortButton,
  FilterButton,
  FiltersContainer,
  ResultsInfo,
  CatalogueGrid,
  CatalogueCard,
  ActionButton,
  Pagination,
  PaginationButton,
  CategoryBadge,
  LoadingSpinner
} from '../styles/CatalogueStyles';

const CataloguePage = () => {
  const [catalogueRecords, setCatalogueRecords] = useState([]);
  const [catalogueLoading, setCatalogueLoading] = useState(false);
  const [sortBy, setSortBy] = useState('date');
  const [sortOrder, setSortOrder] = useState('desc');
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalRecords, setTotalRecords] = useState(0);
  const [activeFilter, setActiveFilter] = useState('all');
  const recordsPerPage = 12;

  useEffect(() => {
    loadCatalogueData();
  }, [currentPage, sortBy, sortOrder, activeFilter]);

  const loadCatalogueData = async () => {
    setCatalogueLoading(true);
    try {
      const response = await recordsApi.getRecords({
        page: currentPage,
        limit: recordsPerPage,
        sort: `${sortBy}:${sortOrder}`,
        type: activeFilter !== 'all' ? activeFilter : undefined
      });

      setCatalogueRecords(response.data.data || response.data || []);
      setTotalRecords(response.data.total || response.data.length || 0);
      setTotalPages(Math.ceil((response.data.total || response.data.length || 0) / recordsPerPage));
    } catch (error) {
      console.error('Error loading catalogue data:', error);
      toast.error('Erreur lors du chargement du catalogue');

      // Données de démonstration
      const mockData = [
        { id: 1, title: 'Registre paroissial de Saint-Pierre (1850-1860)', date: '1850-01-01', category: 'Documents religieux', views: 234, description: 'Registre complet des baptêmes, mariages et sépultures de la paroisse Saint-Pierre pour la décennie 1850-1860.' },
        { id: 2, title: 'Correspondance du maire Dupont (1920-1925)', date: '1920-05-15', category: 'Correspondance', views: 156, description: 'Échanges épistolaires du maire concernant la reconstruction de la ville après la Grande Guerre.' },
        { id: 3, title: 'Plan cadastral napoléonien (1848)', date: '1848-12-03', category: 'Cartes', views: 189, description: 'Plan détaillé du territoire communal avant les grandes transformations industrielles du XIXe siècle.' },
        { id: 4, title: 'Actes notariés - Étude Moreau (1785-1790)', date: '1785-06-20', category: 'Actes juridiques', views: 98, description: 'Collection d\'actes notariés de l\'étude Moreau, témoignant de la vie économique pré-révolutionnaire.' },
        { id: 5, title: 'Registre des délibérations municipales (1900-1910)', date: '1900-01-01', category: 'Administration', views: 145, description: 'Procès-verbaux des séances du conseil municipal au début du XXe siècle.' },
        { id: 6, title: 'Photographies de la rue principale (1920-1930)', date: '1920-01-01', category: 'Photographies', views: 267, description: 'Collection de photographies montrant l\'évolution de la rue principale durant les années 1920.' },
        { id: 7, title: 'Livre de raison de la famille Dubois (1840-1880)', date: '1840-03-12', category: 'Documents personnels', views: 112, description: 'Chronique familiale détaillant la vie quotidienne d\'une famille bourgeoise du XIXe siècle.' },
        { id: 8, title: 'Cadastre parcellaire de 1852', date: '1852-07-25', category: 'Cartes', views: 203, description: 'Relevé parcellaire complet du territoire communal avec indication des propriétaires.' },
        { id: 9, title: 'Registres de la garde nationale (1870-1871)', date: '1870-09-04', category: 'Militaire', views: 178, description: 'Listes des membres de la garde nationale pendant la guerre franco-prussienne.' },
        { id: 10, title: 'Comptes de fabrique (1820-1850)', date: '1820-01-01', category: 'Économie', views: 89, description: 'Registres comptables de la fabrique paroissiale sur trois décennies.' },
        { id: 11, title: 'Plans d\'alignement des rues (1885)', date: '1885-11-15', category: 'Urbanisme', views: 134, description: 'Projets d\'aménagement urbain pour la modernisation de la ville.' },
        { id: 12, title: 'Correspondance préfectorale (1900-1905)', date: '1900-02-28', category: 'Administration', views: 156, description: 'Échanges entre la mairie et la préfecture concernant l\'administration locale.' }
      ];
      setCatalogueRecords(mockData);
      setTotalRecords(mockData.length);
      setTotalPages(Math.ceil(mockData.length / recordsPerPage));
    } finally {
      setCatalogueLoading(false);
    }
  };

  const handleSort = (field) => {
    if (sortBy === field) {
      setSortOrder(sortOrder === 'asc' ? 'desc' : 'asc');
    } else {
      setSortBy(field);
      setSortOrder('desc');
    }
    setCurrentPage(1);
  };

  const handlePageChange = (page) => {
    setCurrentPage(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleFilterChange = (filter) => {
    setActiveFilter(filter);
    setCurrentPage(1);
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  return (
    <CatalogueContainer>
      <CatalogueHeader>
        <h1>Catalogue des Archives</h1>
        <p>Explorez notre collection complète de documents historiques, organisés et numérisés pour faciliter vos recherches.</p>
      </CatalogueHeader>

      <CatalogueControls>
        <SortControls>
          <span>Trier par :</span>
          <SortButton
            active={sortBy === 'date'}
            onClick={() => handleSort('date')}
          >
            <FaSort />
            Date {sortBy === 'date' && (sortOrder === 'asc' ? '↑' : '↓')}
          </SortButton>
          <SortButton
            active={sortBy === 'title'}
            onClick={() => handleSort('title')}
          >
            <FaSort />
            Titre {sortBy === 'title' && (sortOrder === 'asc' ? '↑' : '↓')}
          </SortButton>
          <SortButton
            active={sortBy === 'views'}
            onClick={() => handleSort('views')}
          >
            <FaSort />
            Popularité {sortBy === 'views' && (sortOrder === 'asc' ? '↑' : '↓')}
          </SortButton>
        </SortControls>

        <FiltersContainer>
          <FilterButton
            active={activeFilter === 'all'}
            onClick={() => handleFilterChange('all')}
          >
            <FaFilter />
            Tout ({totalRecords})
          </FilterButton>
          <FilterButton
            active={activeFilter === 'documents'}
            onClick={() => handleFilterChange('documents')}
          >
            Documents
          </FilterButton>
          <FilterButton
            active={activeFilter === 'photos'}
            onClick={() => handleFilterChange('photos')}
          >
            Photos
          </FilterButton>
          <FilterButton
            active={activeFilter === 'manuscripts'}
            onClick={() => handleFilterChange('manuscripts')}
          >
            Manuscrits
          </FilterButton>
          <FilterButton
            active={activeFilter === 'maps'}
            onClick={() => handleFilterChange('maps')}
          >
            Cartes
          </FilterButton>
        </FiltersContainer>
      </CatalogueControls>

      <ResultsInfo>
        Affichage de {(currentPage - 1) * recordsPerPage + 1} à {Math.min(currentPage * recordsPerPage, totalRecords)} sur {totalRecords} documents
      </ResultsInfo>

      {catalogueLoading ? (
        <LoadingSpinner />
      ) : (
        <CatalogueGrid>
          {catalogueRecords.map((record) => (
            <CatalogueCard key={record.id}>
              <div className="card-header">
                <CategoryBadge>{record.category || record.type || 'Document'}</CategoryBadge>
                <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', fontSize: '0.9rem', color: '#666' }}>
                  <FaEye />
                  {record.views || record.view_count || 0}
                </div>
              </div>

              <div className="card-title">
                {record.title || record.name || 'Document sans titre'}
              </div>

              <div className="card-metadata">
                <span>📅 {formatDate(record.date || record.created_at)}</span>
                <span>📄 {record.pages || 'N/A'} pages</span>
                <span>💾 {record.size || 'N/A'}</span>
              </div>

              <div className="card-description">
                {record.description || record.content || record.excerpt || 'Aucune description disponible'}
              </div>

              <div className="card-actions">
                <ActionButton variant="primary">
                  <FaEye />
                  Consulter
                </ActionButton>
                <ActionButton>
                  <FaDownload />
                  Télécharger
                </ActionButton>
                <ActionButton>
                  <FaExternalLinkAlt />
                  Détails
                </ActionButton>
              </div>
            </CatalogueCard>
          ))}
        </CatalogueGrid>
      )}

      {totalPages > 1 && (
        <Pagination>
          <PaginationButton
            onClick={() => handlePageChange(currentPage - 1)}
            disabled={currentPage === 1}
          >
            <FaArrowLeft />
            Précédent
          </PaginationButton>

          {[...Array(totalPages)].map((_, index) => (
            <PaginationButton
              key={index + 1}
              active={currentPage === index + 1}
              onClick={() => handlePageChange(index + 1)}
            >
              {index + 1}
            </PaginationButton>
          ))}

          <PaginationButton
            onClick={() => handlePageChange(currentPage + 1)}
            disabled={currentPage === totalPages}
          >
            Suivant
            <FaArrowRight />
          </PaginationButton>
        </Pagination>
      )}
    </CatalogueContainer>
  );
};

export default CataloguePage;
