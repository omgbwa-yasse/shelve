import React from 'react';
import { Link } from 'react-router-dom';
import styled from 'styled-components';
import { usePaginatedApi } from '../../hooks/useApi';
import { eventsApi, newsApi } from '../../services/shelveApi';
import { SectionLoading } from '../common/Loading';
import ErrorMessageComponent from '../common/ErrorMessage';
import { formatDate } from '../../utils/dateUtils';
import { stringUtils } from '../../utils/helpers';

// Styled components
const HomePage = styled.div`
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
`;

const HeroSection = styled.section`
  background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
  color: white;
  padding: 4rem 2rem;
  border-radius: 12px;
  margin-bottom: 3rem;
  text-align: center;

  @media (max-width: 768px) {
    padding: 2rem 1rem;
    margin-bottom: 2rem;
  }
`;

const HeroTitle = styled.h1`
  font-size: 3rem;
  margin-bottom: 1rem;
  font-weight: 700;

  @media (max-width: 768px) {
    font-size: 2rem;
  }
`;

const HeroSubtitle = styled.p`
  font-size: 1.25rem;
  margin-bottom: 2rem;
  opacity: 0.9;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;

  @media (max-width: 768px) {
    font-size: 1rem;
  }
`;

const HeroActions = styled.div`
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
`;

const HeroButton = styled(Link)`
  padding: 1rem 2rem;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.2s ease;
  display: inline-block;

  ${props => props.variant === 'primary' ? `
    background: white;
    color: #007bff;

    &:hover {
      background: #f8f9fa;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
  ` : `
    background: transparent;
    color: white;
    border: 2px solid white;

    &:hover {
      background: white;
      color: #007bff;
    }
  `}
`;

const Section = styled.section`
  margin-bottom: 3rem;

  @media (max-width: 768px) {
    margin-bottom: 2rem;
  }
`;

const SectionHeader = styled.div`
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;

  @media (max-width: 768px) {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
`;

const SectionTitle = styled.h2`
  font-size: 2rem;
  color: #212529;
  margin: 0;

  @media (max-width: 768px) {
    font-size: 1.5rem;
  }
`;

const SectionLink = styled(Link)`
  color: #007bff;
  text-decoration: none;
  font-weight: 500;

  &:hover {
    text-decoration: underline;
  }
`;

const Grid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
`;

const Card = styled.div`
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  transition: all 0.2s ease;
  border: 1px solid #e9ecef;

  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
  }
`;

const CardTitle = styled.h3`
  font-size: 1.25rem;
  margin-bottom: 0.5rem;
  color: #212529;

  a {
    color: inherit;
    text-decoration: none;

    &:hover {
      color: #007bff;
    }
  }
`;

const CardDate = styled.div`
  color: #6c757d;
  font-size: 0.875rem;
  margin-bottom: 1rem;
`;

const CardDescription = styled.p`
  color: #495057;
  line-height: 1.6;
  margin-bottom: 1rem;
`;

const CardFooter = styled.div`
  display: flex;
  justify-content: space-between;
  align-items: center;

  @media (max-width: 768px) {
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-start;
  }
`;

const CardButton = styled(Link)`
  color: #007bff;
  text-decoration: none;
  font-weight: 500;
  font-size: 0.875rem;

  &:hover {
    text-decoration: underline;
  }
`;

const StatsSection = styled.section`
  background: #f8f9fa;
  border-radius: 12px;
  padding: 2rem;
  margin-bottom: 3rem;
`;

const StatsGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 2rem;

  @media (max-width: 768px) {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
`;

const StatCard = styled.div`
  text-align: center;
  padding: 1rem;
`;

const StatNumber = styled.div`
  font-size: 2.5rem;
  font-weight: 700;
  color: #007bff;
  margin-bottom: 0.5rem;
`;

const StatLabel = styled.div`
  color: #6c757d;
  font-size: 0.875rem;
  font-weight: 500;
`;

