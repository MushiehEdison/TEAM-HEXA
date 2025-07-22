import React, { createContext, useContext, useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import EditProfile from './components/profile';
import History from './components/history';
import AllVisits from './components/visits';
import Home from './home';
import SignUp from './components/SignUp';
import SignIn from './components/SignIn';

const AuthContext = createContext();

export const useAuth = () => useContext(AuthContext);

const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(localStorage.getItem('token') || null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    console.log('AuthProvider useEffect: Checking token', token);
    if (token) {
      fetch('http://localhost:5000/api/auth/verify', {
        method: 'GET',
        headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' },
      })
        .then((res) => {
          console.log('Verify response status:', res.status);
          if (!res.ok) {
            throw new Error(`Verification failed: ${res.status}`);
          }
          return res.json();
        })
        .then((data) => {
          console.log('Verify response data:', data);
          if (data.user) {
            setUser(data.user); // Set user data
            setToken(token); // Ensure token is set
          } else {
            console.error('No user data in verify response:', data);
            localStorage.removeItem('token');
            setToken(null);
          }
        })
        .catch((error) => {
          console.error('Verify error:', error.message);
          localStorage.removeItem('token');
          setToken(null);
        })
        .finally(() => {
          setLoading(false);
          console.log('Auth loading complete, user:', user, 'token:', token);
        });
    } else {
      setLoading(false);
      console.log('No token found, auth loading complete');
    }
  }, [token]); // Dependency on token only

  const login = (newToken, userData) => {
    console.log('Logging in with token:', newToken, 'user:', userData);
    localStorage.setItem('token', newToken);
    setToken(newToken);
    setUser(userData);
  };

  const logout = () => {
    console.log('Logging out');
    localStorage.removeItem('token');
    setToken(null);
    setUser(null);
  };

  return (
    <AuthContext.Provider value={{ user, token, login, logout, loading }}>
      {children}
    </AuthContext.Provider>
  );
};

const ProtectedRoute = ({ children }) => {
  const { user, loading } = useAuth();

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-transparent">
        <div className="flex flex-col items-center space-y-4">
          <div className="spinner border-4 border-gray-300 border-t-gray-800 rounded-full w-12 h-12 animate-spin"></div>
          <p className="text-gray-600 text-lg font-semibold animate-pulse">Loading Your Experience...</p>
        </div>
      </div>
    );
  }

  if (!user) {
    console.log('ProtectedRoute: No user, redirecting to /signin');
    return <Navigate to="/signin" replace />;
  }

  return children;
};

const App = () => {
  return (
    <AuthProvider>
      <Router>
        <main className="pt-16 pb-20">
          <Routes>
            <Route path="/signin" element={<SignIn />} />
            <Route path="/signup" element={<SignUp />} />
            <Route
              path="/"
              element={
                <ProtectedRoute>
                  <Home />
                </ProtectedRoute>
              }
            />
            <Route
              path="/chat"
              element={
                <ProtectedRoute>
                  <Home />
                </ProtectedRoute>
              }
            />
            <Route
              path="/chat/:conversationId"
              element={
                <ProtectedRoute>
                  <Home />
                </ProtectedRoute>
              }
            />
            <Route
              path="/profile"
              element={
                <ProtectedRoute>
                  <EditProfile />
                </ProtectedRoute>
              }
            />
            <Route
              path="/profile/edit"
              element={
                <ProtectedRoute>
                  <EditProfile isEditing={true} />
                </ProtectedRoute>
              }
            />
            <Route
              path="/visits"
              element={
                <ProtectedRoute>
                  <AllVisits />
                </ProtectedRoute>
              }
            />
            <Route
              path="/history"
              element={
                <ProtectedRoute>
                  <History />
                </ProtectedRoute>
              }
            />
          </Routes>
        </main>
      </Router>
    </AuthProvider>
  );
};

const styles = `
  .spinner {
    animation: spin 1s linear infinite;
  }
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
`;

const styleSheet = document.createElement('style');
styleSheet.textContent = styles;
document.head.appendChild(styleSheet);

export default App;