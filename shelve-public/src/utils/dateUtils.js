import { format, parseISO, isValid, differenceInDays, differenceInHours, differenceInMinutes } from 'date-fns';
import { fr } from 'date-fns/locale';
import { DATE_FORMATS } from './constants';

// Format date for display
export const formatDate = (date, formatString = DATE_FORMATS.DISPLAY) => {
  if (!date) return '';

  try {
    const dateObj = typeof date === 'string' ? parseISO(date) : new Date(date);
    if (!isValid(dateObj)) return '';

    return format(dateObj, formatString, { locale: fr });
  } catch (error) {
    console.error('Error formatting date:', error);
    return '';
  }
};

// Format date with time
export const formatDateTime = (date) => {
  return formatDate(date, DATE_FORMATS.DISPLAY_WITH_TIME);
};

// Format date for API
export const formatDateForApi = (date) => {
  return formatDate(date, DATE_FORMATS.API);
};

// Format date with time for API
export const formatDateTimeForApi = (date) => {
  return formatDate(date, DATE_FORMATS.API_WITH_TIME);
};

// Get relative time (e.g., "il y a 2 heures")
export const getRelativeTime = (date) => {
  if (!date) return '';

  try {
    const dateObj = typeof date === 'string' ? parseISO(date) : new Date(date);
    if (!isValid(dateObj)) return '';

    const now = new Date();
    const diffInMinutes = differenceInMinutes(now, dateObj);
    const diffInHours = differenceInHours(now, dateObj);
    const diffInDays = differenceInDays(now, dateObj);

    if (diffInMinutes < 1) {
      return 'À l\'instant';
    } else if (diffInMinutes < 60) {
      return `Il y a ${diffInMinutes} minute${diffInMinutes > 1 ? 's' : ''}`;
    } else if (diffInHours < 24) {
      return `Il y a ${diffInHours} heure${diffInHours > 1 ? 's' : ''}`;
    } else if (diffInDays < 7) {
      return `Il y a ${diffInDays} jour${diffInDays > 1 ? 's' : ''}`;
    } else {
      return formatDate(dateObj);
    }
  } catch (error) {
    console.error('Error getting relative time:', error);
    return '';
  }
};

// Check if date is today
export const isToday = (date) => {
  if (!date) return false;

  try {
    const dateObj = typeof date === 'string' ? parseISO(date) : new Date(date);
    const today = new Date();
    return differenceInDays(today, dateObj) === 0;
  } catch (error) {
    return false;
  }
};

// Check if date is in the past
export const isPast = (date) => {
  if (!date) return false;

  try {
    const dateObj = typeof date === 'string' ? parseISO(date) : new Date(date);
    return dateObj < new Date();
  } catch (error) {
    return false;
  }
};

// Check if date is in the future
export const isFuture = (date) => {
  if (!date) return false;

  try {
    const dateObj = typeof date === 'string' ? parseISO(date) : new Date(date);
    return dateObj > new Date();
  } catch (error) {
    return false;
  }
};

// Get start of day
export const getStartOfDay = (date = new Date()) => {
  const dateObj = new Date(date);
  dateObj.setHours(0, 0, 0, 0);
  return dateObj;
};

// Get end of day
export const getEndOfDay = (date = new Date()) => {
  const dateObj = new Date(date);
  dateObj.setHours(23, 59, 59, 999);
  return dateObj;
};

// Get date range options for filters
export const getDateRangeOptions = () => {
  const now = new Date();
  const startOfToday = getStartOfDay(now);
  const endOfToday = getEndOfDay(now);

  const yesterday = new Date(now);
  yesterday.setDate(yesterday.getDate() - 1);

  const lastWeek = new Date(now);
  lastWeek.setDate(lastWeek.getDate() - 7);

  const lastMonth = new Date(now);
  lastMonth.setMonth(lastMonth.getMonth() - 1);

  const lastYear = new Date(now);
  lastYear.setFullYear(lastYear.getFullYear() - 1);

  return {
    today: { start: startOfToday, end: endOfToday },
    yesterday: { start: getStartOfDay(yesterday), end: getEndOfDay(yesterday) },
    lastWeek: { start: getStartOfDay(lastWeek), end: endOfToday },
    lastMonth: { start: getStartOfDay(lastMonth), end: endOfToday },
    lastYear: { start: getStartOfDay(lastYear), end: endOfToday },
  };
};

