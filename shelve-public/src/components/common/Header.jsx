import React, { useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import styled from 'styled-components';
import { useAuth } from '../../context/AuthContext.js';

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

const UserMenu = styled.div`
  position: relative;
  display: inline-block;
`;

const UserMenuButton = styled.button`
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: transparent;
  border: 2px solid #007bff;
  border-radius: 25px;
  color: #007bff;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;

  &:hover {
    background: #007bff;
    color: white;
  }

  &:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
  }
`;

const UserMenuDropdown = styled.div`
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 0.5rem;
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  min-width: 250px;
  z-index: 1000;
  display: ${props => props.isOpen ? 'block' : 'none'};

  @media (max-width: 768px) {
    position: fixed;
    top: 64px;
    right: 1rem;
    left: 1rem;
    width: auto;
  }
`;

const UserMenuHeader = styled.div`
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
  background: #f8f9fa;
  border-radius: 8px 8px 0 0;
`;

const UserMenuName = styled.div`
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.25rem;
`;

const UserMenuEmail = styled.div`
  font-size: 0.875rem;
  color: #6c757d;
`;

const UserMenuItem = styled(Link)`
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: #495057;
  text-decoration: none;
  transition: background-color 0.2s ease;
  border-bottom: 1px solid #f8f9fa;

  &:hover {
    background: #f8f9fa;
    color: #007bff;
    text-decoration: none;
  }

  &:last-child {
    border-bottom: none;
  }
`;

const UserMenuButton2 = styled.button`
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.75rem 1rem;
  background: none;
  border: none;
  color: #dc3545;
  text-align: left;
  cursor: pointer;
  transition: background-color 0.2s ease;
  border-radius: 0 0 8px 8px;

  &:hover {
    background: #f8d7da;
    color: #721c24;
  }
`;

const Header = () => {
  const { user, isAuthenticated, logout } = useAuth();
  const location = useLocation();
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [isUserMenuOpen, setIsUserMenuOpen] = useState(false);

  const isActive = (path) => {
    return location.pathname === path ? 'active' : '';
  };

  const handleLogout = () => {
    logout();
    setIsMobileMenuOpen(false);
    setIsUserMenuOpen(false);
  };

  const toggleMobileMenu = () => {
    setIsMobileMenuOpen(!isMobileMenuOpen);
  };

  const toggleUserMenu = () => {
    setIsUserMenuOpen(!isUserMenuOpen);
  };

  const closeMobileMenu = () => {
    setIsMobileMenuOpen(false);
  };

  const closeUserMenu = () => {
    setIsUserMenuOpen(false);
  };

  // Close user menu when clicking outside
  React.useEffect(() => {
    const handleClickOutside = (event) => {
      if (isUserMenuOpen && !event.target.closest('[data-user-menu]')) {
        setIsUserMenuOpen(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [isUserMenuOpen]);

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
            to="/feedback"
            className={isActive('/feedback')}
            onClick={closeMobileMenu}
          >
            Contact
          </NavLink>
        </Nav>

        <UserSection>
          {isAuthenticated ? (
            <UserMenu data-user-menu>
              <UserMenuButton onClick={toggleUserMenu}>
                <span>👤</span>
                <span>{user?.first_name || user?.name || 'Mon compte'}</span>
                <span>{isUserMenuOpen ? '▼' : '▶'}</span>
              </UserMenuButton>

              <UserMenuDropdown isOpen={isUserMenuOpen}>
                <UserMenuHeader>
                  <UserMenuName>
                    {user?.first_name && user?.last_name
                      ? `${user.first_name} ${user.last_name}`
                      : user?.name || 'Utilisateur'
                    }
                  </UserMenuName>
                  <UserMenuEmail>{user?.email}</UserMenuEmail>
                </UserMenuHeader>

                <UserMenuItem
                  to="/user/dashboard"
                  onClick={closeUserMenu}
                >
                  <span>🏠</span>
                  <span>Tableau de bord</span>
                </UserMenuItem>

                <UserMenuItem
                  to="/chat"
                  onClick={closeUserMenu}
                >
                  <span>💬</span>
                  <span>Assistant virtuel</span>
                </UserMenuItem>

                <UserMenuItem
                  to="/documents/request"
                  onClick={closeUserMenu}
                >
                  <span>📄</span>
                  <span>Mes demandes</span>
                </UserMenuItem>

                <UserMenuItem
                  to="/user/settings"
                  onClick={closeUserMenu}
                >
                  <span>⚙️</span>
                  <span>Paramètres</span>
                </UserMenuItem>

                <UserMenuButton2 onClick={handleLogout}>
                  <span>🚪</span>
                  <span>Déconnexion</span>
                </UserMenuButton2>
              </UserMenuDropdown>
            </UserMenu>
          ) : (
            <>
              <Button
                as={Link}
                to="/login"
                variant="secondary"
                onClick={closeMobileMenu}
              >
                Connexion
              </Button>
              <Button
                as={Link}
                to="/register"
                variant="primary"
                onClick={closeMobileMenu}
              >
                Inscription
              </Button>
            </>
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
