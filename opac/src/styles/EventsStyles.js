import styled from 'styled-components';

// Styles pour la page Events
export const EventsContainer = styled.div`
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
`;

export const EventsHeader = styled.div`
  background: ${props => props.theme.colors.white};
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-bottom: 2rem;
  text-align: center;

  h1 {
    color: ${props => props.theme.colors.primary};
    margin-bottom: 1rem;
  }

  p {
    color: ${props => props.theme.colors.text};
    font-size: 1.1rem;
    line-height: 1.6;
  }
`;

export const EventsGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
`;

export const EventCard = styled.div`
  background: ${props => props.theme.colors.white};
  border: 1px solid ${props => props.theme.colors.accent};
  border-radius: 8px;
  padding: 2rem;
  transition: all 0.3s;
  position: relative;
  overflow: hidden;

  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
  }

  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: ${props => props.theme.colors.secondary};
  }

  .event-date {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: ${props => props.theme.colors.secondary};
    font-weight: bold;
    font-size: 0.9rem;
    margin-bottom: 1rem;
  }

  .event-title {
    font-size: 1.4rem;
    font-weight: bold;
    color: ${props => props.theme.colors.primary};
    margin-bottom: 1rem;
    line-height: 1.4;
  }

  .event-description {
    color: ${props => props.theme.colors.text};
    line-height: 1.6;
    margin-bottom: 1.5rem;
  }

  .event-location {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: ${props => props.theme.colors.text};
    font-size: 0.9rem;
    margin-bottom: 1rem;
  }

  .event-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
    border-top: 1px solid ${props => props.theme.colors.light};
    padding-top: 1rem;
  }
`;

export const EventButton = styled.button`
  background: ${props => props.variant === 'primary' ? props.theme.colors.primary : props.theme.colors.light};
  color: ${props => props.variant === 'primary' ? props.theme.colors.white : props.theme.colors.text};
  border: 1px solid ${props => props.theme.colors.accent};
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  transition: all 0.3s;

  &:hover {
    background: ${props => props.theme.colors.secondary};
    color: ${props => props.theme.colors.white};
  }
`;

export const FilterTabs = styled.div`
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  justify-content: center;
  flex-wrap: wrap;
`;

export const FilterTab = styled.button`
  background: ${props => props.active ? props.theme.colors.primary : props.theme.colors.light};
  color: ${props => props.active ? props.theme.colors.white : props.theme.colors.text};
  border: 1px solid ${props => props.theme.colors.accent};
  padding: 0.75rem 1.5rem;
  border-radius: 25px;
  cursor: pointer;
  transition: all 0.3s;
  font-weight: 500;

  &:hover {
    background: ${props => props.theme.colors.primary};
    color: ${props => props.theme.colors.white};
  }
`;

export const EventStatus = styled.span`
  background: ${props => {
    switch (props.status) {
      case 'upcoming': return props.theme.colors.secondary;
      case 'ongoing': return '#28a745';
      case 'past': return '#6c757d';
      default: return props.theme.colors.accent;
    }
  }};
  color: ${props => props.theme.colors.white};
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
`;

export const LoadingSpinner = styled.div`
  display: flex;
  justify-content: center;
  align-items: center;
  height: 200px;

  &::after {
    content: '';
    width: 40px;
    height: 40px;
    border: 4px solid ${props => props.theme.colors.light};
    border-top: 4px solid ${props => props.theme.colors.primary};
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
`;
