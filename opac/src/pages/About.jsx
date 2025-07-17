import React from 'react';
import {
  FaMapMarkerAlt,
  FaPhone,
  FaEnvelope,
  FaClock,
  FaUsers,
  FaBook,
  FaDownload,
  FaAward,
  FaExternalLinkAlt
} from 'react-icons/fa';
import {
  AboutContainer,
  AboutHeader,
  AboutContent,
  MainContent,
  ContentSection,
  Sidebar,
  SidebarCard,
  ContactInfo,
  StatsGrid,
  StatCard,
  TeamGrid,
  TeamMember,
  TimelineContainer,
  TimelineItem,
  ActionButton
} from '../styles/AboutStyles';

const AboutPage = () => {
  const teamMembers = [
    {
      name: 'Marie Dubois',
      role: 'Directrice des Archives',
      avatar: 'MD'
    },
    {
      name: 'Jean-Pierre Martin',
      role: 'Archiviste Senior',
      avatar: 'JM'
    },
    {
      name: 'Sophie Blanc',
      role: 'Responsable Numérisation',
      avatar: 'SB'
    },
    {
      name: 'Alain Rousseau',
      role: 'Restaurateur',
      avatar: 'AR'
    },
    {
      name: 'Catherine Leroy',
      role: 'Médiatrice Culturelle',
      avatar: 'CL'
    },
    {
      name: 'Marc Durand',
      role: 'Informaticien Documentaliste',
      avatar: 'MD'
    }
  ];

  const timeline = [
    {
      year: '2024',
      event: 'Lancement du nouveau portail numérique et partenariat avec l\'Université locale'
    },
    {
      year: '2022',
      event: 'Inauguration de l\'atelier de restauration et acquisition d\'équipements de numérisation haute résolution'
    },
    {
      year: '2020',
      event: 'Début du grand projet de numérisation des archives anciennes'
    },
    {
      year: '2018',
      event: 'Extension du bâtiment et création d\'espaces de consultation modernisés'
    },
    {
      year: '2015',
      event: 'Mise en place du système de gestion électronique des documents'
    },
    {
      year: '2010',
      event: 'Création du service éducatif et développement des activités culturelles'
    },
    {
      year: '2005',
      event: 'Rénovation complète des locaux et mise aux normes de conservation'
    },
    {
      year: '1995',
      event: 'Création du service des Archives Historiques'
    }
  ];

  const stats = [
    { number: '50,000+', label: 'Documents' },
    { number: '500+', label: 'Heures/an' },
    { number: '1,200+', label: 'Visiteurs/an' },
    { number: '25+', label: 'Années' }
  ];

  return (
    <AboutContainer>
      <AboutHeader>
        <h1>À propos de nous</h1>
        <div className="subtitle">Service de documentation et patrimoine historique</div>
        <p className="description">
          Depuis plus de 25 ans, notre service œuvre pour la préservation, la valorisation et la
          mise à disposition du patrimoine documentaire historique. Nous sommes les gardiens de
          la mémoire collective et les facilitateurs de la recherche historique.
        </p>
      </AboutHeader>

      <AboutContent>
        <MainContent>
          <ContentSection>
            <h2>Notre Mission</h2>
            <p>
              Le Service des Archives Historiques a pour mission principale de collecter, conserver,
              classer et communiquer les documents d'archives publiques et privées présentant un
              intérêt historique, administratif ou culturel.
            </p>
            <p>
              Nous nous engageons à :
            </p>
            <ul>
              <li>Préserver le patrimoine documentaire pour les générations futures</li>
              <li>Faciliter l'accès aux documents pour les chercheurs et le grand public</li>
              <li>Numériser et moderniser nos collections</li>
              <li>Développer des activités culturelles et éducatives</li>
              <li>Conseiller les administrations et les particuliers</li>
            </ul>
          </ContentSection>

          <ContentSection>
            <h2>Nos Collections</h2>
            <h3>Archives Anciennes (avant 1790)</h3>
            <p>
              Registres paroissiaux, chartes, titres de propriété, comptabilités seigneuriales
              et documents relatifs à l'Ancien Régime.
            </p>

            <h3>Archives Modernes (1790-1940)</h3>
            <p>
              États civils, registres de délibérations, correspondances administratives,
              plans cadastraux et documents de l'époque révolutionnaire et post-révolutionnaire.
            </p>

            <h3>Archives Contemporaines (après 1940)</h3>
            <p>
              Documents administratifs récents, témoignages oraux, photographies et
              collections privées d'intérêt historique.
            </p>
          </ContentSection>

          <ContentSection>
            <h2>Nos Services</h2>
            <ul>
              <li><strong>Consultation sur place</strong> : Salle de lecture équipée pour la consultation des documents originaux</li>
              <li><strong>Recherches personnalisées</strong> : Aide à la recherche généalogique et historique</li>
              <li><strong>Reproduction de documents</strong> : Photocopies, scans et photographies des documents</li>
              <li><strong>Visites guidées</strong> : Découverte des coulisses des archives</li>
              <li><strong>Formations</strong> : Ateliers de paléographie et d'histoire locale</li>
              <li><strong>Expositions</strong> : Valorisation des collections par des expositions temporaires</li>
            </ul>
          </ContentSection>

          <ContentSection>
            <h2>Notre Équipe</h2>
            <p>
              Notre équipe pluridisciplinaire réunit des compétences variées au service de la
              préservation et de la valorisation du patrimoine.
            </p>
            <TeamGrid>
              {teamMembers.map((member, index) => (
                <TeamMember key={index}>
                  <div className="member-avatar">
                    {member.avatar}
                  </div>
                  <div className="member-name">
                    {member.name}
                  </div>
                  <div className="member-role">
                    {member.role}
                  </div>
                </TeamMember>
              ))}
            </TeamGrid>
          </ContentSection>

          <ContentSection>
            <h2>Notre Histoire</h2>
            <p>
              Retracez l'évolution de notre service à travers les principales étapes de son développement.
            </p>
            <TimelineContainer>
              {timeline.map((item, index) => (
                <TimelineItem key={index}>
                  <div className="timeline-year">{item.year}</div>
                  <div className="timeline-event">{item.event}</div>
                </TimelineItem>
              ))}
            </TimelineContainer>
          </ContentSection>
        </MainContent>

        <Sidebar>
          <SidebarCard>
            <h3>Informations Pratiques</h3>
            <ContactInfo>
              <div className="contact-item">
                <FaMapMarkerAlt className="contact-icon" />
                <div className="contact-text">
                  <strong>Adresse</strong><br />
                  123 Rue des Archives<br />
                  75000 Paris, France
                </div>
              </div>
              <div className="contact-item">
                <FaPhone className="contact-icon" />
                <div className="contact-text">
                  <strong>Téléphone</strong><br />
                  +33 1 23 45 67 89
                </div>
              </div>
              <div className="contact-item">
                <FaEnvelope className="contact-icon" />
                <div className="contact-text">
                  <strong>Email</strong><br />
                  contact@archives-historiques.fr
                </div>
              </div>
              <div className="contact-item">
                <FaClock className="contact-icon" />
                <div className="contact-text">
                  <strong>Horaires</strong><br />
                  Lun-Ven: 9h-17h<br />
                  Sam: 9h-12h
                </div>
              </div>
            </ContactInfo>
          </SidebarCard>

          <SidebarCard>
            <h3>Chiffres Clés</h3>
            <StatsGrid>
              {stats.map((stat, index) => (
                <StatCard key={index}>
                  <div className="stat-number">{stat.number}</div>
                  <div className="stat-label">{stat.label}</div>
                </StatCard>
              ))}
            </StatsGrid>
          </SidebarCard>

          <SidebarCard>
            <h3>Certifications</h3>
            <div style={{ textAlign: 'center', marginBottom: '1rem' }}>
              <FaAward style={{ fontSize: '3rem', color: '#D2691E', marginBottom: '1rem' }} />
              <p>
                <strong>Certification ISO 21500</strong><br />
                Gestion de projets de numérisation
              </p>
            </div>
            <div style={{ textAlign: 'center' }}>
              <FaBook style={{ fontSize: '3rem', color: '#8B4513', marginBottom: '1rem' }} />
              <p>
                <strong>Label "Patrimoine XXe siècle"</strong><br />
                Reconnaissance officielle
              </p>
            </div>
          </SidebarCard>

          <SidebarCard>
            <h3>Liens Utiles</h3>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
              <ActionButton>
                <FaDownload />
                Guide du chercheur
              </ActionButton>
              <ActionButton>
                <FaExternalLinkAlt />
                Portail national des archives
              </ActionButton>
              <ActionButton>
                <FaUsers />
                Associations partenaires
              </ActionButton>
            </div>
          </SidebarCard>
        </Sidebar>
      </AboutContent>
    </AboutContainer>
  );
};

export default AboutPage;
