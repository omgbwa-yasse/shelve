import React from 'react';
import { Link } from 'react-router-dom';
import styled from 'styled-components';
import { APP_CONFIG } from '../../utils/constants';

// Styled components
const FooterContainer = styled.footer`
  background: #343a40;
  color: white;
  margin-top: auto;
`;

const FooterContent = styled.div`
  max-width: 1200px;
  margin: 0 auto;
  padding: 3rem 1rem 2rem;

  @media (max-width: 768px) {
    padding: 2rem 0.5rem 1rem;
  }
`;

const FooterGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
`;

const FooterSection = styled.div`
  h4 {
    color: #007bff;
    margin-bottom: 1rem;
    font-size: 1.125rem;
    font-weight: 600;
  }
`;

const FooterList = styled.ul`
  list-style: none;
  padding: 0;
  margin: 0;

  li {
    margin: 0.5rem 0;
  }
`;

const FooterLink = styled(Link)`
  color: #adb5bd;
  text-decoration: none;
  transition: color 0.2s ease;

  &:hover {
    color: #007bff;
    text-decoration: none;
  }
`;

const ExternalLink = styled.a`
  color: #adb5bd;
  text-decoration: none;
  transition: color 0.2s ease;

  &:hover {
    color: #007bff;
    text-decoration: none;
  }
`;

const FooterText = styled.p`
  color: #adb5bd;
  margin: 0.5rem 0;
  line-height: 1.6;
`;

const FooterBottom = styled.div`
  border-top: 1px solid #495057;
  padding-top: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;

  @media (max-width: 768px) {
    flex-direction: column;
    text-align: center;
    gap: 0.5rem;
  }
`;

const Copyright = styled.p`
  color: #6c757d;
  margin: 0;
  font-size: 0.875rem;
`;

const SocialLinks = styled.div`
  display: flex;
  gap: 1rem;
  align-items: center;
`;

const SocialLink = styled.a`
  color: #adb5bd;
  font-size: 1.25rem;
  transition: color 0.2s ease;

  &:hover {
    color: #007bff;
  }
`;

const Badge = styled.span`
  background: #007bff;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
`;

const Footer = () => {
  const currentYear = new Date().getFullYear();

  return (
    <FooterContainer>
      <FooterContent>
        <FooterGrid>
          {/* À propos */}
          <FooterSection>
            <h4>À propos de Shelve</h4>
            <FooterText>
              Shelve est un système moderne de gestion d'archives qui permet
              l'accès public aux documents, événements et services de votre organisation.
            </FooterText>
            <FooterText>
              <Badge>v{APP_CONFIG.VERSION}</Badge>
            </FooterText>
          </FooterSection>

          {/* Navigation */}
          <FooterSection>
            <h4>Navigation</h4>
            <FooterList>
              <li><FooterLink to="/">Accueil</FooterLink></li>
              <li><FooterLink to="/events">Événements</FooterLink></li>
              <li><FooterLink to="/news">Actualités</FooterLink></li>
              <li><FooterLink to="/records">Archives</FooterLink></li>
              <li><FooterLink to="/documents/request">Demandes de documents</FooterLink></li>
              <li><FooterLink to="/feedback">Nous contacter</FooterLink></li>
            </FooterList>
          </FooterSection>

          {/* Services */}
          <FooterSection>
            <h4>Services</h4>
            <FooterList>
              <li><FooterLink to="/user/dashboard">Mon compte</FooterLink></li>
              <li><FooterLink to="/search">Recherche avancée</FooterLink></li>
              <li><FooterLink to="/templates">Modèles</FooterLink></li>
              <li><FooterLink to="/chat">Chat public</FooterLink></li>
              <li><FooterLink to="/user/register">Inscription</FooterLink></li>
            </FooterList>
          </FooterSection>

          {/* Contact */}
          <FooterSection>
            <h4>Contact</h4>
            <FooterText>
              <strong>Adresse:</strong><br />
              123 Rue des Archives<br />
              75000 Paris, France
            </FooterText>
            <FooterText>
              <strong>Téléphone:</strong><br />
              <ExternalLink href="tel:+33123456789">+33 1 23 45 67 89</ExternalLink>
            </FooterText>
            <FooterText>
              <strong>Email:</strong><br />
              <ExternalLink href="mailto:contact@shelve.fr">contact@shelve.fr</ExternalLink>
            </FooterText>
          </FooterSection>
        </FooterGrid>

        <FooterBottom>
          <Copyright>
            © {currentYear} {APP_CONFIG.NAME}. Tous droits réservés.
          </Copyright>

          <SocialLinks>
            <SocialLink
              href="https://facebook.com"
              target="_blank"
              rel="noopener noreferrer"
              title="Facebook"
            >
              📘
            </SocialLink>
            <SocialLink
              href="https://twitter.com"
              target="_blank"
              rel="noopener noreferrer"
              title="Twitter"
            >
              🐦
            </SocialLink>
            <SocialLink
              href="https://linkedin.com"
              target="_blank"
              rel="noopener noreferrer"
              title="LinkedIn"
            >
              💼
            </SocialLink>
            <SocialLink
              href="mailto:contact@shelve.fr"
              title="Email"
            >
              ✉️
            </SocialLink>
          </SocialLinks>
        </FooterBottom>
      </FooterContent>
    </FooterContainer>
  );
};

export default Footer;
