import { useState, useEffect, useCallback, useMemo } from 'react';
import { recordsApi } from '../services/shelveApi';
import { useApi } from './useApi';

export const useSearch = (initialQuery = '', initialFilters = {}) => {
  const [query, setQuery] = useState(initialQuery);
  const [filters, setFilters] = useState({
    type: '',
    status: 'published',
    date_from: '',
    date_to: '',
    classification: '',
    ...initialFilters
  });
  const [suggestions, setSuggestions] = useState([]);
  const [recentSearches, setRecentSearches] = useState([]);
  const [searchHistory, setSearchHistory] = useState([]);

  // Recherche principale avec debounce
  const {
    data: searchResults,
    loading: isSearching,
    error: searchError,
    refetch: performSearch
  } = useApi(
    () => query.trim() || Object.values(filters).some(v => v)
      ? recordsApi.searchRecords({ query, ...filters })
      : null,
    [query, filters],
    {
      debounce: 500, // Délai avant de lancer la recherche
      enabled: false // Contrôlé manuellement
    }
  );

  // Suggestions de recherche
  const {
    data: searchSuggestions,
    loading: loadingSuggestions
  } = useApi(
    () => query.length >= 2 ? recordsApi.getSearchSuggestions(query) : null,
    [query],
    { debounce: 300 }
  );

  // Recherches populaires
  const {
    data: popularSearches
  } = useApi(
    () => recordsApi.getPopularSearches(),
    []
  );

  // Mise à jour des suggestions
  useEffect(() => {
    if (searchSuggestions?.data) {
      setSuggestions(searchSuggestions.data);
    }
  }, [searchSuggestions]);

  // Chargement des recherches récentes depuis localStorage
  useEffect(() => {
    const saved = localStorage.getItem('recentSearches');
    if (saved) {
      try {
        setRecentSearches(JSON.parse(saved));
      } catch (err) {
        console.error('Erreur lecture recherches récentes:', err);
      }
    }

    const savedHistory = localStorage.getItem('searchHistory');
    if (savedHistory) {
      try {
        setSearchHistory(JSON.parse(savedHistory));
      } catch (err) {
        console.error('Erreur lecture historique recherches:', err);
      }
    }
  }, []);

  // Exécuter une recherche
  const search = useCallback((newQuery = query, newFilters = filters) => {
    setQuery(newQuery);
    setFilters(newFilters);

    // Ajouter à l'historique si ce n'est pas vide
    if (newQuery.trim()) {
      const searchEntry = {
        query: newQuery,
        filters: newFilters,
        timestamp: new Date().toISOString(),
        results_count: 0 // Sera mis à jour après les résultats
      };

      setSearchHistory(prev => {
        const updated = [searchEntry, ...prev.filter(s => s.query !== newQuery)].slice(0, 50);
        localStorage.setItem('searchHistory', JSON.stringify(updated));
        return updated;
      });

      // Ajouter aux recherches récentes
      addToRecentSearches(newQuery);
    }

    performSearch();
  }, [query, filters, performSearch, addToRecentSearches]);

  // Ajouter une recherche aux récentes
  const addToRecentSearches = useCallback((searchQuery) => {
    if (!searchQuery.trim()) return;

    setRecentSearches(prev => {
      const updated = [
        searchQuery,
        ...prev.filter(s => s !== searchQuery)
      ].slice(0, 10);

      localStorage.setItem('recentSearches', JSON.stringify(updated));
      return updated;
    });
  }, []);

  // Supprimer une recherche récente
  const removeFromRecentSearches = useCallback((searchQuery) => {
    setRecentSearches(prev => {
      const updated = prev.filter(s => s !== searchQuery);
      localStorage.setItem('recentSearches', JSON.stringify(updated));
      return updated;
    });
  }, []);

  // Effacer toutes les recherches récentes
  const clearRecentSearches = useCallback(() => {
    setRecentSearches([]);
    localStorage.removeItem('recentSearches');
  }, []);

  // Effacer l'historique
  const clearSearchHistory = useCallback(() => {
    setSearchHistory([]);
    localStorage.removeItem('searchHistory');
  }, []);

  // Mettre à jour un filtre
  const updateFilter = useCallback((key, value) => {
    setFilters(prev => ({ ...prev, [key]: value }));
  }, []);

  // Réinitialiser les filtres
  const resetFilters = useCallback(() => {
    setFilters({
      type: '',
      status: 'published',
      date_from: '',
      date_to: '',
      classification: ''
    });
  }, []);

  // Réinitialiser la recherche complète
  const resetSearch = useCallback(() => {
    setQuery('');
    resetFilters();
    setSuggestions([]);
  }, [resetFilters]);

  // Recherche avancée avec facettes
  const searchWithFacets = useCallback(async (searchParams) => {
    try {
      const results = await recordsApi.searchRecordsWithFacets(searchParams);
      return results;
    } catch (error) {
      console.error('Erreur recherche avec facettes:', error);
      throw error;
    }
  }, []);

  // Exporter les résultats de recherche
  const exportSearchResults = useCallback(async (format = 'csv') => {
    if (!searchResults?.data?.length) {
      throw new Error('Aucun résultat à exporter');
    }

    try {
      const exportData = await recordsApi.exportSearchResults({
        query,
        filters,
        format,
        results: searchResults.data.map(r => r.id)
      });

      // Déclencher le téléchargement
      const blob = new Blob([exportData], {
        type: format === 'csv' ? 'text/csv' : 'application/json'
      });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `recherche_${new Date().toISOString().split('T')[0]}.${format}`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);

    } catch (error) {
      console.error('Erreur export:', error);
      throw error;
    }
  }, [query, filters, searchResults]);

  // Statistiques de recherche
  const searchStats = useMemo(() => {
    const results = searchResults?.data || [];
    const total = searchResults?.meta?.total || 0;

    return {
      total,
      hasResults: results.length > 0,
      isEmpty: total === 0,
      hasMore: results.length < total,
      currentPage: searchResults?.meta?.current_page || 1,
      totalPages: searchResults?.meta?.last_page || 1,
      perPage: searchResults?.meta?.per_page || 20
    };
  }, [searchResults]);

  // Vérifier si la recherche est active
  const hasActiveSearch = useMemo(() => {
    return query.trim() !== '' || Object.values(filters).some(v => v !== '' && v !== 'published');
  }, [query, filters]);

  return {
    // État de la recherche
    query,
    filters,
    searchResults: searchResults?.data || [],
    searchMeta: searchResults?.meta,
    isSearching,
    searchError,
    hasActiveSearch,
    searchStats,

    // Suggestions et historique
    suggestions,
    loadingSuggestions,
    recentSearches,
    searchHistory,
    popularSearches: popularSearches?.data || [],

    // Actions de recherche
    search,
    setQuery,
    updateFilter,
    resetFilters,
    resetSearch,
    performSearch,

    // Gestion de l'historique
    addToRecentSearches,
    removeFromRecentSearches,
    clearRecentSearches,
    clearSearchHistory,

    // Fonctionnalités avancées
    searchWithFacets,
    exportSearchResults
  };
};

