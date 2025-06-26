import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { recordsApi } from '../../services/shelveApi';
import { useApi } from '../../hooks/useApi';
import Loading from '../common/Loading';
import ErrorMessage from '../common/ErrorMessage';
import { formatDate } from '../../utils/dateUtils';
import { formatFileSize } from '../../utils/helpers';
import { RECORD_TYPES } from '../../utils/constants';

const RecordDetail = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [showFullDescription, setShowFullDescription] = useState(false);
  const [selectedImageIndex, setSelectedImageIndex] = useState(0);
  const [relatedRecords, setRelatedRecords] = useState(null);
  const [loadingRelated, setLoadingRelated] = useState(false);

  // Premier appel API pour récupérer le record
  const {
    data: record,
    loading,
    error,
    refetch
  } = useApi(() => recordsApi.getRecord(id));

  // Effet pour charger les records similaires quand le record principal est chargé
  useEffect(() => {
    if (record?.id && record?.type) {
      setLoadingRelated(true);
      recordsApi.getRecords({
        type: record.type,
        limit: 4,
        exclude: record.id
      })
      .then(response => {
        setRelatedRecords(response.data);
      })
      .catch(error => {
        console.error('Erreur lors du chargement des records similaires:', error);
        setRelatedRecords(null);
      })
      .finally(() => {
        setLoadingRelated(false);
      });
    }
  }, [record?.id, record?.type]);

  useEffect(() => {
    if (error?.status === 404) {
      navigate('/records', { replace: true });
    }
  }, [error, navigate]);

  const handleShare = async () => {
    if (navigator.share) {
      try {
        await navigator.share({
          title: record.title,
          text: record.description?.substring(0, 100) + '...',
          url: window.location.href
        });
      } catch (err) {
        console.log('Erreur lors du partage:', err);
      }
    } else {
      navigator.clipboard.writeText(window.location.href).then(() => {
        alert('Lien copié dans le presse-papiers !');
      });
    }
  };

  const handleDownload = (fileUrl, fileName) => {
    const link = document.createElement('a');
    link.href = fileUrl;
    link.download = fileName;
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  const getTypeInfo = (type) => {
    const typeConfig = RECORD_TYPES.find(t => t.value === type);
    return typeConfig || { label: type, value: type };
  };

  if (loading) return <Loading />;
  if (error && error.status !== 404) {
    return <ErrorMessage message={error.message} onRetry={refetch} />;
  }
  if (!record) return null;

  const typeInfo = getTypeInfo(record.type);
  const images = record.images || [];
  const hasMultipleImages = images.length > 1;

  return (
    <div className="record-detail">
      <div className="container mx-auto px-4 py-8">
        {/* Breadcrumb */}
        <nav className="breadcrumb mb-6" aria-label="Fil d'Ariane">
          <ol className="flex items-center space-x-2 text-sm text-gray-600">
            <li>
              <Link to="/" className="hover:text-blue-600">Accueil</Link>
            </li>
            <li className="before:content-['/'] before:mx-2">
              <Link to="/records" className="hover:text-blue-600">Archives</Link>
            </li>
            <li className="before:content-['/'] before:mx-2 text-gray-900">
              {record.title}
            </li>
          </ol>
        </nav>

        <article className="record-article">
          {/* En-tête */}
          <header className="record-header mb-8">
            <div className="flex flex-wrap items-center gap-3 mb-4">
              <span className="inline-block px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                {typeInfo.label}
              </span>

              {record.reference && (
                <span className="text-gray-600 font-mono text-sm">
                  Réf: {record.reference}
                </span>
              )}

              {record.date && (
                <time
                  dateTime={record.date}
                  className="text-gray-600"
                >
                  {formatDate(record.date)}
                </time>
              )}

              {record.digital_copy_available && (
                <span className="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                  Copie numérique disponible
                </span>
              )}
            </div>

            <h1 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              {record.title}
            </h1>

            {record.subtitle && (
              <p className="text-xl text-gray-600 mb-6">
                {record.subtitle}
              </p>
            )}

            {/* Actions */}
            <div className="flex flex-wrap gap-3">
              <button
                onClick={handleShare}
                className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                aria-label="Partager ce document"
              >
                📤 Partager
              </button>

              <button
                onClick={() => navigate(-1)}
                className="flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                ← Retour
              </button>

              <Link
                to="/document-request"
                state={{ recordId: record.id, recordTitle: record.title }}
                className="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
              >
                📋 Demander une consultation
              </Link>
            </div>
          </header>

          {/* Images/Visionneuse */}
          {images.length > 0 && (
            <div className="record-viewer mb-8">
              <div className="main-image mb-4">                <img
                  src={images[selectedImageIndex]?.url || images[0]?.url}
                  alt={`${record.title} - Vue ${selectedImageIndex + 1}`}
                  className="w-full max-h-96 object-contain bg-gray-50 rounded-lg shadow-md"
                />
              </div>

              {hasMultipleImages && (
                <div className="image-thumbnails">
                  <div className="flex gap-2 overflow-x-auto pb-2">
                    {images.map((image, index) => (
                      <button
                        key={image.id || index}
                        onClick={() => setSelectedImageIndex(index)}
                        className={`flex-shrink-0 w-16 h-16 rounded border-2 overflow-hidden ${
                          index === selectedImageIndex
                            ? 'border-blue-500'
                            : 'border-gray-200 hover:border-gray-300'
                        }`}
                      >
                        <img
                          src={image.thumbnail_url || image.url}
                          alt={`Miniature ${index + 1}`}
                          className="w-full h-full object-cover"
                        />
                      </button>
                    ))}
                  </div>
                  <p className="text-sm text-gray-500 mt-2">
                    Image {selectedImageIndex + 1} sur {images.length}
                  </p>
                </div>
              )}
            </div>
          )}

          {/* Informations détaillées */}
          <div className="record-info grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <div className="lg:col-span-2">
              {/* Description */}
              {record.description && (
                <div className="description mb-6">
                  <h2 className="text-xl font-semibold text-gray-900 mb-3">
                    Description
                  </h2>
                  <div className="prose max-w-none">
                    {(() => {
                      const isLongDescription = record.description.length > 500;
                      const truncatedText = isLongDescription
                        ? record.description.substring(0, 500) + '...'
                        : record.description;

                      const displayText = showFullDescription
                        ? record.description
                        : truncatedText;

                      return (
                        <>
                          <div
                            className="text-gray-700 leading-relaxed"
                            dangerouslySetInnerHTML={{ __html: displayText }}
                          />

                          {isLongDescription && (
                            <button
                              onClick={() => setShowFullDescription(!showFullDescription)}
                              className="mt-3 text-blue-600 hover:text-blue-800 font-medium"
                            >
                              {showFullDescription ? 'Voir moins' : 'Lire la suite'}
                            </button>
                          )}
                        </>
                      );
                    })()}
                  </div>
                </div>
              )}

              {/* Contenu étendu */}
              {record.content && (
                <div className="content mb-6">
                  <h2 className="text-xl font-semibold text-gray-900 mb-3">
                    Contenu détaillé
                  </h2>
                  <div
                    className="prose max-w-none"
                    dangerouslySetInnerHTML={{ __html: record.content }}
                  />
                </div>
              )}
            </div>

            {/* Sidebar avec métadonnées */}
            <div className="lg:col-span-1">
              <div className="bg-gray-50 rounded-lg p-6 sticky top-8">
                <h3 className="text-lg font-semibold text-gray-900 mb-4">
                  Informations techniques
                </h3>

                <dl className="space-y-4">
                  {record.classification && (
                    <div>
                      <dt className="text-sm font-medium text-gray-700">Classification</dt>
                      <dd className="text-sm text-gray-900 font-mono">{record.classification}</dd>
                    </div>
                  )}

                  {record.series && (
                    <div>
                      <dt className="text-sm font-medium text-gray-700">Série</dt>
                      <dd className="text-sm text-gray-900">{record.series}</dd>
                    </div>
                  )}

                  {record.date_range && (
                    <div>
                      <dt className="text-sm font-medium text-gray-700">Période</dt>
                      <dd className="text-sm text-gray-900">{record.date_range}</dd>
                    </div>
                  )}

                  {record.location && (
                    <div>
                      <dt className="text-sm font-medium text-gray-700">Localisation</dt>
                      <dd className="text-sm text-gray-900">{record.location}</dd>
                    </div>
                  )}

                  {record.format && (
                    <div>
                      <dt className="text-sm font-medium text-gray-700">Format</dt>
                      <dd className="text-sm text-gray-900">{record.format}</dd>
                    </div>
                  )}

                  {record.dimensions && (
                    <div>
                      <dt className="text-sm font-medium text-gray-700">Dimensions</dt>
                      <dd className="text-sm text-gray-900">{record.dimensions}</dd>
                    </div>
                  )}

                  {record.language && (
                    <div>
                      <dt className="text-sm font-medium text-gray-700">Langue</dt>
                      <dd className="text-sm text-gray-900">{record.language}</dd>
                    </div>
                  )}

                  <div>
                    <dt className="text-sm font-medium text-gray-700">Dernière mise à jour</dt>
                    <dd className="text-sm text-gray-900">{formatDate(record.updated_at)}</dd>
                  </div>
                </dl>

                {/* Conditions d'accès */}
                {record.access_conditions && (
                  <div className="mt-6 p-4 bg-yellow-50 rounded-lg">
                    <h4 className="text-sm font-medium text-yellow-800 mb-2">
                      Conditions d'accès
                    </h4>
                    <p className="text-sm text-yellow-700">
                      {record.access_conditions}
                    </p>
                  </div>
                )}
              </div>
            </div>
          </div>

          {/* Fichiers attachés */}
          {record.attachments && record.attachments.length > 0 && (
            <div className="record-attachments mb-8">
              <h3 className="text-xl font-semibold text-gray-900 mb-4">
                Fichiers disponibles
              </h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {record.attachments.map((attachment) => (
                  <div
                    key={attachment.id || attachment.name}
                    className="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
                  >
                    <div className="flex items-center gap-3">
                      <span className="text-2xl">📄</span>
                      <div>
                        <div className="font-medium text-gray-900">
                          {attachment.name}
                        </div>
                        {attachment.size && (
                          <div className="text-sm text-gray-500">
                            {formatFileSize(attachment.size)}
                          </div>
                        )}
                      </div>
                    </div>

                    <div className="flex gap-2">
                      <a
                        href={attachment.url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                      >
                        Voir
                      </a>

                      {attachment.downloadable && (
                        <button
                          onClick={() => handleDownload(attachment.url, attachment.name)}
                          className="text-green-600 hover:text-green-800 text-sm font-medium"
                        >
                          Télécharger
                        </button>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Mots-clés */}
          {record.tags && record.tags.length > 0 && (
            <div className="record-tags mb-8">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">
                Mots-clés
              </h3>
              <div className="flex flex-wrap gap-2">
                {record.tags.map((tag) => (
                  <Link
                    key={tag}
                    to={`/records?search=${encodeURIComponent(tag)}`}
                    className="inline-block px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded-full hover:bg-gray-300 transition-colors"
                  >
                    {tag}
                  </Link>
                ))}
              </div>
            </div>
          )}
        </article>

        {/* Documents similaires */}
        {relatedRecords?.data && relatedRecords.data.length > 0 && (
          <section className="related-records mt-12">
            <h2 className="text-2xl font-bold text-gray-900 mb-6">
              Documents similaires
            </h2>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              {relatedRecords.data.map(relatedRecord => (
                <Link
                  key={relatedRecord.id}
                  to={`/records/${relatedRecord.id}`}
                  className="block bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow"
                >
                  {relatedRecord.thumbnail_url && (
                    <div className="h-32 overflow-hidden">
                      <img
                        src={relatedRecord.thumbnail_url}
                        alt={relatedRecord.title}
                        className="w-full h-full object-cover"
                      />
                    </div>
                  )}

                  <div className="p-4">
                    {relatedRecord.reference && (
                      <div className="text-xs text-gray-500 font-mono mb-1">
                        {relatedRecord.reference}
                      </div>
                    )}
                    <h3 className="font-semibold text-gray-900 text-sm line-clamp-2 mb-2">
                      {relatedRecord.title}
                    </h3>
                    <div className="text-xs text-gray-500">
                      {formatDate(relatedRecord.date || relatedRecord.created_at)}
                    </div>
                  </div>
                </Link>
              ))}
            </div>

            {!loadingRelated && (
              <div className="text-center mt-6">
                <Link
                  to="/records"
                  className="inline-block text-blue-600 hover:text-blue-800 font-medium"
                >
                  Voir tous les documents →
                </Link>
              </div>
            )}
          </section>
        )}
      </div>
    </div>
  );
};

export default RecordDetail;
