import styled from 'styled-components';

// Styles pour la page About
export const AboutContainer = styled.div`
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
`;

export const AboutHeader = styled.div`
  background: ${props => props.theme.colors.white};
  padding: 3rem 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-bottom: 2rem;
  text-align: center;

  h1 {
    color: ${props => props.theme.colors.primary};
    margin-bottom: 1rem;
    font-size: 2.5rem;
  }

  .subtitle {
    color: ${props => props.theme.colors.secondary};
    font-size: 1.2rem;
    margin-bottom: 1.5rem;
    font-weight: 500;
  }

  .description {
    color: ${props => props.theme.colors.text};
    font-size: 1.1rem;
    line-height: 1.6;
    max-width: 800px;
    margin: 0 auto;
  }
`;

export const AboutContent = styled.div`
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
`;

export const MainContent = styled.div`
  display: flex;
  flex-direction: column;
  gap: 2rem;
`;

export const ContentSection = styled.section`
  background: ${props => props.theme.colors.white};
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);

  h2 {
    color: ${props => props.theme.colors.primary};
    margin-bottom: 1.5rem;
    font-size: 1.8rem;
    border-bottom: 2px solid ${props => props.theme.colors.accent};
    padding-bottom: 0.5rem;
  }

  h3 {
    color: ${props => props.theme.colors.secondary};
    margin-bottom: 1rem;
    font-size: 1.3rem;
  }

  p {
    color: ${props => props.theme.colors.text};
    line-height: 1.7;
    margin-bottom: 1rem;
  }

  ul {
    color: ${props => props.theme.colors.text};
    line-height: 1.7;
    margin-left: 1.5rem;
    margin-bottom: 1rem;
  }

  li {
    margin-bottom: 0.5rem;
  }
`;

export const Sidebar = styled.aside`
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
`;

export const SidebarCard = styled.div`
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

export const ContactInfo = styled.div`
  .contact-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    color: ${props => props.theme.colors.text};
  }

  .contact-icon {
    color: ${props => props.theme.colors.primary};
    font-size: 1.2rem;
  }

  .contact-text {
    flex: 1;
  }
`;

export const StatsGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
`;

export const StatCard = styled.div`
  text-align: center;
  padding: 1rem;
  background: ${props => props.theme.colors.light};
  border-radius: 8px;

  .stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: ${props => props.theme.colors.primary};
    margin-bottom: 0.5rem;
  }

  .stat-label {
    color: ${props => props.theme.colors.text};
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
`;

export const TeamGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-top: 1.5rem;
`;

export const TeamMember = styled.div`
  text-align: center;
  padding: 1.5rem;
  background: ${props => props.theme.colors.light};
  border-radius: 8px;
  transition: all 0.3s;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }

  .member-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: ${props => props.theme.colors.accent};
    margin: 0 auto 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: ${props => props.theme.colors.white};
    font-size: 2rem;
  }

  .member-name {
    font-weight: bold;
    color: ${props => props.theme.colors.primary};
    margin-bottom: 0.5rem;
  }

  .member-role {
    color: ${props => props.theme.colors.text};
    font-size: 0.9rem;
  }
`;

export const TimelineContainer = styled.div`
  position: relative;
  margin-left: 2rem;
`;

export const TimelineItem = styled.div`
  position: relative;
  padding-left: 3rem;
  margin-bottom: 2rem;

  &::before {
    content: '';
    position: absolute;
    left: -1rem;
    top: 0.5rem;
    width: 1rem;
    height: 1rem;
    background: ${props => props.theme.colors.primary};
    border-radius: 50%;
    z-index: 2;
  }

  &::after {
    content: '';
    position: absolute;
    left: -0.5rem;
    top: 1.5rem;
    width: 2px;
    height: calc(100% + 1rem);
    background: ${props => props.theme.colors.accent};
    z-index: 1;
  }

  &:last-child::after {
    display: none;
  }

  .timeline-year {
    font-weight: bold;
    color: ${props => props.theme.colors.primary};
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
  }

  .timeline-event {
    color: ${props => props.theme.colors.text};
    line-height: 1.6;
  }
`;

export const ActionButton = styled.button`
  background: ${props => props.theme.colors.primary};
  color: ${props => props.theme.colors.white};
  border: none;
  padding: 1rem 2rem;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1rem;
  font-weight: 500;
  transition: all 0.3s;
  margin-top: 1rem;

  &:hover {
    background: ${props => props.theme.colors.secondary};
    transform: translateY(-1px);
  }
`;
