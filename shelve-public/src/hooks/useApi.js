import { useState, useEffect, useCallback } from 'react';
import { errorUtils } from '../utils/helpers';

// Generic API hook for data fetching
export const useApi = (apiFunction, initialParams = null, options = {}) => {
  const {
    immediate = true,
    onSuccess = null,
    onError = null,
    dependencies = [],
  } = options;

  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(immediate);
  const [error, setError] = useState(null);

  const execute = useCallback(async (params = initialParams) => {
    setLoading(true);
    setError(null);

    try {
      const response = await apiFunction(params);
      const result = response.data;

      setData(result);

      if (onSuccess) {
        onSuccess(result);
      }

      return { success: true, data: result };
    } catch (err) {
      const errorMessage = errorUtils.getErrorMessage(err);
      setError(errorMessage);

      if (onError) {
        onError(errorMessage);
      }

      return { success: false, error: errorMessage };
    } finally {
      setLoading(false);
    }
  }, [apiFunction, initialParams, onSuccess, onError]);

  useEffect(() => {
    if (immediate) {
      execute();
    }
  }, [immediate, execute, ...dependencies]);

  const reset = useCallback(() => {
    setData(null);
    setError(null);
    setLoading(false);
  }, []);

  const refetch = useCallback(() => {
    return execute();
  }, [execute]);

  return {
    data,
    loading,
    error,
    execute,
    refetch,
    reset,
  };
};

// Hook for paginated API calls
export const usePaginatedApi = (apiFunction, initialParams = {}, options = {}) => {
  const {
    pageSize = 20,
    immediate = true,
    onSuccess = null,
    onError = null,
  } = options;

  const [data, setData] = useState([]);
  const [pagination, setPagination] = useState({
    currentPage: 1,
    totalPages: 1,
    totalItems: 0,
    pageSize,
    hasMore: false,
  });
  const [loading, setLoading] = useState(immediate);
  const [error, setError] = useState(null);

  const fetchPage = useCallback(async (page = 1, params = {}) => {
    setLoading(true);
    setError(null);

    try {
      const queryParams = {
        ...initialParams,
        ...params,
        page,
        per_page: pageSize,
      };

      const response = await apiFunction(queryParams);
      const result = response.data;

      if (page === 1) {
        setData(result.data || []);
      } else {
        setData(prevData => [...prevData, ...(result.data || [])]);
      }

      setPagination({
        currentPage: result.current_page || page,
        totalPages: result.last_page || 1,
        totalItems: result.total || 0,
        pageSize,
        hasMore: (result.current_page || page) < (result.last_page || 1),
      });

      if (onSuccess) {
        onSuccess(result);
      }

      return { success: true, data: result };
    } catch (err) {
      const errorMessage = errorUtils.getErrorMessage(err);
      setError(errorMessage);

      if (onError) {
        onError(errorMessage);
      }

      return { success: false, error: errorMessage };
    } finally {
      setLoading(false);
    }
  }, [apiFunction, initialParams, pageSize, onSuccess, onError]);

  const loadMore = useCallback(() => {
    if (pagination.hasMore && !loading) {
      return fetchPage(pagination.currentPage + 1);
    }
  }, [fetchPage, pagination.hasMore, pagination.currentPage, loading]);

  const refresh = useCallback((params = {}) => {
    setData([]);
    return fetchPage(1, params);
  }, [fetchPage]);

  const reset = useCallback(() => {
    setData([]);
    setPagination({
      currentPage: 1,
      totalPages: 1,
      totalItems: 0,
      pageSize,
      hasMore: false,
    });
    setError(null);
    setLoading(false);
  }, [pageSize]);

  useEffect(() => {
    if (immediate) {
      fetchPage(1);
    }
  }, [immediate, fetchPage]);

  return {
    data,
    pagination,
    loading,
    error,
    fetchPage,
    loadMore,
    refresh,
    reset,
  };
};

