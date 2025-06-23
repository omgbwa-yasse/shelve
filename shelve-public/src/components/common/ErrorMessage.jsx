import React from 'react';
import styled from 'styled-components';

// Styled components
const ErrorContainer = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: ${props => props.size === 'small' ? '1rem' : '2rem'};
  text-align: center;
  background: ${props => {
    switch (props.variant) {
      case 'danger': return '#f8d7da';
      case 'warning': return '#fff3cd';
      case 'info': return '#d1ecf1';
      default: return '#f8d7da';
    }
  }};
  border: 1px solid ${props => {
    switch (props.variant) {
      case 'danger': return '#f5c6cb';
      case 'warning': return '#ffeaa7';
      case 'info': return '#b6d4fe';
      default: return '#f5c6cb';
    }
  }};
  border-radius: 8px;
  color: ${props => {
    switch (props.variant) {
      case 'danger': return '#721c24';
      case 'warning': return '#856404';
      case 'info': return '#0c5460';
      default: return '#721c24';
    }
  }};
  margin: ${props => props.fullWidth ? '0' : '1rem'};
  min-height: ${props => props.minHeight || 'auto'};
`;

const ErrorIcon = styled.div`
  font-size: ${props => props.size === 'small' ? '1.5rem' : '2.5rem'};
  margin-bottom: ${props => props.size === 'small' ? '0.5rem' : '1rem'};
`;

const ErrorTitle = styled.h3`
  margin: 0 0 ${props => props.hasMessage ? '0.5rem' : '0'} 0;
  font-size: ${props => props.size === 'small' ? '1rem' : '1.25rem'};
  font-weight: 600;
`;

const ErrorMessage = styled.p`
  margin: 0 0 ${props => props.hasActions ? '1rem' : '0'} 0;
  font-size: ${props => props.size === 'small' ? '0.875rem' : '1rem'};
  line-height: 1.5;
  opacity: 0.9;
`;

const ErrorActions = styled.div`
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  justify-content: center;
`;

const ErrorButton = styled.button`
  padding: 0.5rem 1rem;
  border: 1px solid transparent;
  border-radius: 4px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;

  ${props => props.variant === 'primary' ? `
    background: #007bff;
    color: white;
    border-color: #007bff;

    &:hover {
      background: #0056b3;
      border-color: #0056b3;
    }
  ` : `
    background: transparent;
    color: #6c757d;
    border-color: #6c757d;

    &:hover {
      background: #6c757d;
      color: white;
    }
  `}

  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
`;

const ErrorList = styled.ul`
  margin: 0;
  padding-left: 1.5rem;
  text-align: left;

  li {
    margin: 0.25rem 0;
  }