export const useDocuments = () => {
  const [favorites, setFavorites] = useState([]);
  const [recentlyViewed, setRecentlyViewed] = useState([]);

  // Charger les favoris depuis localStorage
  useEffect(() => {
    const savedFavorites = localStorage.getItem('documentFavorites');
    if (savedFavorites) {
      try {
        setFavorites(JSON.parse(savedFavorites));
      } catch (err) {
        console.error('Erreur lecture favoris:', err);
      }
    }

    const savedRecent = localStorage.getItem('recentlyViewedDocuments');
    if (savedRecent) {
      try {
        setRecentlyViewed(JSON.parse(savedRecent));
      } catch (err) {
        console.error('Erreur lecture documents récents:', err);
      }
    }
  }, []);

  // Ajouter/retirer des favoris
  const toggleFavorite = useCallback((documentId) => {
    setFavorites(prev => {
      const isFavorited = prev.includes(documentId);
      const updated = isFavorited
        ? prev.filter(id => id !== documentId)
        : [...prev, documentId];

      localStorage.setItem('documentFavorites', JSON.stringify(updated));
      return updated;
    });
  }, []);

  // Vérifier si un document est en favori
  const isFavorite = useCallback((documentId) => {
    return favorites.includes(documentId);
  }, [favorites]);

  // Ajouter un document aux récemment vus
  const addToRecentlyViewed = useCallback((document) => {
    const entry = {
      id: document.id,
      title: document.title,
      reference: document.reference,
      thumbnail_url: document.thumbnail_url,
      viewed_at: new Date().toISOString()
    };

    setRecentlyViewed(prev => {
      const updated = [
        entry,
        ...prev.filter(doc => doc.id !== document.id)
      ].slice(0, 20);

      localStorage.setItem('recentlyViewedDocuments', JSON.stringify(updated));
      return updated;
    });
  }, []);

  // Supprimer un document des récemment vus
  const removeFromRecentlyViewed = useCallback((documentId) => {
    setRecentlyViewed(prev => {
      const updated = prev.filter(doc => doc.id !== documentId);
      localStorage.setItem('recentlyViewedDocuments', JSON.stringify(updated));
      return updated;
    });
  }, []);

  // Effacer l'historique des documents vus
  const clearRecentlyViewed = useCallback(() => {
    setRecentlyViewed([]);
    localStorage.removeItem('recentlyViewedDocuments');
  }, []);

  // Effacer les favoris
  const clearFavorites = useCallback(() => {
    setFavorites([]);
    localStorage.removeItem('documentFavorites');
  }, []);

  return {
    favorites,
    recentlyViewed,
    toggleFavorite,
    isFavorite,
    addToRecentlyViewed,
    removeFromRecentlyViewed,
    clearRecentlyViewed,
    clearFavorites
  };
};
