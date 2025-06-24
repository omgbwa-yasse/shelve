import React, { Suspense } from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from 'react-query';
import { ToastContainer } from 'react-toastify';
import styled, { ThemeProvider } from 'styled-components';

// Styles
import 'react-toastify/dist/ReactToastify.css';
import './styles/globals.css';

// Contexts
import { AuthProvider } from './context/AuthContext';
import { ChatProvider } from './context/ChatContext';

// Components
import Header from './components/common/Header';
import Footer from './components/common/Footer';
import { PageLoading } from './components/common/Loading';
import ErrorMessageComponent, { ErrorBoundaryFallback } from './components/common/ErrorMessage';

// Lazy loaded pages
const HomePage = React.lazy(() => import('./components/pages/HomePage'));
const EventsPage = React.lazy(() => import('./components/pages/EventsPage'));
const EventDetail = React.lazy(() => import('./components/pages/EventDetail'));
const NewsPage = React.lazy(() => import('./components/pages/NewsPage'));
const NewsDetail = React.lazy(() => import('./components/pages/NewsDetail'));
const RecordsPage = React.lazy(() => import('./components/pages/RecordsPage'));
const RecordDetail = React.lazy(() => import('./components/pages/RecordDetail'));
const DocumentRequestPage = React.lazy(() => import('./components/pages/DocumentRequestPage'));
const FeedbackPage = React.lazy(() => import('./components/pages/FeedbackPage'));
const UserDashboard = React.lazy(() => import('./components/pages/UserDashboard'));
const ChatPage = React.lazy(() => import('./components/pages/ChatPage'));

// Theme configuration
const theme = {
  colors: {
    primary: '#007bff',
    secondary: '#6c757d',
    success: '#28a745',
    danger: '#dc3545',
    warning: '#ffc107',
    info: '#17a2b8',
    light: '#f8f9fa',
    dark: '#343a40',
    white: '#ffffff',
    black: '#000000',
  },
  fonts: {
    primary: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
    secondary: "'Arial', sans-serif",
  },
  spacing: {
    xs: '0.25rem',
    sm: '0.5rem',
    md: '1rem',
    lg: '1.5rem',
    xl: '2rem',
    xxl: '3rem',
  },
  borderRadius: {
    sm: '4px',
    md: '8px',
    lg: '12px',
    xl: '16px',
    round: '50%',
  },
  shadows: {
    sm: '0 1px 3px rgba(0,0,0,0.12)',
    md: '0 4px 6px rgba(0,0,0,0.1)',
    lg: '0 10px 25px rgba(0,0,0,0.1)',
    xl: '0 20px 40px rgba(0,0,0,0.1)',
  },
  breakpoints: {
    mobile: '768px',
    tablet: '1024px',
    desktop: '1200px',
  },
};

// Styled components
const AppContainer = styled.div`
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  font-family: ${props => props.theme.fonts.primary};
  color: ${props => props.theme.colors.dark};
  background-color: ${props => props.theme.colors.light};
`;

const MainContent = styled.main`
  flex: 1;
  padding-top: 2rem;
  padding-bottom: 2rem;

  @media (max-width: ${props => props.theme.breakpoints.mobile}) {
    padding-top: 1rem;
    padding-bottom: 1rem;
  }
`;

// React Query client
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 2,
      refetchOnWindowFocus: false,
      staleTime: 5 * 60 * 1000, // 5 minutes
    },
    mutations: {
      retry: 1,
    },
  },
});

// Error Boundary Component
class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, error: null };
  }

  static getDerivedStateFromError(error) {
    return { hasError: true, error };
  }

  componentDidCatch(error, errorInfo) {
    console.error('Error caught by boundary:', error, errorInfo);
  }

  render() {
    if (this.state.hasError) {
      return (
        <ErrorBoundaryFallback
          error={this.state.error}
          resetError={() => this.setState({ hasError: false, error: null })}
        />
      );
    }

    return this.props.children;
  }
}

// Route wrapper with error boundary
const RouteWrapper = ({ children }) => (
  <ErrorBoundary>
    <Suspense fallback={<PageLoading />}>
      {children}
    </Suspense>
  </ErrorBoundary>
);

// App component
function App() {
  return (
    <ErrorBoundary>
      <QueryClientProvider client={queryClient}>
        <ThemeProvider theme={theme}>
          <AuthProvider>
            <ChatProvider>
              <Router future={{
                v7_relativeSplatPath: true,
                v7_startTransition: true
              }}>
                <AppContainer>
                  <Header />

                  <MainContent>
                    <Routes>
                      {/* Home */}
                      <Route
                        path="/"
                        element={
                          <RouteWrapper>
                            <HomePage />
                          </RouteWrapper>
                        }
                      />

                      {/* Events */}
                      <Route
                        path="/events"
                        element={
                          <RouteWrapper>
                            <EventsPage />
                          </RouteWrapper>
                        }
                      />
                      <Route
                        path="/events/:id"
                        element={
                          <RouteWrapper>
                            <EventDetail />
                          </RouteWrapper>
                        }
                      />

                      {/* News */}
                      <Route
                        path="/news"
                        element={
                          <RouteWrapper>
                            <NewsPage />
                          </RouteWrapper>
                        }
                      />
                      <Route
                        path="/news/:id"
                        element={
                          <RouteWrapper>
                            <NewsDetail />
                          </RouteWrapper>
                        }
                      />

                      {/* Records */}
                      <Route
                        path="/records"
                        element={
                          <RouteWrapper>
                            <RecordsPage />
                          </RouteWrapper>
                        }
                      />
                      <Route
                        path="/records/:id"
                        element={
                          <RouteWrapper>
                            <RecordDetail />
                          </RouteWrapper>
                        }
                      />

                      {/* Documents */}
                      <Route
                        path="/documents/request"
                        element={
                          <RouteWrapper>
                            <DocumentRequestPage />
                          </RouteWrapper>
                        }
                      />

                      {/* Feedback */}
                      <Route
                        path="/feedback"
                        element={
                          <RouteWrapper>
                            <FeedbackPage />
                          </RouteWrapper>
                        }
                      />

                      {/* User */}
                      <Route
                        path="/user/dashboard"
                        element={
                          <RouteWrapper>
                            <UserDashboard />
                          </RouteWrapper>
                        }
                      />

                      {/* Chat */}
                      <Route
                        path="/chat"
                        element={
                          <RouteWrapper>
                            <ChatPage />
                          </RouteWrapper>
                        }
                      />

                      {/* 404 */}
                      <Route
                        path="*"
                        element={
                          <ErrorMessageComponent
                            title="Page non trouvée"
                            message="La page que vous recherchez n'existe pas."
                            variant="warning"
                            size="large"
                          />
                        }
                      />
                    </Routes>
                  </MainContent>

                  <Footer />
                </AppContainer>
              </Router>

              {/* Toast notifications */}
              <ToastContainer
                position="top-right"
                autoClose={5000}
                hideProgressBar={false}
                newestOnTop={false}
                closeOnClick
                rtl={false}
                pauseOnFocusLoss
                draggable
                pauseOnHover
                theme="light"
              />
            </ChatProvider>
          </AuthProvider>
        </ThemeProvider>
      </QueryClientProvider>
    </ErrorBoundary>
  );
}

export default App;