const HomePageComponent = () => {
  // Fetch recent events
  const {
    data: events,
    loading: eventsLoading,
    error: eventsError,
  } = usePaginatedApi(
    eventsApi.getEvents,
    { per_page: 6 },
    { immediate: true }
  );

  // Fetch recent news
  const {
    data: news,
    loading: newsLoading,
    error: newsError,
  } = usePaginatedApi(
    newsApi.getNews,
    { per_page: 6 },
    { immediate: true }
  );

  return (
    <HomePage>
      {/* Hero Section */}
      <HeroSection>
        <HeroTitle>Bienvenue sur Shelve Public</HeroTitle>
        <HeroSubtitle>
          Accédez aux archives publiques, consultez les événements,
          suivez les actualités et interagissez avec notre communauté.
        </HeroSubtitle>
        <HeroActions>
          <HeroButton to="/records" variant="primary">
            Explorer les archives
          </HeroButton>
          <HeroButton to="/events" variant="secondary">
            Voir les événements
          </HeroButton>
        </HeroActions>
      </HeroSection>

      {/* Statistics */}
      <StatsSection>
        <SectionTitle style={{ textAlign: 'center', marginBottom: '2rem' }}>
          Shelve en chiffres
        </SectionTitle>
        <StatsGrid>
          <StatCard>
            <StatNumber>1,234</StatNumber>
            <StatLabel>Documents publics</StatLabel>
          </StatCard>
          <StatCard>
            <StatNumber>56</StatNumber>
            <StatLabel>Événements organisés</StatLabel>
          </StatCard>
          <StatCard>
            <StatNumber>789</StatNumber>
            <StatLabel>Utilisateurs actifs</StatLabel>
          </StatCard>
          <StatCard>
            <StatNumber>2,100</StatNumber>
            <StatLabel>Recherches effectuées</StatLabel>
          </StatCard>
        </StatsGrid>
      </StatsSection>

      {/* Recent Events */}
      <Section>
        <SectionHeader>
          <SectionTitle>Événements à venir</SectionTitle>
          <SectionLink to="/events">Voir tous les événements →</SectionLink>
        </SectionHeader>

        {eventsLoading ? (
          <SectionLoading text="Chargement des événements..." />
        ) : eventsError ? (
          <ErrorMessageComponent
            message={eventsError}
            variant="warning"
          />
        ) : (
          <Grid>
            {events.slice(0, 3).map(event => (
              <Card key={event.id}>
                <CardTitle>
                  <Link to={`/events/${event.id}`}>
                    {event.title}
                  </Link>
                </CardTitle>
                <CardDate>
                  📅 {formatDate(event.start_date)}
                  {event.location && ` • 📍 ${event.location}`}
                </CardDate>
                <CardDescription>
                  {stringUtils.truncate(event.description, 120)}
                </CardDescription>
                <CardFooter>
                  <span style={{ color: '#28a745', fontSize: '0.875rem' }}>
                    {event.available_spots ?
                      `${event.available_spots} places disponibles` :
                      'Places limitées'
                    }
                  </span>
                  <CardButton to={`/events/${event.id}`}>
                    En savoir plus
                  </CardButton>
                </CardFooter>
              </Card>
            ))}
          </Grid>
        )}
      </Section>

      {/* Recent News */}
      <Section>
        <SectionHeader>
          <SectionTitle>Dernières actualités</SectionTitle>
          <SectionLink to="/news">Voir toutes les actualités →</SectionLink>
        </SectionHeader>

        {newsLoading ? (
          <SectionLoading text="Chargement des actualités..." />
        ) : newsError ? (
          <ErrorMessageComponent
            message={newsError}
            variant="warning"
          />
        ) : (
          <Grid>
            {news.slice(0, 3).map(article => (
              <Card key={article.id}>
                <CardTitle>
                  <Link to={`/news/${article.id}`}>
                    {article.title}
                  </Link>
                </CardTitle>
                <CardDate>
                  📰 {formatDate(article.published_at)}
                  {article.category && ` • ${article.category}`}
                </CardDate>
                <CardDescription>
                  {stringUtils.truncate(article.excerpt || article.content, 120)}
                </CardDescription>
                <CardFooter>
                  <span style={{ color: '#6c757d', fontSize: '0.875rem' }}>
                    {article.author && `Par ${article.author}`}
                  </span>
                  <CardButton to={`/news/${article.id}`}>
                    Lire la suite
                  </CardButton>
                </CardFooter>
              </Card>
            ))}
          </Grid>
        )}
      </Section>

      {/* Quick Actions */}
      <Section>
        <SectionTitle>Actions rapides</SectionTitle>
        <Grid>
          <Card>
            <CardTitle>
              🔍 Recherche avancée
            </CardTitle>
            <CardDescription>
              Utilisez notre moteur de recherche pour trouver rapidement
              les documents et informations dont vous avez besoin.
            </CardDescription>
            <CardButton to="/records">
              Rechercher →
            </CardButton>
          </Card>

          <Card>
            <CardTitle>
              📄 Demande de documents
            </CardTitle>
            <CardDescription>
              Soumettez une demande pour obtenir des documents spécifiques
              ou des informations détaillées.
            </CardDescription>
            <CardButton to="/documents/request">
              Faire une demande →
            </CardButton>
          </Card>

          <Card>
            <CardTitle>
              💬 Nous contacter
            </CardTitle>
            <CardDescription>
              Vous avez des questions ou des suggestions ?
              N'hésitez pas à nous faire part de vos commentaires.
            </CardDescription>
            <CardButton to="/feedback">
              Nous contacter →
            </CardButton>
          </Card>
        </Grid>
      </Section>
    </HomePage>
  );
};

export default HomePageComponent;
