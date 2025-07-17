import styled from 'styled-components';

// Styles pour la page News
export const NewsContainer = styled.div`
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
`;

export const NewsHeader = styled.div`
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

export const NewsGrid = styled.div`
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
`;

export const MainNewsSection = styled.div`
  display: flex;
  flex-direction: column;
  gap: 2rem;
`;

export const NewsCard = styled.article`
  background: ${props => props.theme.colors.white};
  border: 1px solid ${props => props.theme.colors.accent};
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.3s;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
  }

  .news-image {
    width: 100%;
    height: 200px;
    background: ${props => props.theme.colors.light};
    display: flex;
    align-items: center;
    justify-content: center;
    color: ${props => props.theme.colors.text};
    font-size: 1.5rem;
  }

  .news-content {
    padding: 1.5rem;
  }

  .news-meta {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: ${props => props.theme.colors.text};
  }

  .news-title {
    font-size: 1.3rem;
    font-weight: bold;
    color: ${props => props.theme.colors.primary};
    margin-bottom: 1rem;
    line-height: 1.4;
  }

  .news-excerpt {
    color: ${props => props.theme.colors.text};
    line-height: 1.6;
    margin-bottom: 1rem;
  }

  .news-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid ${props => props.theme.colors.light};
    padding-top: 1rem;
  }
`;

export const NewsButton = styled.button`
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

export const SidebarNews = styled.aside`
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
    color: ${props => props.theme.colors.primary};
    margin-bottom: 1rem;
    border-bottom: 2px solid ${props => props.theme.colors.accent};
    padding-bottom: 0.5rem;
  }
`;

export const RecentNewsItem = styled.div`
  padding: 1rem 0;
  border-bottom: 1px solid ${props => props.theme.colors.light};

  &:last-child {
    border-bottom: none;
  }

  .recent-title {
    font-size: 0.9rem;
    font-weight: 500;
    color: ${props => props.theme.colors.primary};
    margin-bottom: 0.5rem;
    line-height: 1.3;
  }

  .recent-date {
    font-size: 0.8rem;
    color: ${props => props.theme.colors.text};
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
`;

export const CategoryTag = styled.span`
  background: ${props => props.theme.colors.accent};
  color: ${props => props.theme.colors.white};
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
`;

export const NewsCategories = styled.div`
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  justify-content: center;
  flex-wrap: wrap;
`;

export const CategoryFilter = styled.button`
  background: ${props => props.active ? props.theme.colors.primary : props.theme.colors.light};
  color: ${props => props.active ? props.theme.colors.white : props.theme.colors.text};
  border: 1px solid ${props => props.theme.colors.accent};
  padding: 0.5rem 1rem;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s;

  &:hover {
    background: ${props => props.theme.colors.primary};
    color: ${props => props.theme.colors.white};
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
