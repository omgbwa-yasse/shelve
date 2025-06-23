import React from 'react';
import styled, { keyframes } from 'styled-components';

// Loading animation
const spin = keyframes`
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
`;

const pulse = keyframes`
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
`;

// Styled components
const LoadingContainer = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: ${props => props.size === 'small' ? '1rem' : '2rem'};
  min-height: ${props => props.fullHeight ? '200px' : 'auto'};
`;

const Spinner = styled.div`
  width: ${props => {
    switch (props.size) {
      case 'small': return '20px';
      case 'large': return '60px';
      default: return '40px';
    }
  }};
  height: ${props => {
    switch (props.size) {
      case 'small': return '20px';
      case 'large': return '60px';
      default: return '40px';
    }
  }};
  border: 3px solid #f3f3f3;
  border-top: 3px solid #007bff;
  border-radius: 50%;
  animation: ${spin} 1s linear infinite;
  margin-bottom: ${props => props.showText ? '1rem' : '0'};
`;

const LoadingText = styled.p`
  margin: 0;
  color: #666;
  font-size: ${props => props.size === 'small' ? '0.875rem' : '1rem'};
  text-align: center;
`;

const SkeletonContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 1rem;
  width: 100%;
`;

const SkeletonLine = styled.div`
  height: ${props => props.height || '1rem'};
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: ${pulse} 1.5s ease-in-out infinite;
  border-radius: 4px;
  width: ${props => props.width || '100%'};
`;

const SkeletonCard = styled.div`
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 1rem;
  background: white;
`;

const DotsContainer = styled.div`
  display: flex;
  gap: 0.5rem;
  align-items: center;
`;

const Dot = styled.div`
  width: 8px;
  height: 8px;
  background: #007bff;
  border-radius: 50%;
  animation: ${pulse} 1.5s ease-in-out infinite;
  animation-delay: ${props => props.delay || '0s'};
`;

// Main Loading component
const Loading = ({
  size = 'medium',
  text = null,
  fullHeight = false,
  variant = 'spinner'
}) => {
  const showText = text !== null;

  if (variant === 'dots') {
    return (
      <LoadingContainer size={size} fullHeight={fullHeight}>
        <DotsContainer>
          <Dot />
          <Dot delay="0.1s" />
          <Dot delay="0.2s" />
        </DotsContainer>
        {showText && <LoadingText size={size}>{text}</LoadingText>}
      </LoadingContainer>
    );
  }

  return (
    <LoadingContainer size={size} fullHeight={fullHeight}>
      <Spinner size={size} showText={showText} />
      {showText && <LoadingText size={size}>{text}</LoadingText>}
    </LoadingContainer>
  );
};

// Skeleton loader for content
export const Skeleton = ({ variant = 'text', count = 1, height, width }) => {
  const renderSkeleton = () => {
    switch (variant) {
      case 'card':
        return (
          <SkeletonCard>
            <SkeletonLine height="1.5rem" width="70%" />
            <SkeletonLine height="1rem" width="100%" />
            <SkeletonLine height="1rem" width="90%" />
            <SkeletonLine height="1rem" width="60%" />
          </SkeletonCard>
        );

      case 'list':
        return (
          <SkeletonContainer>
            {Array.from({ length: count }).map((_, index) => (
              <div key={index} style={{ display: 'flex', gap: '1rem', alignItems: 'center' }}>
                <SkeletonLine height="3rem" width="3rem" />
                <div style={{ flex: 1 }}>
                  <SkeletonLine height="1rem" width="70%" />
                  <SkeletonLine height="0.75rem" width="50%" />
                </div>
              </div>
            ))}
          </SkeletonContainer>
        );

      case 'table':
        return (
          <SkeletonContainer>
            {Array.from({ length: count }).map((_, index) => (
              <div key={index} style={{ display: 'flex', gap: '1rem' }}>
                <SkeletonLine height="2rem" width="20%" />
                <SkeletonLine height="2rem" width="30%" />
                <SkeletonLine height="2rem" width="25%" />
                <SkeletonLine height="2rem" width="25%" />
              </div>
            ))}
          </SkeletonContainer>
        );

      default:
        return (
          <SkeletonContainer>
            {Array.from({ length: count }).map((_, index) => (
              <SkeletonLine key={index} height={height} width={width} />
            ))}
          </SkeletonContainer>
        );
    }
  };

  return renderSkeleton();
};

// Inline loading for buttons
export const InlineLoading = ({ size = 'small' }) => (
  <Spinner size={size} style={{ margin: 0 }} />
);

// Page loading overlay
export const PageLoading = ({ text = 'Chargement en cours...' }) => (
  <div style={{
    position: 'fixed',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    background: 'rgba(255, 255, 255, 0.9)',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    zIndex: 9999,
  }}>
    <Loading size="large" text={text} />
  </div>
);

// Section loading
export const SectionLoading = ({ text = 'Chargement...', minHeight = '200px' }) => (
  <div style={{
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    minHeight,
    background: '#f8f9fa',
    borderRadius: '8px',
    border: '1px solid #dee2e6',
  }}>
    <Loading text={text} />
  </div>
);

// Loading states for lists
export const ListLoading = ({ count = 5 }) => (
  <Skeleton variant="list" count={count} />
);

// Loading states for cards
export const CardLoading = ({ count = 3 }) => (
  <div style={{ display: 'grid', gap: '1rem', gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))' }}>
    {Array.from({ length: count }).map((_, index) => (
      <Skeleton key={index} variant="card" />
    ))}
  </div>
);

// Loading states for tables
export const TableLoading = ({ rows = 5 }) => (
  <Skeleton variant="table" count={rows} />
);

export default Loading;
