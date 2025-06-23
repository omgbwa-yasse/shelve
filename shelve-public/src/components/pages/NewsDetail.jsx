import React, { useState, useEffect } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { useQuery } from 'react-query';
import { toast } from 'react-toastify';
import shelveApi from '../../services/shelveApi';
import { formatDate } from '../../utils/dateUtils';
import Loading from '../common/Loading';
import ErrorMessage from '../common/ErrorMessage';
import { Button } from '../forms/FormComponents';

const NewsDetail = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [relatedNews, setRelatedNews] = useState([]);

  // Fetch news article
  const {
    data: article,
    isLoading,
    error,
    refetch
  } = useQuery(
    ['news', id],
    () => shelveApi.getNewsById(id),
    {
      enabled: !!id,
      onError: (error) => {
        console.error('Error fetching news article:', error);
        if (error.response?.status === 404) {
          toast.error('Article non trouvé');
          navigate('/news');
        } else {
          toast.error('Erreur lors du chargement de l\'article');
        }
      }
    }
  );

  // Fetch related news
  useQuery(
    ['related-news', id, article?.category?.id],
    () => shelveApi.getNews({
      category: article?.category?.id,
      exclude: id,
      per_page: 3
    }),
    {
      enabled: !!article?.category?.id,
      onSuccess: (data) => {
        setRelatedNews(data?.data || []);
      }
    }
  );

  // Update page title
  useEffect(() => {
    if (article) {
      document.title = `${article.title} - Actualités - Shelve`;

      // Add structured data
      const structuredData = {
        "@context": "https://schema.org",
        "@type": "NewsArticle",
        "headline": article.title,
        "description": article.excerpt,
        "author": {
          "@type": "Organization",
          "name": "Shelve"
        },
        "publisher": {
          "@type": "Organization",
          "name": "Shelve"
        },
        "datePublished": article.published_at,
        "dateModified": article.updated_at,
        "image": article.image,
        "mainEntityOfPage": {
          "@type": "WebPage",
          "@id": window.location.href
        }
      };

      const script = document.createElement('script');
      script.type = 'application/ld+json';
      script.text = JSON.stringify(structuredData);
      document.head.appendChild(script);

      return () => {
        document.title = 'Shelve';
        document.head.removeChild(script);
      };
    }
  }, [article]);

  if (isLoading) {
    return <Loading size="large" message="Chargement de l'article..." />;
  }

  if (error) {
    return (
      <ErrorMessage
        title="Erreur de chargement"
        message="Impossible de charger l'article. Veuillez réessayer."
        onRetry={refetch}
        variant="error"
      />
    );
  }

  if (!article) {
    return (
      <ErrorMessage
        title="Article non trouvé"
        message="L'article que vous recherchez n'existe pas."
        variant="warning"
        size="large"
      />
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="container mx-auto px-4 py-8">
        {/* Breadcrumb */}
        <nav className="flex items-center space-x-2 text-sm text-gray-600 mb-8" aria-label="Fil d'ariane">
          <Link to="/" className="hover:text-blue-600 transition-colors">
            Accueil
          </Link>
          <span>/</span>
          <Link to="/news" className="hover:text-blue-600 transition-colors">
            Actualités
          </Link>
          <span>/</span>
          <span className="text-gray-900 font-medium">{article.title}</span>
        </nav>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Main content */}
          <article className="lg:col-span-2">
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
              {/* Header */}
              <div className="p-8 pb-6">
                {/* Category */}
                {article.category && (
                  <span className="inline-block px-3 py-1 text-sm font-medium text-blue-800 bg-blue-100 rounded-full mb-4">
                    {article.category.name}
                  </span>
                )}

                {/* Title */}
                <h1 className="text-3xl lg:text-4xl font-bold text-gray-900 mb-4 leading-tight">
                  {article.title}
                </h1>

                {/* Excerpt */}
                {article.excerpt && (
                  <p className="text-lg text-gray-600 mb-6 leading-relaxed">
                    {article.excerpt}
                  </p>
                )}

                {/* Meta */}
                <div className="flex flex-wrap items-center gap-4 text-sm text-gray-500 border-b border-gray-200 pb-6">
                  <time dateTime={article.published_at} className="flex items-center">
                    <svg className="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {formatDate(article.published_at)}
                  </time>

                  {article.read_time && (
                    <span className="flex items-center">
                      <svg className="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      {article.read_time} min de lecture
                    </span>
                  )}

                  {article.author && (
                    <span className="flex items-center">
                      <svg className="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                      {article.author}
                    </span>
                  )}
                </div>
              </div>

              {/* Featured image */}
              {article.image && (
                <div className="mb-8">
                  <img
                    src={article.image}
                    alt={article.image_alt || ''}
                    className="w-full h-64 md:h-96 object-cover"
                  />
                  {article.image_caption && (
                    <p className="text-sm text-gray-600 mt-2 px-8 italic">
                      {article.image_caption}
                    </p>
                  )}
                </div>
              )}

              {/* Content */}
              <div className="px-8 pb-8">
                <div
                  className="prose prose-lg max-w-none prose-blue prose-headings:text-gray-900 prose-a:text-blue-600 hover:prose-a:text-blue-700"
                  dangerouslySetInnerHTML={{ __html: article.content }}
                />

                {/* Tags */}
                {article.tags && article.tags.length > 0 && (
                  <div className="mt-8 pt-6 border-t border-gray-200">
                    <h3 className="text-sm font-medium text-gray-900 mb-2">Tags:</h3>
                    <div className="flex flex-wrap gap-2">
                      {article.tags.map((tag, index) => (
                        <span
                          key={index}
                          className="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-md"
                        >
                          #{tag}
                        </span>
                      ))}
                    </div>
                  </div>
                )}

                {/* Share buttons */}
                <div className="mt-8 pt-6 border-t border-gray-200">
                  <h3 className="text-sm font-medium text-gray-900 mb-4">Partager cet article:</h3>
                  <div className="flex space-x-3">
                    <Button
                      onClick={() => {
                        const url = encodeURIComponent(window.location.href);
                        const text = encodeURIComponent(article.title);
                        window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank');
                      }}
                      variant="outline"
                      size="sm"
                      className="flex items-center"
                      aria-label="Partager sur Twitter"
                    >
                      <svg className="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                      </svg>
                      Twitter
                    </Button>

                    <Button
                      onClick={() => {
                        const url = encodeURIComponent(window.location.href);
                        window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
                      }}
                      variant="outline"
                      size="sm"
                      className="flex items-center"
                      aria-label="Partager sur Facebook"
                    >
                      <svg className="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                      </svg>
                      Facebook
                    </Button>

                    <Button
                      onClick={() => {
                        navigator.clipboard.writeText(window.location.href);
                        toast.success('Lien copié dans le presse-papiers');
                      }}
                      variant="outline"
                      size="sm"
                      className="flex items-center"
                      aria-label="Copier le lien"
                    >
                      <svg className="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                      </svg>
                      Copier
                    </Button>
                  </div>
                </div>
              </div>
            </div>
          </article>

          {/* Sidebar */}
          <aside className="lg:col-span-1">
            {/* Navigation */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
              <div className="flex space-x-3">
                <Button
                  onClick={() => navigate('/news')}
                  variant="outline"
                  className="flex-1"
                  aria-label="Retour aux actualités"
                >
                  ← Retour
                </Button>

                <Button
                  onClick={() => window.print()}
                  variant="outline"
                  className="flex items-center"
                  aria-label="Imprimer l'article"
                >
                  <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                  </svg>
                </Button>
              </div>
            </div>

            {/* Related articles */}
            {relatedNews.length > 0 && (
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 className="text-lg font-semibold text-gray-900 mb-4">
                  Articles similaires
                </h3>
                <div className="space-y-4">
                  {relatedNews.map((related) => (
                    <Link
                      key={related.id}
                      to={`/news/${related.id}`}
                      className="block group"
                      aria-label={`Lire l'article: ${related.title}`}
                    >
                      <div className="flex space-x-3">
                        {related.image && (
                          <img
                            src={related.image}
                            alt=""
                            className="w-16 h-16 object-cover rounded-md flex-shrink-0"
                            loading="lazy"
                          />
                        )}
                        <div className="flex-1 min-w-0">
                          <h4 className="text-sm font-medium text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-2 mb-1">
                            {related.title}
                          </h4>
                          <time dateTime={related.published_at} className="text-xs text-gray-500">
                            {formatDate(related.published_at)}
                          </time>
                        </div>
                      </div>
                    </Link>
                  ))}
                </div>
              </div>
            )}
          </aside>
        </div>
      </div>
    </div>
  );
};

export default NewsDetail;
