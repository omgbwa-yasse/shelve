import React, { useState, useEffect, useCallback } from 'react';
import { toast } from 'react-toastify';
import {
  FaCalendarAlt,
  FaUser,
  FaEye,
  FaArrowRight,
  FaTag,
  FaClock
} from 'react-icons/fa';
import { newsApi } from '../services/AllServices';
import {
  NewsContainer,
  NewsGrid,
  MainNewsSection,
  NewsCard,
  NewsButton,
  SidebarNews,
  SidebarSection,
  RecentNewsItem,
  CategoryTag,
  NewsCategories,
  CategoryFilter,
  LoadingSpinner
} from '../styles/NewsStyles';

const NewsPage = () => {
  const [news, setNews] = useState([]);
  const [recentNews, setRecentNews] = useState([]);
  const [loading, setLoading] = useState(false);
  const [activeCategory, setActiveCategory] = useState('all');

  const loadNews = useCallback(async () => {
    setLoading(true);
    try {
      const response = await newsApi.getNews({
        category: activeCategory !== 'all' ? activeCategory : undefined,
        limit: 20
      });

      setNews(response.data.data || response.data || []);
      setRecentNews((response.data.data || response.data || []).slice(0, 5));
    } catch (error) {
      console.error('Error loading news:', error);
      toast.error('Erreur lors du chargement des actualités');

      // Données de démonstration
      const mockNews = [
        {
          id: 1,
          title: 'Numérisation de 500 nouveaux documents historiques',
          excerpt: 'Notre équipe a achevé la numérisation d\'une importante collection de registres paroissiaux du XVIIIe siècle. Ces documents sont désormais consultables en ligne.',
          content: 'La numérisation de cette collection représente plusieurs mois de travail minutieux. Chaque document a été photographié en haute résolution et indexé pour faciliter les recherches. Cette initiative s\'inscrit dans notre programme de préservation numérique du patrimoine.',
          date: '2024-03-10',
          author: 'Marie Dubois',
          category: 'Numérisation',
          views: 156,
          image: null
        },
        {
          id: 2,
          title: 'Don exceptionnel de la famille Moreau',
          excerpt: 'La famille Moreau a généreusement fait don de plus de 200 documents privés datant du XIXe siècle, incluant correspondances, actes notariés et photographies.',
          content: 'Ce don exceptionnel enrichit considérablement nos collections. Les documents témoignent de la vie quotidienne d\'une famille bourgeoise au XIXe siècle et apportent un éclairage unique sur l\'histoire locale.',
          date: '2024-03-05',
          author: 'Jean-Pierre Martin',
          category: 'Acquisitions',
          views: 89,
          image: null
        },
        {
          id: 3,
          title: 'Nouveau système de recherche en ligne',
          excerpt: 'Un nouveau moteur de recherche plus performant est maintenant disponible sur notre site web, permettant des recherches plus précises dans nos collections.',
          content: 'Ce système utilise l\'intelligence artificielle pour améliorer la pertinence des résultats de recherche. Les utilisateurs peuvent désormais effectuer des recherches par mots-clés, dates, types de documents et bien plus encore.',
          date: '2024-02-28',
          author: 'Sophie Blanc',
          category: 'Innovation',
          views: 234,
          image: null
        },
        {
          id: 4,
          title: 'Restauration d\'un manuscrit du XVe siècle',
          excerpt: 'Notre atelier de restauration a terminé la restauration d\'un précieux manuscrit enluminé du XVe siècle, qui sera bientôt exposé au public.',
          content: 'Cette restauration complexe a nécessité 6 mois de travail. Le manuscrit, gravement endommagé par l\'humidité, a retrouvé son éclat d\'origine grâce aux techniques de restauration les plus modernes.',
          date: '2024-02-20',
          author: 'Alain Rousseau',
          category: 'Restauration',
          views: 178,
          image: null
        },
        {
          id: 5,
          title: 'Formation du personnel aux nouvelles technologies',
          excerpt: 'Notre équipe suit une formation spécialisée sur l\'utilisation des dernières technologies de numérisation et de conservation préventive.',
          content: 'Cette formation s\'étend sur trois semaines et couvre les aspects techniques, éthiques et pratiques de la numérisation patrimoniale. Elle permettra d\'améliorer encore la qualité de nos services.',
          date: '2024-02-15',
          author: 'Catherine Leroy',
          category: 'Formation',
          views: 92,
          image: null
        },
        {
          id: 6,
          title: 'Partenariat avec l\'Université locale',
          excerpt: 'Signature d\'un accord de coopération avec l\'Université pour faciliter l\'accès aux archives dans le cadre de projets de recherche étudiants.',
          content: 'Ce partenariat permettra aux étudiants en histoire et en patrimoine d\'accéder plus facilement à nos collections pour leurs travaux de recherche. Des séances de formation spécifiques seront organisées.',
          date: '2024-02-10',
          author: 'Marc Durand',
          category: 'Partenariat',
          views: 145,
          image: null
        }
      ];

      setNews(mockNews);
      setRecentNews(mockNews.slice(0, 5));
    } finally {
      setLoading(false);
    }
  }, [activeCategory]);

  useEffect(() => {
    loadNews();
  }, [loadNews]);

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  const categories = [
    { id: 'all', label: 'Toutes', count: news.length },
    { id: 'numérisation', label: 'Numérisation', count: news.filter(n => n.category === 'Numérisation').length },
    { id: 'acquisitions', label: 'Acquisitions', count: news.filter(n => n.category === 'Acquisitions').length },
    { id: 'restauration', label: 'Restauration', count: news.filter(n => n.category === 'Restauration').length },
    { id: 'innovation', label: 'Innovation', count: news.filter(n => n.category === 'Innovation').length },
    { id: 'formation', label: 'Formation', count: news.filter(n => n.category === 'Formation').length },
    { id: 'partenariat', label: 'Partenariat', count: news.filter(n => n.category === 'Partenariat').length }
  ];

  const filteredNews = activeCategory === 'all'
    ? news
    : news.filter(article => article.category.toLowerCase() === activeCategory.toLowerCase());

  return (
    <NewsContainer>
      <NewsCategories>
        {categories.map(category => (
          <CategoryFilter
            key={category.id}
            active={activeCategory === category.id}
            onClick={() => setActiveCategory(category.id)}
          >
            {category.label} ({category.count})
          </CategoryFilter>
        ))}
      </NewsCategories>

      {loading ? (
        <LoadingSpinner />
      ) : (
        <NewsGrid>
          <MainNewsSection>
            {filteredNews.map((article) => (
              <NewsCard key={article.id}>
                <div className="news-image">
                  📰
                </div>
                <div className="news-content">
                  <div className="news-meta">
                    <span>
                      <FaCalendarAlt />
                      {formatDate(article.date)}
                    </span>
                    <span>
                      <FaUser />
                      {article.author}
                    </span>
                    <span>
                      <FaEye />
                      {article.views} vues
                    </span>
                  </div>

                  <h2 className="news-title">
                    {article.title}
                  </h2>

                  <p className="news-excerpt">
                    {article.excerpt}
                  </p>

                  <div className="news-footer">
                    <CategoryTag>
                      <FaTag />
                      {article.category}
                    </CategoryTag>
                    <NewsButton variant="primary">
                      Lire la suite
                      <FaArrowRight />
                    </NewsButton>
                  </div>
                </div>
              </NewsCard>
            ))}
          </MainNewsSection>

          <SidebarNews>
            <SidebarSection>
              <h3>Actualités récentes</h3>
              {recentNews.map((article) => (
                <RecentNewsItem key={article.id}>
                  <div className="recent-title">
                    {article.title}
                  </div>
                  <div className="recent-date">
                    <FaClock />
                    {formatDate(article.date)}
                  </div>
                </RecentNewsItem>
              ))}
            </SidebarSection>

            <SidebarSection>
              <h3>Catégories</h3>
              {categories.filter(cat => cat.id !== 'all').map(category => (
                <div key={category.id} style={{
                  display: 'flex',
                  justifyContent: 'space-between',
                  alignItems: 'center',
                  padding: '0.5rem 0',
                  borderBottom: '1px solid #eee'
                }}>
                  <span>{category.label}</span>
                  <span style={{
                    background: '#f8f9fa',
                    padding: '0.25rem 0.5rem',
                    borderRadius: '12px',
                    fontSize: '0.8rem'
                  }}>
                    {category.count}
                  </span>
                </div>
              ))}
            </SidebarSection>

            <SidebarSection>
              <h3>Archives mensuelles</h3>
              <div style={{ color: '#666', fontSize: '0.9rem' }}>
                <div style={{ marginBottom: '0.5rem' }}>Mars 2024 (3)</div>
                <div style={{ marginBottom: '0.5rem' }}>Février 2024 (3)</div>
                <div style={{ marginBottom: '0.5rem' }}>Janvier 2024 (2)</div>
                <div style={{ marginBottom: '0.5rem' }}>Décembre 2023 (4)</div>
              </div>
            </SidebarSection>
          </SidebarNews>
        </NewsGrid>
      )}

      {filteredNews.length === 0 && !loading && (
        <div style={{
          textAlign: 'center',
          padding: '3rem',
          color: '#666'
        }}>
          Aucune actualité trouvée pour cette catégorie.
        </div>
      )}
    </NewsContainer>
  );
};

export default NewsPage;
