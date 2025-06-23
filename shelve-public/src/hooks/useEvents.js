import { useState, useCallback, useMemo } from 'react';
import { eventsApi } from '../services/shelveApi';
import { usePaginatedApi, useApiMutation } from './useApi';
import { EVENT_TYPES, EVENT_STATUS } from '../utils/constants';
import { formatDate, isFuture, isPast } from '../utils/dateUtils';

// Main hook for events management
export const useEvents = (initialFilters = {}) => {
  const [filters, setFilters] = useState({
    type: '',
    status: '',
    date_from: '',
    date_to: '',
    search: '',
    ...initialFilters,
  });

  // Paginated events fetching
  const {
    data: events,
    pagination,
    loading,
    error,
    fetchPage,
    loadMore,
    refresh,
    reset,
  } = usePaginatedApi(
    eventsApi.getEvents,
    filters,
    {
      pageSize: 12,
      immediate: true,
    }
  );

  // Update filters and refresh
  const updateFilters = useCallback((newFilters) => {
    setFilters(prev => ({ ...prev, ...newFilters }));
    refresh(newFilters);
  }, [refresh]);

  // Clear filters
  const clearFilters = useCallback(() => {
    const clearedFilters = {
      type: '',
      status: '',
      date_from: '',
      date_to: '',
      search: '',
    };
    setFilters(clearedFilters);
    refresh(clearedFilters);
  }, [refresh]);

  // Filter events by status
  const filteredEvents = useMemo(() => {
    return events.map(event => ({
      ...event,
      computed_status: getEventStatus(event),
      is_upcoming: isFuture(event.start_date),
      is_past: isPast(event.end_date),
      formatted_start_date: formatDate(event.start_date),
      formatted_end_date: formatDate(event.end_date),
    }));
  }, [events]);

  return {
    events: filteredEvents,
    pagination,
    loading,
    error,
    filters,
    updateFilters,
    clearFilters,
    fetchPage,
    loadMore,
    refresh,
    reset,
  };
};

// Hook for single event details
export const useEvent = (eventId) => {
  const [event, setEvent] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const fetchEvent = useCallback(async () => {
    if (!eventId) return;

    setLoading(true);
    setError(null);

    try {
      const response = await eventsApi.getEvent(eventId);
      const eventData = {
        ...response.data,
        computed_status: getEventStatus(response.data),
        is_upcoming: isFuture(response.data.start_date),
        is_past: isPast(response.data.end_date),
        formatted_start_date: formatDate(response.data.start_date),
        formatted_end_date: formatDate(response.data.end_date),
      };
      setEvent(eventData);
      return { success: true, data: eventData };
    } catch (err) {
      const errorMessage = err.response?.data?.message || 'Erreur lors du chargement de l\'événement';
      setError(errorMessage);
      return { success: false, error: errorMessage };
    } finally {
      setLoading(false);
    }
  }, [eventId]);

  return {
    event,
    loading,
    error,
    fetchEvent,
    refetch: fetchEvent,
  };
};

// Hook for event registration
export const useEventRegistration = () => {
  const {
    mutate: register,
    loading: registering,
    error: registrationError,
    reset: resetRegistration,
  } = useApiMutation(
    (data) => eventsApi.registerToEvent(data.eventId, data.registrationData),
    {
      onSuccess: (result) => {
        console.log('Registration successful:', result);
      },
      onError: (error) => {
        console.error('Registration failed:', error);
      },
    }
  );

  const registerToEvent = useCallback(async (eventId, registrationData) => {
    return await register({ eventId, registrationData });
  }, [register]);

  return {
    registerToEvent,
    registering,
    registrationError,
    resetRegistration,
  };
};

