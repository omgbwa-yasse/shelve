import React, { useState, useEffect, useCallback } from 'react';
import { toast } from 'react-toastify';
import {
  FaCalendarAlt,
  FaMapMarkerAlt,
  FaInfoCircle,
  FaTicketAlt,
  FaUsers
} from 'react-icons/fa';
import { eventsApi } from '../services/AllServices';
import {
  EventsContainer,
  EventsHeader,
  EventsGrid,
  EventCard,
  EventButton,
  FilterTabs,
  FilterTab,
  EventStatus,
  LoadingSpinner
} from '../styles/EventsStyles';

const EventsPage = () => {
  const [events, setEvents] = useState([]);
  const [loading, setLoading] = useState(false);
  const [activeFilter, setActiveFilter] = useState('all');

  const loadEvents = useCallback(async () => {
    setLoading(true);
    try {
      const response = await eventsApi.getEvents({
        status: activeFilter !== 'all' ? activeFilter : undefined,
        limit: 50
      });

      setEvents(response.data.data || response.data || []);
    } catch (error) {
      console.error('Error loading events:', error);
      toast.error('Erreur lors du chargement des événements');

      // Données de démonstration
      const mockEvents = [
        {
          id: 1,
          title: 'Exposition "Mémoires de la Grande Guerre"',
          date: '2024-03-15',
          time: '14:00',
          location: 'Salle d\'exposition des Archives',
          description: 'Découvrez une collection unique de documents, photographies et témoignages de la Première Guerre mondiale. Cette exposition présente des archives inédites provenant de familles locales et d\'institutions régionales.',
          status: 'upcoming',
          category: 'Exposition',
          capacity: 50,
          price: 'Gratuit'
        },
        {
          id: 2,
          title: 'Conférence : "L\'évolution urbaine au XIXe siècle"',
          date: '2024-03-22',
          time: '18:30',
          location: 'Amphithéâtre municipal',
          description: 'Le professeur Martin Dubois nous présentera ses recherches sur les transformations urbaines de notre région au XIXe siècle, basées sur l\'analyse des plans cadastraux et des archives municipales.',
          status: 'upcoming',
          category: 'Conférence',
          capacity: 100,
          price: 'Gratuit'
        },
        {
          id: 3,
          title: 'Atelier de paléographie - Niveau débutant',
          date: '2024-04-05',
          time: '10:00',
          location: 'Salle de formation',
          description: 'Apprenez les bases de la lecture des écritures anciennes. Cet atelier pratique vous permettra de déchiffrer des documents du XVIIe au XIXe siècle.',
          status: 'upcoming',
          category: 'Atelier',
          capacity: 15,
          price: '25€'
        },
        {
          id: 4,
          title: 'Journée portes ouvertes',
          date: '2024-04-12',
          time: '09:00',
          location: 'Ensemble du bâtiment',
          description: 'Découvrez les coulisses de nos archives ! Visite guidée des réserves, démonstrations de restauration et présentation des métiers du patrimoine.',
          status: 'upcoming',
          category: 'Visite',
          capacity: 200,
          price: 'Gratuit'
        },
        {
          id: 5,
          title: 'Présentation des nouvelles acquisitions',
          date: '2024-02-28',
          time: '16:00',
          location: 'Salle de lecture',
          description: 'Présentation des documents récemment acquis ou donnés aux archives, incluant des correspondances privées et des registres d\'entreprises locales.',
          status: 'past',
          category: 'Présentation',
          capacity: 30,
          price: 'Gratuit'
        },
        {
          id: 6,
          title: 'Colloque "Patrimoine numérique et archives"',
          date: '2024-05-20',
          time: '09:00',
          location: 'Centre de conférences',
          description: 'Journée d\'étude sur les enjeux de la numérisation du patrimoine archivistique. Interventions d\'experts nationaux et internationaux.',
          status: 'upcoming',
          category: 'Colloque',
          capacity: 150,
          price: '15€'
        }
      ];

      setEvents(mockEvents);
    } finally {
      setLoading(false);
    }
  }, [activeFilter]);

  useEffect(() => {
    loadEvents();
  }, [loadEvents]);

  const getEventStatus = (eventDate) => {
    const today = new Date();
    const eventDay = new Date(eventDate);
    
    if (eventDay > today) return 'upcoming';
    if (eventDay.toDateString() === today.toDateString()) return 'ongoing';
    return 'past';
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  const formatTime = (timeString) => {
    return timeString || '00:00';
  };

  const getStatusLabel = (status) => {
    switch (status) {
      case 'upcoming': return 'À venir';
      case 'ongoing': return 'En cours';
      case 'past': return 'Passé';
      default: return 'Inconnu';
    }
  };

  const filteredEvents = events.filter(event => {
    if (activeFilter === 'all') return true;
    const status = getEventStatus(event.date);
    return status === activeFilter;
  });

  return (
    <EventsContainer>
      <EventsHeader>
        <h1>Événements</h1>
        <p>
          Découvrez notre programmation culturelle et scientifique. Conférences, expositions, 
          ateliers et journées d'étude pour approfondir vos connaissances du patrimoine historique.
        </p>
      </EventsHeader>

      <FilterTabs>
        <FilterTab
          active={activeFilter === 'all'}
          onClick={() => setActiveFilter('all')}
        >
          Tous les événements
        </FilterTab>
        <FilterTab
          active={activeFilter === 'upcoming'}
          onClick={() => setActiveFilter('upcoming')}
        >
          À venir
        </FilterTab>
        <FilterTab
          active={activeFilter === 'ongoing'}
          onClick={() => setActiveFilter('ongoing')}
        >
          En cours
        </FilterTab>
        <FilterTab
          active={activeFilter === 'past'}
          onClick={() => setActiveFilter('past')}
        >
          Passés
        </FilterTab>
      </FilterTabs>

      {loading ? (
        <LoadingSpinner />
      ) : (
        <EventsGrid>
          {filteredEvents.map((event) => (
            <EventCard key={event.id}>
              <div className="event-date">
                <FaCalendarAlt />
                {formatDate(event.date)} à {formatTime(event.time)}
              </div>

              <div className="event-title">
                {event.title}
              </div>

              <div className="event-location">
                <FaMapMarkerAlt />
                {event.location}
              </div>

              <div className="event-description">
                {event.description}
              </div>

              <div style={{ display: 'flex', gap: '1rem', alignItems: 'center', marginBottom: '1rem' }}>
                <EventStatus status={getEventStatus(event.date)}>
                  {getStatusLabel(getEventStatus(event.date))}
                </EventStatus>
                <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', fontSize: '0.9rem' }}>
                  <FaUsers />
                  {event.capacity} places
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', fontSize: '0.9rem' }}>
                  <FaTicketAlt />
                  {event.price}
                </div>
              </div>

              <div className="event-actions">
                <EventButton variant="primary">
                  <FaInfoCircle />
                  En savoir plus
                </EventButton>
                {getEventStatus(event.date) === 'upcoming' && (
                  <EventButton>
                    <FaTicketAlt />
                    S'inscrire
                  </EventButton>
                )}
              </div>
            </EventCard>
          ))}
        </EventsGrid>
      )}

      {filteredEvents.length === 0 && !loading && (
        <div style={{ 
          textAlign: 'center', 
          padding: '3rem', 
          color: '#666' 
        }}>
          Aucun événement trouvé pour cette catégorie.
        </div>
      )}
    </EventsContainer>
  );
};

export default EventsPage;