// Parse date range string
export const parseDateRange = (rangeString) => {
  const ranges = getDateRangeOptions();

  switch (rangeString) {
    case 'today':
      return ranges.today;
    case 'yesterday':
      return ranges.yesterday;
    case 'last_week':
      return ranges.lastWeek;
    case 'last_month':
      return ranges.lastMonth;
    case 'last_year':
      return ranges.lastYear;
    default:
      return null;
  }
};

// Validate date string
export const isValidDate = (dateString) => {
  if (!dateString) return false;

  try {
    const date = typeof dateString === 'string' ? parseISO(dateString) : new Date(dateString);
    return isValid(date);
  } catch (error) {
    return false;
  }
};

// Get calendar weeks for a month
export const getCalendarWeeks = (year, month) => {
  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  const weeks = [];

  // Get the first day of the first week
  const startDate = new Date(firstDay);
  startDate.setDate(startDate.getDate() - firstDay.getDay());

  let currentDate = new Date(startDate);

  while (currentDate <= lastDay || currentDate.getDay() !== 0) {
    const week = [];

    for (let i = 0; i < 7; i++) {
      week.push(new Date(currentDate));
      currentDate.setDate(currentDate.getDate() + 1);
    }

    weeks.push(week);

    // Break if we've covered the entire month
    if (currentDate > lastDay && currentDate.getDay() === 0) {
      break;
    }
  }

  return weeks;
};

// Get time slots for a day (e.g., for event scheduling)
export const getTimeSlots = (startHour = 8, endHour = 18, intervalMinutes = 30) => {
  const slots = [];
  const date = new Date();

  for (let hour = startHour; hour < endHour; hour++) {
    for (let minute = 0; minute < 60; minute += intervalMinutes) {
      date.setHours(hour, minute, 0, 0);
      slots.push({
        time: format(date, 'HH:mm'),
        value: format(date, 'HH:mm'),
        label: format(date, 'HH:mm'),
      });
    }
  }

  return slots;
};

// Add business days (excluding weekends)
export const addBusinessDays = (date, days) => {
  const result = new Date(date);
  let addedDays = 0;

  while (addedDays < days) {
    result.setDate(result.getDate() + 1);

    // Skip weekends (Saturday = 6, Sunday = 0)
    if (result.getDay() !== 0 && result.getDay() !== 6) {
      addedDays++;
    }
  }

  return result;
};

// Check if date is a business day
export const isBusinessDay = (date) => {
  const day = date.getDay();
  return day !== 0 && day !== 6; // Not Sunday (0) or Saturday (6)
};

// Get age from birth date
export const getAge = (birthDate) => {
  if (!birthDate) return null;

  try {
    const birth = typeof birthDate === 'string' ? parseISO(birthDate) : new Date(birthDate);
    const today = new Date();
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();

    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
      age--;
    }

    return age;
  } catch (error) {
    return null;
  }
};

// Format duration in minutes to human readable
export const formatDuration = (minutes) => {
  if (!minutes || minutes < 0) return '';

  const hours = Math.floor(minutes / 60);
  const remainingMinutes = minutes % 60;

  if (hours === 0) {
    return `${remainingMinutes} min`;
  } else if (remainingMinutes === 0) {
    return `${hours}h`;
  } else {
    return `${hours}h ${remainingMinutes}min`;
  }
};

export default {
  formatDate,
  formatDateTime,
  formatDateForApi,
  formatDateTimeForApi,
  getRelativeTime,
  isToday,
  isPast,
  isFuture,
  getStartOfDay,
  getEndOfDay,
  getDateRangeOptions,
  parseDateRange,
  isValidDate,
  getCalendarWeeks,
  getTimeSlots,
  addBusinessDays,
  isBusinessDay,
  getAge,
  formatDuration,
};
