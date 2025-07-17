import React, { useState } from 'react';
import { BrowserRouter as Router, Routes, Route, Link } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from 'react-query';
import { ToastContainer } from 'react-toastify';
import styled, { ThemeProvider } from 'styled-components';
import {
  FaBars,
  FaTimes
} from 'react-icons/fa';
import CataloguePage from './pages/Catalogue';
import HomePage from './pages/Home';
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
