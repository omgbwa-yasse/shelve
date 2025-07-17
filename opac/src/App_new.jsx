import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Link } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from 'react-query';
import { ToastContainer } from 'react-toastify';
import styled, { ThemeProvider } from 'styled-components';
import {
  FaSearch,
  FaCalendarAlt,
  FaFileAlt,
  FaUsers,
  FaDownload,
  FaHistory,
  FaBars,
  FaTimes
} from 'react-icons/fa';
import shelveApi from './services/AllServices';
import 'react-toastify/dist/ReactToastify.css';

// Styled Components
const theme = {
  colors: {
    primary: '#8B4513',
    secondary: '#D2691E',
    accent: '#CD853F',
    background: '#F5F5DC',
    text: '#2F4F4F',
    white: '#FFFFFF',
    light: '#F8F8F8'
  }
};

const Container = styled.div`
  min-height: 100vh;
  background: ${props => props.theme.colors.background};
  color: ${props => props.theme.colors.text};
`;

const Header = styled.header`
  background: ${props => props.theme.colors.primary};
  color: ${props => props.theme.colors.white};
  padding: 1rem 0;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
`;

const HeaderContent = styled.div`
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 2rem;
`;

const Logo = styled.div`
  display: flex;
  align-items: center;
  gap: 1rem;

  h1 {
    font-size: 1.8rem;
    font-weight: bold;
    margin: 0;
  }

  .subtitle {
    font-size: 0.9rem;
    opacity: 0.8;
    margin-top: 0.25rem;
  }
`;

const Navigation = styled.nav`
  display: flex;
  gap: 2rem;
  align-items: center;

  @media (max-width: 768px) {
    display: ${props => props.isOpen ? 'flex' : 'none'};
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: ${props => props.theme.colors.primary};
    flex-direction: column;
    padding: 1rem 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }
`;

const NavLink = styled(Link)`
  color: ${props => props.theme.colors.white};
  text-decoration: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  transition: background 0.3s;

  &:hover {
    background: ${props => props.theme.colors.secondary};
  }
`;

const MobileMenuToggle = styled.button`
  display: none;
  background: none;
  border: none;
  color: ${props => props.theme.colors.white};
  font-size: 1.5rem;
  cursor: pointer;

  @media (max-width: 768px) {
    display: block;
  }
`;

const SearchSection = styled.section`
  background: ${props => props.theme.colors.white};
  padding: 3rem 0;
  text-align: center;
`;

const SearchContainer = styled.div`
  max-width: 800px;
  margin: 0 auto;
  padding: 0 2rem;
`;

const SearchForm = styled.form`
  display: flex;
  gap: 1rem;
  margin-top: 2rem;

  @media (max-width: 768px) {
    flex-direction: column;
  }
`;

const SearchInput = styled.input`
  flex: 1;
  padding: 1rem;
  border: 2px solid ${props => props.theme.colors.accent};
  border-radius: 8px;
  font-size: 1rem;

  &:focus {
    outline: none;
    border-color: ${props => props.theme.colors.primary};
  }
`;

const SearchButton = styled.button`
  background: ${props => props.theme.colors.primary};
  color: ${props => props.theme.colors.white};
  border: none;
  padding: 1rem 2rem;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: background 0.3s;

  &:hover {
    background: ${props => props.theme.colors.secondary};
  }
`;

const FiltersContainer = styled.div`
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
  justify-content: center;
  flex-wrap: wrap;
`;

const FilterButton = styled.button`
  background: ${props => props.active ? props.theme.colors.secondary : props.theme.colors.light};
  color: ${props => props.active ? props.theme.colors.white : props.theme.colors.text};
  border: 1px solid ${props => props.theme.colors.accent};
  padding: 0.5rem 1rem;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s;

  &:hover {
    background: ${props => props.theme.colors.secondary};
    color: ${props => props.theme.colors.white};
  }
`;

const MainContent = styled.main`
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 2rem;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
    padding: 1rem;
  }
`;

const ContentArea = styled.div`
  background: ${props => props.theme.colors.white};
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
`;

const Sidebar = styled.aside`
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
`;

const SidebarSection = styled.div`
  background: ${props => props.theme.colors.white};
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);

  h3 {
    margin-top: 0;
    color: ${props => props.theme.colors.primary};
    border-bottom: 2px solid ${props => props.theme.colors.accent};
    padding-bottom: 0.5rem;
  }
`;

const StatsGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
`;

const StatCard = styled.div`
  background: ${props => props.theme.colors.white};
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  text-align: center;

  .icon {
    font-size: 2rem;
    color: ${props => props.theme.colors.primary};
    margin-bottom: 1rem;
  }

  .number {
    font-size: 2rem;
    font-weight: bold;
    color: ${props => props.theme.colors.secondary};
  }

  .label {
    color: ${props => props.theme.colors.text};
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
`;

const RecordCard = styled.div`
  background: ${props => props.theme.colors.white};
  border: 1px solid ${props => props.theme.colors.accent};
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 1rem;
  transition: transform 0.3s;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }

  .title {
    font-size: 1.2rem;
    font-weight: bold;
    color: ${props => props.theme.colors.primary};
    margin-bottom: 0.5rem;
  }

  .metadata {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: ${props => props.theme.colors.text};
  }

  .description {
    color: ${props => props.theme.colors.text};
    line-height: 1.5;
  }
`;

const EventCard = styled.div`
  border-left: 4px solid ${props => props.theme.colors.secondary};
  padding: 1rem;
  margin-bottom: 1rem;
  background: ${props => props.theme.colors.light};

  .date {
    font-size: 0.9rem;
    color: ${props => props.theme.colors.secondary};
    font-weight: bold;
  }

  .title {
    font-size: 1.1rem;
    color: ${props => props.theme.colors.primary};
    margin: 0.5rem 0;
  }

  .description {
    color: ${props => props.theme.colors.text};
    font-size: 0.9rem;
  }
