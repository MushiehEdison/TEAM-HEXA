import React, { useState, useContext } from 'react';
import { Mic, Send, User, Heart, Menu, X, Edit3, Moon, Sun, Phone, Calendar, Activity } from 'lucide-react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../App';
import { ChatContext } from '../../home';

const Sidebar = ({ isOpen, onClose, isDarkMode }) => {
  const { user, logout } = useAuth();
  const { resetMessages } = useContext(ChatContext);
  const navigate = useNavigate();
  const [error, setError] = useState(null);

  const handleNewChat = async () => {
    try {
      const token = localStorage.getItem('token');
      if (!token) {
        throw new Error('No authentication token found');
      }

      const response = await fetch('http://localhost:5000/api/auth/conversation', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify({ message: '', isMicInput: false }), // Send empty message to create new conversation
      });

      const data = await response.json();
      if (!response.ok) {
        throw new Error(data.message || 'Failed to start new conversation');
      }

      resetMessages();
      navigate(`/chat/${data.id}`);
      setError(null);
    } catch (err) {
      setError(err.message);
      console.error('Error creating new chat:', err.message);
    }
  };

  const handleLogout = () => {
    logout();
    navigate('/signin');
  };

  return (
    <>
      {isOpen && (
        <div className="fixed inset-0 bg-black bg-opacity-50 z-40" onClick={onClose} />
      )}
      
      <div className={`fixed top-0 left-0 h-full w-80 max-w-xs transform transition-transform duration-300 z-50 ${
        isOpen ? 'translate-x-0' : '-translate-x-full'
      } ${isDarkMode ? 'bg-gray-800' : 'bg-white'} shadow-xl`}>
        
        <div className="p-4 border-b border-gray-200">
          <div className="flex items-center justify-between">
            <h2 className={`text-lg font-semibold ${isDarkMode ? 'text-white' : 'text-gray-800'}`}>
              Patient Profile
            </h2>
            <button
              onClick={onClose}
              className={`p-2 rounded-full ${isDarkMode ? 'hover:bg-gray-700' : 'hover:bg-gray-100'} transition-colors`}
            >
              <X className={`w-5 h-5 ${isDarkMode ? 'text-white' : 'text-gray-700'}`} />
            </button>
          </div>
        </div>
        
        <div className="p-4 space-y-6">
          <div className="flex items-center space-x-4">
            <div className="w-16 h-16 bg-gradient-to-br from-pink-400 to-purple-500 rounded-full flex items-center justify-center">
              <User className="w-8 h-8 text-white" />
            </div>
            <div>
              <h3 className={`text-lg font-medium ${isDarkMode ? 'text-white' : 'text-gray-800'}`}>
                {user ? user.name : 'Guest'}
              </h3>
              <p className={`text-sm ${isDarkMode ? 'text-gray-300' : 'text-gray-600'}`}>
                {user ? user.email : 'Not logged in'}
              </p>
            </div>
          </div>
          
          <div className="space-y-4">
            <div className={`p-3 rounded-lg ${isDarkMode ? 'bg-gray-700' : 'bg-gray-50'}`}>
              <Link to="/history" className="flex items-center space-x-2 mb-1">
                <Activity className="w-4 h-4 text-blue-500" />
                <span className={`text-sm font-medium ${isDarkMode ? 'text-white' : 'text-gray-800'}`}>
                  Conversation History
                </span>
              </Link>
            </div>
            
            <div className={`p-3 rounded-lg ${isDarkMode ? 'bg-gray-700' : 'bg-gray-50'}`}>
              <Link to="/visits" className="flex items-center space-x-2 mb-1">
                <Calendar className="w-4 h-4 text-green-500" />
                <span className={`text-sm font-medium ${isDarkMode ? 'text-white' : 'text-gray-800'}`}>
                  Last Visit
                </span>
              </Link>
            </div>
            
            <div className={`p-3 rounded-lg ${isDarkMode ? 'bg-gray-700' : 'bg-gray-50'}`}>
              <div className="flex items-center space-x-2 mb-1">
                <Phone className="w-4 h-4 text-purple-500" />
                <span className={`text-sm font-medium ${isDarkMode ? 'text-white' : 'text-gray-800'}`}>
                  Language
                </span>
              </div>
            </div>
          </div>
          
          <div className="space-y-3">
            <button 
              onClick={handleNewChat}
              className="w-full bg-blue-500 hover:bg-blue-600 text-white py-3 px-4 rounded-lg flex items-center justify-center space-x-2 transition-colors"
            >
              <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <span className="font-medium">NEW CHAT</span>
            </button>
            
            <Link to="/profile" className="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 px-4 rounded-lg flex items-center justify-center space-x-2 transition-colors">
              <Edit3 className="w-4 h-4" />
              <span className="font-medium">Edit Profile</span>
            </Link>

            <button
              onClick={handleLogout}
              className="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 px-4 rounded-lg flex items-center justify-center space-x-2 transition-colors"
            >
              <span className="font-medium">Logout</span>
            </button>
          </div>

          {error && (
            <p className={`text-red-500 text-sm mt-2 animate-fade-in ${isDarkMode ? 'text-red-400' : 'text-red-500'}`}>
              {error}
            </p>
          )}
        </div>
      </div>
    </>
  );
};

export default Sidebar;