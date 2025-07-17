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
  background: ${props => props.theme.colors.primary};
  color: ${props => props.theme.colors.white};
  padding: 1rem 0;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

export const MobileMenuToggle = styled.button`
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

export const Footer = styled.footer`
  background: ${props => props.theme.colors.primary};
  color: ${props => props.theme.colors.white};
  text-align: center;
  padding: 2rem 0;
  margin-top: 4rem;
`;
