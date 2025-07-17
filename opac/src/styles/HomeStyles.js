import styled from 'styled-components';

// Styles pour la page d'accueil
export const SearchSection = styled.section`
  background: ${props => props.theme.colors.white};
  padding: 1rem 0;
  text-align: center;
  border-bottom: 1px solid ${props => props.theme.colors.light};
`;

export const SearchContainer = styled.div`
  max-width: 800px;
  margin: 0 auto;
  padding: 0 2rem;
`;

export const SearchForm = styled.form`
  display: flex;
  gap: 1rem;

  @media (max-width: 768px) {
    flex-direction: column;
  }
`;

export const SearchInput = styled.input`
  flex: 1;
  padding: 0.75rem;
  border: 2px solid ${props => props.theme.colors.accent};
  border-radius: 6px;
  font-size: 1rem;

  &:focus {
    outline: none;
    border-color: ${props => props.theme.colors.primary};
  }
`;

export const SearchButton = styled.button`
  background: ${props => props.theme.colors.primary};
  color: ${props => props.theme.colors.white};
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: background 0.3s;

  &:hover {
    background: ${props => props.theme.colors.secondary};
  }
`;

export const MainContent = styled.main`
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 2rem;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
    padding: 1rem;
  }
`;

export const ContentArea = styled.div`
  background: ${props => props.theme.colors.white};
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
`;

export const Sidebar = styled.aside`
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
`;

export const SidebarSection = styled.div`
  background: ${props => props.theme.colors.white};
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);

  h3 {
    margin-top: 0;
    color: ${props => props.theme.colors.primary};
    border-bottom: 2px solid ${props => props.theme.colors.accent};
    padding-bottom: 0.5rem;
  }
`;

export const StatsGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
`;

export const StatCard = styled.div`
  background: ${props => props.theme.colors.white};
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  text-align: center;

  .icon {
    font-size: 2rem;
    color: ${props => props.theme.colors.primary};
    margin-bottom: 1rem;
  }

  .number {
    font-size: 2rem;
    font-weight: bold;
    color: ${props => props.theme.colors.secondary};
  }

  .label {
    color: ${props => props.theme.colors.text};
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
`;

export const RecordCard = styled.div`
  background: ${props => props.theme.colors.white};
  border: 1px solid ${props => props.theme.colors.accent};
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 1rem;
  transition: transform 0.3s;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }

  .title {
    font-size: 1.2rem;
    font-weight: bold;
    color: ${props => props.theme.colors.primary};
    margin-bottom: 0.5rem;
  }

  .metadata {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: ${props => props.theme.colors.text};
  }

  .description {
    color: ${props => props.theme.colors.text};
    line-height: 1.5;
  }
`;

export const EventCard = styled.div`
  border-left: 4px solid ${props => props.theme.colors.secondary};
  padding: 1rem;
  margin-bottom: 1rem;
  background: ${props => props.theme.colors.light};

  .date {
    font-size: 0.9rem;
    color: ${props => props.theme.colors.secondary};
    font-weight: bold;
  }

  .title {
    font-size: 1.1rem;
    color: ${props => props.theme.colors.primary};
    margin: 0.5rem 0;
  }

  .description {
    color: ${props => props.theme.colors.text};
    font-size: 0.9rem;
  }
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
