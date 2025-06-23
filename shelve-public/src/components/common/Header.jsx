import React, { useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import styled from 'styled-components';
import { useAuth } from '../../context/AuthContext';
import { FEATURES } from '../../utils/constants';

// Styled components
const HeaderContainer = styled.header`
  background: white;
  border-bottom: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
`;

const HeaderContent = styled.div`
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 64px;

  @media (max-width: 768px) {
    padding: 0 0.5rem;
  }
`;

const Logo = styled(Link)`
  font-size: 1.5rem;
  font-weight: bold;
  color: #007bff;
  text-decoration: none;

  &:hover {
    color: #0056b3;
    text-decoration: none;
  }
`;

const Nav = styled.nav`
  display: flex;
  align-items: center;
  gap: 2rem;

  @media (max-width: 768px) {
    display: ${props => props.isOpen ? 'flex' : 'none'};
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    flex-direction: column;
    padding: 1rem;
    border-top: 1px solid #dee2e6;
    gap: 1rem;
  }
`;

const NavLink = styled(Link)`
  color: #495057;
  text-decoration: none;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  transition: all 0.2s ease;

  &:hover,
  &.active {
    color: #007bff;
    background: #f8f9fa;
    text-decoration: none;
  }
`;

const UserSection = styled.div`
  display: flex;
  align-items: center;
  gap: 1rem;

  @media (max-width: 768px) {
    gap: 0.5rem;
  }
`;

const UserName = styled.span`
  color: #495057;
  font-weight: 500;

  @media (max-width: 768px) {
    display: none;
  }
`;

const Button = styled.button`
  padding: 0.5rem 1rem;
  border: 1px solid transparent;
  border-radius: 4px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
  display: inline-block;

  ${props => props.variant === 'primary' ? `
    background: #007bff;
    color: white;
    border-color: #007bff;

    &:hover {
      background: #0056b3;
      border-color: #0056b3;
    }
  ` : `
    background: transparent;
    color: #6c757d;
    border-color: #6c757d;

    &:hover {
      background: #6c757d;
      color: white;
    }
  `}
`;

const MobileMenuButton = styled.button`
  display: none;
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #495057;

  @media (max-width: 768px) {
    display: block;
  }
`;

const Header = () => {
  const { user, isAuthenticated, logout } = useAuth();
  const location = useLocation();
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  const isActive = (path) => {
    return location.pathname === path ? 'active' : '';
  };

  const handleLogout = () => {
    logout();
    setIsMobileMenuOpen(false);
  };

  const toggleMobileMenu = () => {
    setIsMobileMenuOpen(!isMobileMenuOpen);
  };

  const closeMobileMenu = () => {
    setIsMobileMenuOpen(false);
  };

  return (
    <HeaderContainer>
      <HeaderContent>
        <Logo to="/" onClick={closeMobileMenu}>
          Shelve Public
        </Logo>

        <Nav isOpen={isMobileMenuOpen}>
          <NavLink
            to="/"
            className={isActive('/')}
            onClick={closeMobileMenu}
          >
            Accueil
          </NavLink>

          <NavLink
            to="/events"
            className={isActive('/events')}
            onClick={closeMobileMenu}
          >
            Événements
          </NavLink>

          <NavLink
            to="/news"
            className={isActive('/news')}
            onClick={closeMobileMenu}
          >
            Actualités
          </NavLink>

          <NavLink
            to="/records"
            className={isActive('/records')}
            onClick={closeMobileMenu}
          >
            Archives
          </NavLink>

          <NavLink
            to="/documents/request"
            className={isActive('/documents/request')}
            onClick={closeMobileMenu}
          >
            Demandes
          </NavLink>

          {FEATURES.CHAT_ENABLED && (
            <NavLink
              to="/chat"
              className={isActive('/chat')}
              onClick={closeMobileMenu}
            >
              Chat
            </NavLink>
          )}

          <NavLink
            to="/feedback"
            className={isActive('/feedback')}
            onClick={closeMobileMenu}
          >
            Contact
          </NavLink>
        </Nav>

        <UserSection>
          {isAuthenticated ? (
            <>
              <UserName>
                Bonjour, {user?.name || user?.email}
              </UserName>
              <NavLink
                to="/user/dashboard"
                className={isActive('/user/dashboard')}
                onClick={closeMobileMenu}
              >
                Mon compte
              </NavLink>
              <Button variant="secondary" onClick={handleLogout}>
                Déconnexion
              </Button>
            </>
          ) : (
            <Button
              as={Link}
              to="/user/register"
              variant="primary"
              onClick={closeMobileMenu}
            >
              Connexion
            </Button>
          )}
        </UserSection>

        <MobileMenuButton onClick={toggleMobileMenu}>
          {isMobileMenuOpen ? '✕' : '☰'}
        </MobileMenuButton>
      </HeaderContent>
    </HeaderContainer>
  );
};

export default Header;