`;

const LoadingSpinner = styled.div`
  display: flex;
  justify-content: center;
  align-items: center;
  height: 200px;

  &::after {
    content: '';
    width: 40px;
    height: 40px;
    border: 4px solid ${props => props.theme.colors.light};
    border-top: 4px solid ${props => props.theme.colors.primary};
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
`;

const Footer = styled.footer`
  background: ${props => props.theme.colors.primary};
  color: ${props => props.theme.colors.white};
  text-align: center;
  padding: 2rem 0;
  margin-top: 4rem;
`;

// Main App Component
function App() {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [activeFilter, setActiveFilter] = useState('all');
  const [records, setRecords] = useState([]);
  const [events, setEvents] = useState([]);
  const [news, setNews] = useState([]);
  const [statistics, setStatistics] = useState(null);
  const [loading, setLoading] = useState(false);
  const [popularSearches, setPopularSearches] = useState([]);

  const queryClient = new QueryClient();

  // Load initial data
  useEffect(() => {
    loadInitialData();
  }, []);

  const loadInitialData = async () => {
    setLoading(true);
    try {
      // Simulate API calls with fallback data
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

      setPopularSearches([
        'registres paroissiaux',
        'cadastre napoléonien',
        'correspondance',
        'cartes anciennes',
        'actes notariés'
      ]);
    } catch (error) {
      console.error('Error loading initial data:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = async (e) => {
    e.preventDefault();
    if (!searchQuery.trim()) return;

    setLoading(true);
    try {
      // Simulate search
      const filtered = records.filter(record =>
        record.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
        record.description.toLowerCase().includes(searchQuery.toLowerCase())
      );
      setRecords(filtered);
    } catch (error) {
      console.error('Search error:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleFilterChange = (filter) => {
    setActiveFilter(filter);
    if (searchQuery) {
      handleSearch({ preventDefault: () => {} });
    }
  };

  return (
    <ThemeProvider theme={theme}>
      <QueryClientProvider client={queryClient}>
        <Container>
          <Router>
            <Header>
              <HeaderContent>
                <Logo>
                  <div>
                    <h1>Archives Historiques</h1>
                    <div className="subtitle">Service de documentation et patrimoine</div>
                  </div>
                </Logo>
                <Navigation isOpen={mobileMenuOpen}>
                  <NavLink to="/">Accueil</NavLink>
                  <NavLink to="/catalogue">Catalogue</NavLink>
                  <NavLink to="/events">Événements</NavLink>
                  <NavLink to="/news">Actualités</NavLink>
                  <NavLink to="/about">À propos</NavLink>
                </Navigation>
                <MobileMenuToggle onClick={() => setMobileMenuOpen(!mobileMenuOpen)}>
                  {mobileMenuOpen ? <FaTimes /> : <FaBars />}
                </MobileMenuToggle>
              </HeaderContent>
            </Header>

            <SearchSection>
              <SearchContainer>
                <h2>Explorez notre patrimoine documentaire</h2>
                <p>Recherchez dans nos collections d'archives historiques, documents patrimoniaux et ressources numériques</p>

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

                <FiltersContainer>
                  <FilterButton
                    active={activeFilter === 'all'}
                    onClick={() => handleFilterChange('all')}
                  >
                    Tout
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
              </SearchContainer>
            </SearchSection>

            <Routes>
              <Route path="/" element={
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
                        <RecordCard key={index}>
                          <div className="title">{record.title || 'Document sans titre'}</div>
                          <div className="metadata">
                            <span>📅 {record.date || 'Date inconnue'}</span>
                            <span>📂 {record.category || 'Non classé'}</span>
                            <span>👁️ {record.views || 0} vues</span>
                          </div>
                          <div className="description">
                            {record.description || 'Aucune description disponible'}
                          </div>
                        </RecordCard>
                      ))
                    )}
                  </ContentArea>

                  <Sidebar>
                    <SidebarSection>
                      <h3>Prochains événements</h3>
                      {events.map((event, index) => (
                        <EventCard key={index}>
                          <div className="date">{event.date || 'Date à confirmer'}</div>
                          <div className="title">{event.title || 'Événement'}</div>
                          <div className="description">{event.description || 'Plus d\'informations bientôt disponibles'}</div>
                        </EventCard>
                      ))}
                    </SidebarSection>

                    <SidebarSection>
                      <h3>Recherches populaires</h3>
                      {popularSearches.map((search, index) => (
                        <div key={index} style={{
                          padding: '0.5rem',
                          background: theme.colors.light,
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
                        <div key={index} style={{ marginBottom: '1rem' }}>
                          <div style={{
                            fontWeight: 'bold',
                            color: theme.colors.primary,
                            marginBottom: '0.25rem'
                          }}>
                            {article.title || 'Article'}
                          </div>
                          <div style={{
                            fontSize: '0.9rem',
                            color: theme.colors.text
                          }}>
                            {article.excerpt || 'Extrait non disponible'}
                          </div>
                        </div>
                      ))}
                    </SidebarSection>
                  </Sidebar>
                </MainContent>
              } />
            </Routes>

            <Footer>
              <p>&copy; 2024 Archives Historiques. Tous droits réservés.</p>
              <p>Préservation du patrimoine documentaire pour les générations futures</p>
            </Footer>

            <ToastContainer />
          </Router>
        </Container>
      </QueryClientProvider>
    </ThemeProvider>
  );
}

export default App;