// Hook for API mutations (POST, PUT, DELETE)
export const useApiMutation = (apiFunction, options = {}) => {
  const {
    onSuccess = null,
    onError = null,
  } = options;

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const mutate = useCallback(async (data, params = {}) => {
    setLoading(true);
    setError(null);

    try {
      const response = await apiFunction(data, params);
      const result = response.data;

      if (onSuccess) {
        onSuccess(result);
      }

      return { success: true, data: result };
    } catch (err) {
      const errorMessage = errorUtils.getErrorMessage(err);
      setError(errorMessage);

      if (onError) {
        onError(errorMessage);
      }

      return { success: false, error: errorMessage };
    } finally {
      setLoading(false);
    }
  }, [apiFunction, onSuccess, onError]);

  const reset = useCallback(() => {
    setError(null);
    setLoading(false);
  }, []);

  return {
    mutate,
    loading,
    error,
    reset,
  };
};

// Hook for file upload with progress
export const useFileUpload = (uploadFunction, options = {}) => {
  const {
    onSuccess = null,
    onError = null,
    onProgress = null,
  } = options;

  const [loading, setLoading] = useState(false);
  const [progress, setProgress] = useState(0);
  const [error, setError] = useState(null);

  const upload = useCallback(async (file, additionalData = {}) => {
    setLoading(true);
    setProgress(0);
    setError(null);

    try {
      const formData = new FormData();
      formData.append('file', file);

      Object.entries(additionalData).forEach(([key, value]) => {
        formData.append(key, value);
      });

      const handleProgress = (progressEvent) => {
        const percentCompleted = Math.round(
          (progressEvent.loaded * 100) / progressEvent.total
        );
        setProgress(percentCompleted);

        if (onProgress) {
          onProgress(percentCompleted);
        }
      };

      const response = await uploadFunction(formData, handleProgress);
      const result = response.data;

      if (onSuccess) {
        onSuccess(result);
      }

      return { success: true, data: result };
    } catch (err) {
      const errorMessage = errorUtils.getErrorMessage(err);
      setError(errorMessage);

      if (onError) {
        onError(errorMessage);
      }

      return { success: false, error: errorMessage };
    } finally {
      setLoading(false);
      setProgress(0);
    }
  }, [uploadFunction, onSuccess, onError, onProgress]);

  const reset = useCallback(() => {
    setProgress(0);
    setError(null);
    setLoading(false);
  }, []);

  return {
    upload,
    loading,
    progress,
    error,
    reset,
  };
};

// Hook for search with debouncing
export const useSearchApi = (searchFunction, options = {}) => {
  const {
    debounceMs = 300,
    minLength = 2,
    immediate = false,
    onSuccess = null,
    onError = null,
  } = options;

  const [query, setQuery] = useState('');
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const search = useCallback(async (searchQuery, filters = {}) => {
    if (searchQuery.length < minLength) {
      setResults([]);
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const response = await searchFunction(searchQuery, filters);
      const result = response.data;

      setResults(result.data || result);

      if (onSuccess) {
        onSuccess(result);
      }

      return { success: true, data: result };
    } catch (err) {
      const errorMessage = errorUtils.getErrorMessage(err);
      setError(errorMessage);

      if (onError) {
        onError(errorMessage);
      }

      return { success: false, error: errorMessage };
    } finally {
      setLoading(false);
    }
  }, [searchFunction, minLength, onSuccess, onError]);

  useEffect(() => {
    if (query.length >= minLength || (immediate && query.length > 0)) {
      const timeoutId = setTimeout(() => {
        search(query);
      }, debounceMs);

      return () => clearTimeout(timeoutId);
    } else {
      setResults([]);
    }
  }, [query, search, debounceMs, minLength, immediate]);

  const clearResults = useCallback(() => {
    setResults([]);
    setError(null);
  }, []);

  const reset = useCallback(() => {
    setQuery('');
    setResults([]);
    setError(null);
    setLoading(false);
  }, []);

  return {
    query,
    setQuery,
    results,
    loading,
    error,
    search,
    clearResults,
    reset,
  };
};

export default {
  useApi,
  usePaginatedApi,
  useApiMutation,
  useFileUpload,
  useSearchApi,
};
