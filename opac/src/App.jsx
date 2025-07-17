import React, { useState } from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from 'react-query';
import { ToastContainer } from 'react-toastify';
import { ThemeProvider } from 'styled-components';
import {
  FaBars,
  FaTimes
} from 'react-icons/fa';
import CataloguePage from './pages/Catalogue';
import HomePage from './pages/Home';
import {
  theme,
  Container,
  Header,
  HeaderContent,
  Logo,
  Navigation,
  NavLink,
  MobileMenuToggle,
  Footer
} from './styles/AppStyles';
import 'react-toastify/dist/ReactToastify.css';

// Main App Component
function App() {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
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

            <Routes>
              <Route path="/" element={<HomePage theme={theme} />} />
              <Route path="/catalogue" element={<CataloguePage />} />
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
