import React, { useState } from 'react';
import { BrowserRouter as Router, Routes, Route, Link } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from 'react-query';
import { ToastContainer } from 'react-toastify';
import { ThemeProvider } from 'styled-components';
import {
  FaBars,
  FaTimes,
  FaHome,
  FaBook,
  FaCalendarAlt,
  FaNewspaper,
  FaInfoCircle,
  FaSignInAlt,
  FaUserPlus
} from 'react-icons/fa';
import CataloguePage from './pages/Catalogue';
import HomePage from './pages/Home';
import EventsPage from './pages/Events';
import NewsPage from './pages/News';
import AboutPage from './pages/About';
import LoginPage from './pages/Login';
import RegisterPage from './pages/Register';
import {
  theme,
  Container,
  Header,
  HeaderContent,
  Logo,
  Navigation,
  MenuButton,
  MobileMenuToggle,
  Footer
} from './styles/AppStyles';
import 'react-toastify/dist/ReactToastify.css';

// Main App Component
function App() {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [activeMenu, setActiveMenu] = useState('home');
  const queryClient = new QueryClient();

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
                        <Navigation>
          <Link to="/" onClick={() => setActiveMenu('home')}>
            <MenuButton $active={activeMenu === 'home'}>
              <FaHome /> Accueil
            </MenuButton>
          </Link>
          <Link to="/catalogue" onClick={() => setActiveMenu('catalogue')}>
            <MenuButton $active={activeMenu === 'catalogue'}>
              <FaBook /> Catalogue
            </MenuButton>
          </Link>
          <Link to="/events" onClick={() => setActiveMenu('events')}>
            <MenuButton $active={activeMenu === 'events'}>
              <FaCalendarAlt /> Événements
            </MenuButton>
          </Link>
          <Link to="/news" onClick={() => setActiveMenu('news')}>
            <MenuButton $active={activeMenu === 'news'}>
              <FaNewspaper /> Actualités
            </MenuButton>
          </Link>
          <Link to="/about" onClick={() => setActiveMenu('about')}>
            <MenuButton $active={activeMenu === 'about'}>
              <FaInfoCircle /> À propos
            </MenuButton>
          </Link>
          <Link to="/login" onClick={() => setActiveMenu('login')}>
            <MenuButton $active={activeMenu === 'login'}>
              <FaSignInAlt /> Connexion
            </MenuButton>
          </Link>
          <Link to="/register" onClick={() => setActiveMenu('register')}>
            <MenuButton $active={activeMenu === 'register'}>
              <FaUserPlus /> Inscription
            </MenuButton>
          </Link>
        </Navigation>
                <MobileMenuToggle onClick={() => setMobileMenuOpen(!mobileMenuOpen)}>
                  {mobileMenuOpen ? <FaTimes /> : <FaBars />}
                </MobileMenuToggle>
              </HeaderContent>
            </Header>

            <Routes>
              <Route path="/" element={<HomePage />} />
              <Route path="/catalogue" element={<CataloguePage />} />
              <Route path="/events" element={<EventsPage />} />
              <Route path="/news" element={<NewsPage />} />
              <Route path="/about" element={<AboutPage />} />
            </Routes>            <Footer>
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
