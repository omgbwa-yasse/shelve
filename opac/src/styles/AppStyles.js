import styled from 'styled-components';
import { Link } from 'react-router-dom';

// Thème principal
export const theme = {
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

// Styles pour l'application principale
export const Container = styled.div`
  min-height: 100vh;
  background: ${props => props.theme.colors.background};
  color: ${props => props.theme.colors.text};
`;

export const Header = styled.header`
  background: linear-gradient(135deg, ${props => props.theme.colors.primary} 0%, ${props => props.theme.colors.secondary} 100%);
  color: ${props => props.theme.colors.white};
  padding: 1rem 0;
  position: sticky;
  top: 0;
  z-index: 1000;
  box-shadow: 0 4px 20px rgba(0,0,0,0.15);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255,255,255,0.1);
`;

export const HeaderContent = styled.div`
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 2rem;
`;

export const Logo = styled.div`
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

export const Navigation = styled.nav`
  display: flex;
  gap: 0.5rem;
  align-items: center;
  background: rgba(255,255,255,0.1);
  border-radius: 12px;
  padding: 0.5rem;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.2);

  @media (max-width: 768px) {
    display: ${props => props.isOpen ? 'flex' : 'none'};
    position: absolute;
    top: 100%;
    left: 1rem;
    right: 1rem;
    background: rgba(139, 69, 19, 0.95);
    flex-direction: column;
    padding: 1rem;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.2);
    backdrop-filter: blur(15px);
    gap: 0.5rem;
  }
`;

export const MenuButton = styled.button`
  color: ${props => props.$active ? props.theme.colors.primary : props.theme.colors.white};
  background: ${props => props.$active ? 'rgba(255,255,255,0.9)' : 'transparent'};
  border: none;
  text-decoration: none;
  padding: 0.75rem 1.25rem;
  border-radius: 10px;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 500;
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.1);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s ease;
    z-index: -1;
  }

  &:hover {
    background: rgba(255,255,255,0.9);
    color: ${props => props.theme.colors.primary};
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    
    &::before {
      transform: scaleX(1);
    }
  }

  &:active {
    transform: translateY(0);
  }

  /* Styles spécifiques pour les boutons de connexion */
  &.auth-button {
    border: 1px solid rgba(255,255,255,0.3);
    margin-left: 0.5rem;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    
    &:hover {
      background: rgba(255,255,255,0.95);
      color: ${props => props.theme.colors.primary};
      border-color: rgba(255,255,255,0.5);
    }
  }

  /* Style spécifique pour le bouton d'inscription */
  &.register-button {
    background: linear-gradient(135deg, ${props => props.theme.colors.accent} 0%, ${props => props.theme.colors.secondary} 100%);
    border: 1px solid rgba(255,255,255,0.2);
    
    &:hover {
      background: rgba(255,255,255,0.95);
      color: ${props => props.theme.colors.primary};
      transform: translateY(-3px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
  }

  @media (max-width: 768px) {
    width: 100%;
    justify-content: center;
    padding: 1rem;
    margin: 0.25rem 0;
  }
`;

export const NavLink = styled(Link)`
  color: ${props => props.theme.colors.white};
  text-decoration: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  transition: background 0.3s;

  &:hover {
    background: ${props => props.theme.colors.secondary};
  }
`;

export const MobileMenuButton = styled.button`
  display: none;
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.2);
  color: ${props => props.theme.colors.white};
  padding: 0.75rem;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.1);
    transform: scale(0);
    transition: transform 0.3s ease;
    border-radius: 10px;
    z-index: -1;
  }

  &:hover {
    background: rgba(255,255,255,0.2);
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    
    &::before {
      transform: scale(1);
    }
  }

  &:active {
    transform: scale(0.95);
  }

  @media (max-width: 768px) {
    display: block;
  }
`;

export const Footer = styled.footer`
  background: ${props => props.theme.colors.primary};
  color: ${props => props.theme.colors.white};
  text-align: center;
  padding: 2rem 0;
  margin-top: 4rem;
`;
