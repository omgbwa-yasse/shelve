import styled from 'styled-components';

// Styles pour la page Catalogue
export const CatalogueContainer = styled.div`
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
`;

export const CatalogueHeader = styled.div`
  background: ${props => props.theme.colors.white};
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-bottom: 2rem;
`;

export const CatalogueControls = styled.div`
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;

  @media (max-width: 768px) {
    flex-direction: column;
    align-items: stretch;
  }
`;

export const SortControls = styled.div`
  display: flex;
  gap: 1rem;
  align-items: center;
`;

export const SortButton = styled.button`
  background: ${props => props.active ? props.theme.colors.primary : props.theme.colors.light};
  color: ${props => props.active ? props.theme.colors.white : props.theme.colors.text};
  border: 1px solid ${props => props.theme.colors.accent};
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s;

  &:hover {
    background: ${props => props.theme.colors.primary};
    color: ${props => props.theme.colors.white};
  }
`;

export const FilterButton = styled.button`
  background: ${props => props.active ? props.theme.colors.secondary : props.theme.colors.light};
  color: ${props => props.active ? props.theme.colors.white : props.theme.colors.text};
  border: 1px solid ${props => props.theme.colors.accent};
  padding: 0.5rem 1rem;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s;

  &:hover {
    background: ${props => props.theme.colors.secondary};
    color: ${props => props.theme.colors.white};
  }
`;

export const FiltersContainer = styled.div`
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
  justify-content: center;
  flex-wrap: wrap;
`;

export const ResultsInfo = styled.div`
  color: ${props => props.theme.colors.text};
  font-size: 0.9rem;
`;

export const CatalogueGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
`;

export const CatalogueCard = styled.div`
  background: ${props => props.theme.colors.white};
  border: 1px solid ${props => props.theme.colors.accent};
  border-radius: 8px;
  padding: 1.5rem;
  transition: all 0.3s;

  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
  }

  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
  }

  .card-title {
    font-size: 1.2rem;
    font-weight: bold;
    color: ${props => props.theme.colors.primary};
    margin-bottom: 0.5rem;
    line-height: 1.4;
  }

  .card-metadata {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: ${props => props.theme.colors.text};
  }

  .card-description {
    color: ${props => props.theme.colors.text};
    line-height: 1.6;
    margin-bottom: 1rem;
  }

  .card-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid ${props => props.theme.colors.light};
    padding-top: 1rem;
  }
`;

export const ActionButton = styled.button`
  background: ${props => props.variant === 'primary' ? props.theme.colors.primary : props.theme.colors.light};
  color: ${props => props.variant === 'primary' ? props.theme.colors.white : props.theme.colors.text};
  border: 1px solid ${props => props.theme.colors.accent};
  padding: 0.5rem 1rem;
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

export const Pagination = styled.div`
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
`;

export const PaginationButton = styled.button`
  background: ${props => props.active ? props.theme.colors.primary : props.theme.colors.light};
  color: ${props => props.active ? props.theme.colors.white : props.theme.colors.text};
  border: 1px solid ${props => props.theme.colors.accent};
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s;

  &:hover {
    background: ${props => props.theme.colors.primary};
    color: ${props => props.theme.colors.white};
  }

  &:disabled {
    background: ${props => props.theme.colors.light};
    color: #ccc;
    cursor: not-allowed;
  }
`;

export const CategoryBadge = styled.span`
  background: ${props => props.theme.colors.accent};
  color: ${props => props.theme.colors.white};
  padding: 0.25rem 0.5rem;
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
