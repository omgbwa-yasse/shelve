import React, { useState, useEffect } from 'react';
import { toast } from 'react-toastify';
import {
  FaSearch,
  FaCalendarAlt,
  FaFileAlt,
  FaUsers,
  FaDownload,
  FaHistory
} from 'react-icons/fa';
import { recordsApi, eventsApi, newsApi, searchApi } from '../services/AllServices';
import {
  SearchSection,
  SearchContainer,
  SearchForm,
  SearchInput,
  SearchButton,
  MainContent,
  ContentArea,
  Sidebar,
  SidebarSection,
  StatsGrid,
  StatCard,
  RecordCard,
  EventCard,
  LoadingSpinner
} from '../styles/HomeStyles';

// Composant HomePage
function HomePage({ theme }) {
  const [searchQuery, setSearchQuery] = useState('');
  const [records, setRecords] = useState([]);
  const [events, setEvents] = useState([]);
  const [news, setNews] = useState([]);
  const [statistics, setStatistics] = useState(null);
  const [loading, setLoading] = useState(false);
  const [popularSearches, setPopularSearches] = useState([]);

  // Load initial data
  useEffect(() => {
    loadInitialData();
  }, []);

  const loadInitialData = async () => {
    setLoading(true);
    try {
      // Charger les données depuis l'API
      const [recordsResponse, eventsResponse, newsResponse] = await Promise.all([
        recordsApi.getRecords({ limit: 10 }),
        eventsApi.getEvents({ limit: 5 }),
        newsApi.getNews({ limit: 3 })
      ]);

      setRecords(recordsResponse.data.data || recordsResponse.data || []);
      setEvents(eventsResponse.data.data || eventsResponse.data || []);
      setNews(newsResponse.data.data || newsResponse.data || []);

      // Statistiques basées sur les données réelles ou valeurs par défaut
      setStatistics({
        total_records: recordsResponse.data.total || recordsResponse.data.length || 0,
        total_users: 1250, // À adapter selon votre API
        total_downloads: 3580 // À adapter selon votre API
      });

      // Recherches populaires - à adapter selon votre API
      setPopularSearches([
        'registres paroissiaux',
        'cadastre napoléonien',
        'correspondance',
        'cartes anciennes',
        'actes notariés'
      ]);
    } catch (error) {
      console.error('Error loading initial data:', error);
      toast.error('Erreur lors du chargement des données. Affichage des données de démonstration.');

      // Fallback en cas d'erreur API
      setRecords([
        { title: 'Registre paroissial de 1850', date: '1850-01-01', category: 'Documents religieux', views: 234, description: 'Registre des baptêmes, mariages et sépultures de la paroisse' },
        { title: 'Correspondance du maire 1920', date: '1920-05-15', category: 'Correspondance', views: 156, description: 'Échanges épistolaires concernant la reconstruction après-guerre' },
        { title: 'Plan cadastral de 1848', date: '1848-12-03', category: 'Cartes', views: 189, description: 'Plan détaillé du territoire communal avant les transformations industrielles' }
      ]);

      setEvents([
        { title: 'Exposition "Mémoires de guerre"', date: '2024-03-15', description: 'Découvrez les témoignages de la Grande Guerre' },
        { title: 'Conférence sur l\'histoire locale', date: '2024-03-22', description: 'Présentation des dernières découvertes archéologiques' },
        { title: 'Atelier de paléographie', date: '2024-04-05', description: 'Apprenez à déchiffrer les écritures anciennes' }
      ]);

      setNews([
        { title: 'Numérisation des registres paroissiaux', excerpt: 'Découvrez les nouveaux documents numérisés disponibles en ligne' },
        { title: 'Nouveaux fonds d\'archives', excerpt: 'La famille Dupont a fait don de précieux documents du XVIIIe siècle' },
        { title: 'Journées du patrimoine', excerpt: 'Venez découvrir les coulisses de nos archives lors des journées portes ouvertes' }
      ]);

      setStatistics({
        total_records: 15420,
        total_users: 1250,
        total_downloads: 3580
      });
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = async (e) => {
    e.preventDefault();
    if (!searchQuery.trim()) return;

    setLoading(true);
    try {
      // Utiliser l'API de recherche
      const searchResponse = await searchApi.search({
        query: searchQuery,
        limit: 20
      });

      setRecords(searchResponse.data.data || searchResponse.data || []);
    } catch (error) {
      console.error('Search error:', error);
      toast.error('Erreur lors de la recherche. Recherche locale effectuée.');

      // Fallback avec recherche locale en cas d'erreur
      const filtered = records.filter(record =>
        record.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
        record.description.toLowerCase().includes(searchQuery.toLowerCase())
      );
      setRecords(filtered);
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <SearchSection>
        <SearchContainer>
          <SearchForm onSubmit={handleSearch}>
            <SearchInput
              type="text"
              placeholder="Rechercher des documents, événements, personnages..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
            />
            <SearchButton type="submit">
              <FaSearch />
              Rechercher
            </SearchButton>
          </SearchForm>
        </SearchContainer>
      </SearchSection>

      <MainContent>
        <ContentArea>
          {statistics && (
            <StatsGrid>
              <StatCard>
                <div className="icon"><FaFileAlt /></div>
                <div className="number">{statistics.total_records || 0}</div>
                <div className="label">Documents</div>
              </StatCard>
              <StatCard>
                <div className="icon"><FaCalendarAlt /></div>
                <div className="number">{events.length}</div>
                <div className="label">Événements</div>
              </StatCard>
              <StatCard>
                <div className="icon"><FaUsers /></div>
                <div className="number">{statistics.total_users || 0}</div>
                <div className="label">Utilisateurs</div>
              </StatCard>
              <StatCard>
                <div className="icon"><FaDownload /></div>
                <div className="number">{statistics.total_downloads || 0}</div>
                <div className="label">Téléchargements</div>
              </StatCard>
            </StatsGrid>
          )}

          <h2>Documents récents</h2>
          {loading ? (
            <LoadingSpinner />
          ) : (
            records.map((record, index) => (
              <RecordCard key={record.id || index}>
                <div className="title">{record.title || record.name || 'Document sans titre'}</div>
                <div className="metadata">
                  <span>📅 {record.date || record.created_at || 'Date inconnue'}</span>
                  <span>📂 {record.category || record.type || 'Non classé'}</span>
                  <span>👁️ {record.views || record.view_count || 0} vues</span>
                </div>
                <div className="description">
                  {record.description || record.content || record.excerpt || 'Aucune description disponible'}
                </div>
              </RecordCard>
            ))
          )}
        </ContentArea>

        <Sidebar>
          <SidebarSection>
            <h3>Prochains événements</h3>
            {events.map((event, index) => (
              <EventCard key={event.id || index}>
                <div className="date">{event.date || event.event_date || event.created_at || 'Date à confirmer'}</div>
                <div className="title">{event.title || event.name || 'Événement'}</div>
                <div className="description">{event.description || event.content || 'Plus d\'informations bientôt disponibles'}</div>
              </EventCard>
            ))}
          </SidebarSection>

          <SidebarSection>
            <h3>Recherches populaires</h3>
            {popularSearches.map((search, index) => (
              <div key={index} style={{
                padding: '0.5rem',
                background: '#F8F8F8',
                marginBottom: '0.5rem',
                borderRadius: '4px',
                cursor: 'pointer'
              }}
              onClick={() => setSearchQuery(search)}
              >
                <FaHistory style={{ marginRight: '0.5rem' }} />
                {search}
              </div>
            ))}
          </SidebarSection>

          <SidebarSection>
            <h3>Dernières actualités</h3>
            {news.map((article, index) => (
              <div key={article.id || index} style={{ marginBottom: '1rem' }}>
                <div style={{
                  fontWeight: 'bold',
                  color: '#8B4513',
                  marginBottom: '0.25rem'
                }}>
                  {article.title || article.name || 'Article'}
                </div>
                <div style={{
                  fontSize: '0.9rem',
                  color: '#2F4F4F'
                }}>
                  {article.excerpt || article.content || article.description || 'Extrait non disponible'}
                </div>
              </div>
            ))}
          </SidebarSection>
        </Sidebar>
      </MainContent>
    </>
  );
}

export default HomePage;