`;

// Get icon based on variant
const getIcon = (variant) => {
  switch (variant) {
    case 'danger': return '❌';
    case 'warning': return '⚠️';
    case 'info': return 'ℹ️';
    default: return '❌';
  }
};

// Main ErrorMessage component
const ErrorMessageComponent = ({
  title = null,
  message = null,
  errors = null,
  variant = 'danger',
  size = 'medium',
  fullWidth = false,
  minHeight = null,
  showIcon = true,
  onRetry = null,
  onDismiss = null,
  retryText = 'Réessayer',
  dismissText = 'Fermer',
  children = null,
}) => {
  const hasMessage = message || errors || children;
  const hasActions = onRetry || onDismiss;

  const renderContent = () => {
    if (children) {
      return children;
    }

    if (errors) {
      if (Array.isArray(errors)) {
        return (
          <ErrorList>
            {errors.map((error, index) => (
              <li key={index}>
                {typeof error === 'string' ? error : error.message || 'Erreur inconnue'}
              </li>
            ))}
          </ErrorList>
        );
      } else if (typeof errors === 'object') {
        return (
          <ErrorList>
            {Object.entries(errors).map(([field, fieldErrors]) => (
              <li key={field}>
                <strong>{field}:</strong>{' '}
                {Array.isArray(fieldErrors) ? fieldErrors.join(', ') : fieldErrors}
              </li>
            ))}
          </ErrorList>
        );
      }
    }

    return message;
  };

  return (
    <ErrorContainer
      variant={variant}
      size={size}
      fullWidth={fullWidth}
      minHeight={minHeight}
    >
      {showIcon && (
        <ErrorIcon size={size}>
          {getIcon(variant)}
        </ErrorIcon>
      )}

      {title && (
        <ErrorTitle size={size} hasMessage={hasMessage}>
          {title}
        </ErrorTitle>
      )}

      {hasMessage && (
        <ErrorMessage size={size} hasActions={hasActions}>
          {renderContent()}
        </ErrorMessage>
      )}

      {hasActions && (
        <ErrorActions>
          {onRetry && (
            <ErrorButton
              variant="primary"
              onClick={onRetry}
              type="button"
            >
              {retryText}
            </ErrorButton>
          )}
          {onDismiss && (
            <ErrorButton
              variant="secondary"
              onClick={onDismiss}
              type="button"
            >
              {dismissText}
            </ErrorButton>
          )}
        </ErrorActions>
      )}
    </ErrorContainer>
  );
};

// Predefined error components
export const NetworkError = ({ onRetry }) => (
  <ErrorMessageComponent
    title="Erreur de connexion"
    message="Impossible de se connecter au serveur. Vérifiez votre connexion internet."
    variant="danger"
    onRetry={onRetry}
  />
);

export const NotFoundError = ({ message = "La ressource demandée n'a pas été trouvée." }) => (
  <ErrorMessageComponent
    title="Ressource introuvable"
    message={message}
    variant="warning"
    showIcon={true}
  />
);

export const ValidationError = ({ errors }) => (
  <ErrorMessageComponent
    title="Erreurs de validation"
    errors={errors}
    variant="warning"
    showIcon={true}
  />
);

export const PermissionError = () => (
  <ErrorMessageComponent
    title="Accès refusé"
    message="Vous n'avez pas les permissions nécessaires pour accéder à cette ressource."
    variant="danger"
    showIcon={true}
  />
);

export const MaintenanceError = () => (
  <ErrorMessageComponent
    title="Maintenance en cours"
    message="Le service est temporairement indisponible pour maintenance. Veuillez réessayer plus tard."
    variant="info"
    showIcon={true}
  />
);

// Inline error for forms
export const InlineError = ({ message }) => (
  <div style={{
    color: '#dc3545',
    fontSize: '0.875rem',
    marginTop: '0.25rem',
    display: 'flex',
    alignItems: 'center',
    gap: '0.25rem',
  }}>
    <span>⚠️</span>
    {message}
  </div>
);

// Error boundary fallback
export const ErrorBoundaryFallback = ({ error, resetError }) => (
  <ErrorMessageComponent
    title="Une erreur inattendue s'est produite"
    message="L'application a rencontré une erreur inattendue. Veuillez recharger la page."
    variant="danger"
    size="large"
    fullWidth={true}
    minHeight="300px"
    onRetry={resetError}
    retryText="Recharger"
  >
    {process.env.NODE_ENV === 'development' && (
      <details style={{ marginTop: '1rem', textAlign: 'left' }}>
        <summary>Détails de l'erreur (mode développement)</summary>
        <pre style={{
          background: '#f8f9fa',
          padding: '1rem',
          borderRadius: '4px',
          overflow: 'auto',
          fontSize: '0.75rem',
          marginTop: '0.5rem',
        }}>
          {error?.stack || error?.message || 'Erreur inconnue'}
        </pre>
      </details>
    )}
  </ErrorMessageComponent>
);

// Page-level error
export const PageError = ({ title, message, onRetry }) => (
  <div style={{
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    minHeight: '50vh',
    padding: '2rem',
  }}>
    <ErrorMessageComponent
      title={title}
      message={message}
      variant="danger"
      size="large"
      onRetry={onRetry}
      fullWidth={false}
    />
  </div>
);

// Section-level error
export const SectionError = ({ message, onRetry }) => (
  <div style={{
    background: '#f8f9fa',
    borderRadius: '8px',
    border: '1px solid #dee2e6',
    padding: '2rem',
    textAlign: 'center',
  }}>
    <ErrorMessageComponent
      message={message}
      variant="warning"
      onRetry={onRetry}
      fullWidth={true}
    />
  </div>
);

export default ErrorMessageComponent;
