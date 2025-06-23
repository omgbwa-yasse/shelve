import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { eventsApi } from '../../services/shelveApi';
import { useApi } from '../../hooks/useApi';
import Loading from '../common/Loading';
import ErrorMessage from '../common/ErrorMessage';
import { formatDate, formatDateTime } from '../../utils/dateUtils';
import { EVENT_TYPES } from '../../utils/constants';

const EventDetail = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [showFullDescription, setShowFullDescription] = useState(false);

  const {
    data: event,
    loading,
    error,
    refetch
  } = useApi(
    () => eventsApi.getEvent(id),
    [id]
  );

  const {
    data: relatedEvents,
    loading: loadingRelated
  } = useApi(
    () => event ? eventsApi.getEvents({
      type: event.type,
      limit: 3,
      exclude: event.id
    }) : null,
    [event]
  );

  useEffect(() => {
    if (error?.status === 404) {
      navigate('/events', { replace: true });
    }
  }, [error, navigate]);

  const handleShare = async () => {
    if (navigator.share) {
      try {
        await navigator.share({
          title: event.title,
          text: event.description?.substring(0, 100) + '...',
          url: window.location.href
        });
      } catch (err) {
        console.log('Erreur lors du partage:', err);
      }
    } else {
      // Fallback: copier l'URL
      navigator.clipboard.writeText(window.location.href).then(() => {
        alert('Lien copié dans le presse-papiers !');
      });
    }
  };

  const getTypeInfo = (type) => {
    const typeConfig = EVENT_TYPES.find(t => t.value === type);
    return typeConfig || { label: type, value: type };
  };

  if (loading) return <Loading />;
  if (error && error.status !== 404) {
    return <ErrorMessage message={error.message} onRetry={refetch} />;
  }
  if (!event) return null;

  const typeInfo = getTypeInfo(event.type);

  return (
    <div className="event-detail">
      <div className="container mx-auto px-4 py-8">
        {/* Breadcrumb */}
        <nav className="breadcrumb mb-6" aria-label="Fil d'Ariane">
          <ol className="flex items-center space-x-2 text-sm text-gray-600">
            <li>
              <Link to="/" className="hover:text-blue-600">Accueil</Link>
            </li>
            <li className="before:content-['/'] before:mx-2">
              <Link to="/events" className="hover:text-blue-600">Événements</Link>
            </li>
            <li className="before:content-['/'] before:mx-2 text-gray-900">
              {event.title}
            </li>
          </ol>
        </nav>

        <article className="event-article">
          {/* En-tête */}          <header className="event-header mb-8">
            <div className="flex flex-wrap items-center gap-3 mb-4">
              {(() => {
                const getTypeClasses = (type) => {
                  if (type === 'event') return 'bg-blue-100 text-blue-800';
                  if (type === 'news') return 'bg-green-100 text-green-800';
                  return 'bg-gray-100 text-gray-800';
                };

                return (
                  <span className={`inline-block px-3 py-1 text-sm font-semibold rounded-full ${getTypeClasses(event.type)}`}>
                    {typeInfo.label}
                  </span>
                );
              })()}

              <time
                dateTime={event.date || event.created_at}
                className="text-gray-600"
              >
                {formatDate(event.date || event.created_at)}
              </time>

              {event.location && (
                <span className="text-gray-600 flex items-center">
                  📍 {event.location}
                </span>
              )}
            </div>

            <h1 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              {event.title}
            </h1>

            {event.subtitle && (
              <p className="text-xl text-gray-600 mb-6">
                {event.subtitle}
              </p>
            )}

            {/* Actions */}
            <div className="flex flex-wrap gap-3">              <button
                onClick={handleShare}
                className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-2 focus:ring-blue-500"
                aria-label="Partager cet événement"
              >
                📤 Partager
              </button>

              <button
                onClick={() => navigate(-1)}
                className="flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                ← Retour
              </button>
            </div>
          </header>

          {/* Image principale */}
          {event.image_url && (
            <div className="event-image mb-8">
              <img
                src={event.image_url}
                alt={event.title}
                className="w-full h-64 md:h-96 object-cover rounded-lg shadow-md"
              />
            </div>
          )}

          {/* Informations de l'événement */}
          {(event.start_date || event.end_date || event.organizer) && (
            <div className="event-info bg-gray-50 rounded-lg p-6 mb-8">
              <h2 className="text-lg font-semibold text-gray-900 mb-4">
                Informations pratiques
              </h2>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {event.start_date && (
                  <div>
                    <strong className="text-gray-700">Début :</strong>
                    <div className="text-gray-600">
                      {formatDateTime(event.start_date)}
                    </div>
                  </div>
                )}

                {event.end_date && (
                  <div>
                    <strong className="text-gray-700">Fin :</strong>
                    <div className="text-gray-600">
                      {formatDateTime(event.end_date)}
                    </div>
                  </div>
                )}

                {event.organizer && (
                  <div>
                    <strong className="text-gray-700">Organisateur :</strong>
                    <div className="text-gray-600">{event.organizer}</div>
                  </div>
                )}

                {event.contact_email && (
                  <div>
                    <strong className="text-gray-700">Contact :</strong>
                    <div className="text-gray-600">
                      <a
                        href={`mailto:${event.contact_email}`}
                        className="text-blue-600 hover:text-blue-800"
                      >
                        {event.contact_email}
                      </a>
                    </div>
                  </div>
                )}
              </div>
            </div>
          )}

          {/* Contenu principal */}
          <div className="event-content prose prose-lg max-w-none mb-8">            {event.description && (
              <div className="description">
                {(() => {
                  const isLongDescription = event.description.length > 500;
                  const truncatedText = isLongDescription
                    ? event.description.substring(0, 500) + '...'
                    : event.description;

                  const displayText = showFullDescription
                    ? event.description
                    : truncatedText;

                  return (
                    <>
                      <div
                        className={`text-gray-700 leading-relaxed ${
                          !showFullDescription && isLongDescription
                            ? 'max-h-40 overflow-hidden'
                            : ''
                        }`}
                        dangerouslySetInnerHTML={{ __html: displayText }}
                      />

                      {isLongDescription && (
                        <button
                          onClick={() => setShowFullDescription(!showFullDescription)}
                          className="mt-4 text-blue-600 hover:text-blue-800 font-medium"
                        >
                          {showFullDescription ? 'Voir moins' : 'Lire la suite'}
                        </button>
                      )}
                    </>
                  );
                })()}
              </div>
            )}

            {event.content && (
              <div
                className="content mt-6"
                dangerouslySetInnerHTML={{ __html: event.content }}
              />
            )}
          </div>

          {/* Documents attachés */}
          {event.attachments && event.attachments.length > 0 && (
            <div className="event-attachments mb-8">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">
                Documents associés
              </h3>              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {event.attachments.map((attachment) => (
                  <a
                    key={attachment.id || attachment.name || attachment.url}
                    href={attachment.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                  >
                    <span className="text-2xl">📄</span>
                    <div>
                      <div className="font-medium text-gray-900">
                        {attachment.name}
                      </div>
                      {attachment.size && (
                        <div className="text-sm text-gray-500">
                          {attachment.size}
                        </div>
                      )}
                    </div>
                  </a>
                ))}
              </div>
            </div>
          )}

          {/* Tags */}
          {event.tags && event.tags.length > 0 && (
            <div className="event-tags mb-8">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">
                Mots-clés
              </h3>              <div className="flex flex-wrap gap-2">
                {event.tags.map((tag) => (
                  <span
                    key={tag}
                    className="inline-block px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded-full"
                  >
                    {tag}
                  </span>
                ))}
              </div>
            </div>
          )}
        </article>

        {/* Événements similaires */}
        {relatedEvents?.data && relatedEvents.data.length > 0 && (
          <section className="related-events mt-12">
            <h2 className="text-2xl font-bold text-gray-900 mb-6">
              Événements similaires
            </h2>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              {relatedEvents.data.map(relatedEvent => (
                <Link
                  key={relatedEvent.id}
                  to={`/events/${relatedEvent.id}`}
                  className="block bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow"
                >
                  {relatedEvent.image_url && (
                    <div className="h-32 overflow-hidden">
                      <img
                        src={relatedEvent.image_url}
                        alt={relatedEvent.title}
                        className="w-full h-full object-cover"
                      />
                    </div>
                  )}

                  <div className="p-4">
                    <div className="text-xs text-gray-500 mb-2">
                      {formatDate(relatedEvent.date || relatedEvent.created_at)}
                    </div>
                    <h3 className="font-semibold text-gray-900 line-clamp-2 mb-2">
                      {relatedEvent.title}
                    </h3>
                    {relatedEvent.description && (
                      <p className="text-sm text-gray-600 line-clamp-2">
                        {relatedEvent.description.substring(0, 100)}...
                      </p>
                    )}
                  </div>
                </Link>
              ))}
            </div>

            {!loadingRelated && (
              <div className="text-center mt-6">
                <Link
                  to="/events"
                  className="inline-block text-blue-600 hover:text-blue-800 font-medium"
                >
                  Voir tous les événements →
                </Link>
              </div>
            )}
          </section>
        )}
      </div>
    </div>
  );
};

export default EventDetail;