// Hook for event calendar view
export const useEventCalendar = () => {
  const [currentDate, setCurrentDate] = useState(new Date());
  const [viewMode, setViewMode] = useState('month'); // 'month', 'week', 'day'

  const {
    data: calendarEvents,
    loading,
    error,
    refresh,
  } = usePaginatedApi(
    eventsApi.getEvents,
    {
      date_from: formatDate(getCalendarRangeStart(currentDate, viewMode)),
      date_to: formatDate(getCalendarRangeEnd(currentDate, viewMode)),
    },
    {
      pageSize: 100,
      immediate: true,
    }
  );

  // Navigate calendar
  const navigateCalendar = useCallback((direction) => {
    setCurrentDate(prev => {
      const newDate = new Date(prev);
      switch (viewMode) {
        case 'month':
          newDate.setMonth(newDate.getMonth() + (direction === 'next' ? 1 : -1));
          break;
        case 'week':
          newDate.setDate(newDate.getDate() + (direction === 'next' ? 7 : -7));
          break;
        case 'day':
          newDate.setDate(newDate.getDate() + (direction === 'next' ? 1 : -1));
          break;
        default:
          break;
      }
      return newDate;
    });
  }, [viewMode]);

  // Change view mode
  const changeViewMode = useCallback((mode) => {
    setViewMode(mode);
  }, []);

  // Go to today
  const goToToday = useCallback(() => {
    setCurrentDate(new Date());
  }, []);

  // Go to specific date
  const goToDate = useCallback((date) => {
    setCurrentDate(new Date(date));
  }, []);

  // Group events by date
  const eventsByDate = useMemo(() => {
    const grouped = {};
    calendarEvents.forEach(event => {
      const dateKey = formatDate(event.start_date);
      if (!grouped[dateKey]) {
        grouped[dateKey] = [];
      }
      grouped[dateKey].push({
        ...event,
        computed_status: getEventStatus(event),
      });
    });
    return grouped;
  }, [calendarEvents]);

  return {
    currentDate,
    viewMode,
    calendarEvents,
    eventsByDate,
    loading,
    error,
    navigateCalendar,
    changeViewMode,
    goToToday,
    goToDate,
    refresh,
  };
};

// Hook for event statistics
export const useEventStats = () => {
  const [stats, setStats] = useState({
    total: 0,
    upcoming: 0,
    ongoing: 0,
    completed: 0,
    by_type: {},
  });

  const {
    data: allEvents,
    loading,
    error,
  } = usePaginatedApi(
    eventsApi.getEvents,
    {},
    {
      pageSize: 1000,
      immediate: true,
      onSuccess: (result) => {
        calculateStats(result.data || []);
      },
    }
  );

  const calculateStats = useCallback((events) => {
    const total = events.length;
    let upcoming = 0;
    let ongoing = 0;
    let completed = 0;
    const byType = {};

    events.forEach(event => {
      const status = getEventStatus(event);

      switch (status) {
        case EVENT_STATUS.UPCOMING:
          upcoming++;
          break;
        case EVENT_STATUS.ONGOING:
          ongoing++;
          break;
        case EVENT_STATUS.COMPLETED:
          completed++;
          break;
        default:
          break;
      }

      // Count by type
      const type = event.type || 'other';
      byType[type] = (byType[type] || 0) + 1;
    });

    setStats({
      total,
      upcoming,
      ongoing,
      completed,
      by_type: byType,
    });
  }, []);

  return {
    stats,
    loading,
    error,
  };
};

// Utility functions
const getEventStatus = (event) => {
  const now = new Date();
  const startDate = new Date(event.start_date);
  const endDate = new Date(event.end_date);

  if (event.status === EVENT_STATUS.CANCELLED) {
    return EVENT_STATUS.CANCELLED;
  }

  if (now < startDate) {
    return EVENT_STATUS.UPCOMING;
  } else if (now >= startDate && now <= endDate) {
    return EVENT_STATUS.ONGOING;
  } else {
    return EVENT_STATUS.COMPLETED;
  }
};

const getCalendarRangeStart = (date, viewMode) => {
  const d = new Date(date);

  switch (viewMode) {
    case 'month':
      return new Date(d.getFullYear(), d.getMonth(), 1);
    case 'week':
      const day = d.getDay();
      const diff = d.getDate() - day + (day === 0 ? -6 : 1); // Monday as first day
      return new Date(d.setDate(diff));
    case 'day':
      return new Date(d.getFullYear(), d.getMonth(), d.getDate());
    default:
      return d;
  }
};

const getCalendarRangeEnd = (date, viewMode) => {
  const d = new Date(date);

  switch (viewMode) {
    case 'month':
      return new Date(d.getFullYear(), d.getMonth() + 1, 0);
    case 'week':
      const day = d.getDay();
      const diff = d.getDate() - day + (day === 0 ? -6 : 1) + 6; // Sunday as last day
      return new Date(d.setDate(diff));
    case 'day':
      return new Date(d.getFullYear(), d.getMonth(), d.getDate(), 23, 59, 59);
    default:
      return d;
  }
};

export default {
  useEvents,
  useEvent,
  useEventRegistration,
  useEventCalendar,
  useEventStats,
};
