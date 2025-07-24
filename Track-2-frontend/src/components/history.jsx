import React, { useState, useEffect } from "react";
import { Calendar, Clock, MessageCircle, Search } from 'lucide-react';
import { useAuth } from '../App';
import { useNavigate } from 'react-router-dom';

const History = () => {
  const { user, token, loading: authLoading } = useAuth();
  const [conversations, setConversations] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    const fetchConversations = async () => {
      if (!token || authLoading) return;
      setLoading(true);
      setError(null);
      try {
        const response = await fetch(`http://localhost:5000/api/auth/conversations?page=${page}&per_page=10`, {
          method: 'GET',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });
        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.message || 'Failed to fetch conversations');
        }
        const { conversations, total, pages } = await response.json();
        const transformedConversations = conversations.map(conv => ({
          id: conv.id,
          title: conv.title,
          date: new Date(conv.updated_at || conv.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
          }),
          time: new Date(conv.updated_at || conv.created_at).toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: 'numeric',
            hour12: true
          }),
          preview: conv.preview
        }));
        setConversations(prev => page === 1 ? transformedConversations : [...prev, ...transformedConversations]);
        setHasMore(page < pages);
      } catch (err) {
        setError(err.message || 'Failed to load conversations');
      } finally {
        setLoading(false);
      }
    };
    fetchConversations();
  }, [token, authLoading, page]);

  const filteredConversations = conversations.filter(conversation =>
    conversation.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
    conversation.preview.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const handleNewConversation = async () => {
    if (!token) {
      setError('Please sign in to start a new conversation');
      return;
    }
    setLoading(true);
    setError(null);
    try {
      const response = await fetch('http://localhost:5000/api/auth/conversation', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ message: '', isMicInput: false })
      });
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to start new conversation');
      }
      const data = await response.json();
      navigate(`/chat/${data.id}`);
    } catch (err) {
      setError(err.message || 'Failed to start new conversation');
    } finally {
      setLoading(false);
    }
  };

  const handleLoadMore = () => {
    if (hasMore && !loading) {
      setPage(prev => prev + 1);
    }
  };

  return (
    <div className="max-w-6xl mx-auto sm:px-6 lg:px-8 sm:py-6">
      <div className="sm:mb-6 pt-4 sm:pt-0">
        <div className="flex justify-between items-center px-4 sm:px-0">
          <h2 className="text-2xl sm:text-3xl font-bold text-gray-900">Conversation History</h2>
          <button
            onClick={handleNewConversation}
            className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 outline outline-1 outline-blue-200 transition-colors text-sm"
            disabled={loading || authLoading}
            aria-label="Start a new conversation"
          >
            New Conversation
          </button>
        </div>
        <div className="mt-4 sm:mt-6 relative px-4 sm:px-0">
          <Search className="absolute left-7 sm:left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
          <input
            type="text"
            placeholder="Search conversations..."
            className="w-full pl-14 sm:pl-10 pr-4 py-3 border border-gray-300 outline outline-1 outline-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            aria-label="Search conversations"
          />
        </div>
      </div>

      {error && (
        <div className="px-4 sm:px-0 mb-4">
          <p className="text-red-600 text-sm">{error}</p>
        </div>
      )}

      {(loading || authLoading) && page === 1 && (
        <div className="px-4 sm:px-0 text-center">
          <p className="text-gray-500">Loading...</p>
        </div>
      )}

      {!(loading || authLoading) && filteredConversations.length === 0 && (
        <div className="px-4 sm:px-0 text-center">
          <p className="text-gray-500">No conversations found.</p>
        </div>
      )}
      {!(loading || authLoading) && filteredConversations.length > 0 && (
        <div className="grid gap-4 sm:gap-6 px-4 sm:px-0">
          {filteredConversations.map((conversation) => (
            <div
              key={conversation.id}
              className="border border-gray-200 outline outline-1 outline-gray-200 rounded-lg py-4 sm:py-6 px-4 sm:px-6 hover:bg-gray-50 transition-colors cursor-pointer"
              onClick={() => navigate(`/chat/${conversation.id}`)}
              role="button"
              tabIndex={0}
              onKeyPress={(e) => e.key === 'Enter' && navigate(`/chat/${conversation.id}`)}
              aria-label={`Select conversation ${conversation.title}`}
            >
              <div className="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
                <div className="flex-1">
                  <div className="flex items-center gap-2 mb-3">
                    <MessageCircle className="h-5 w-5 text-blue-600" aria-hidden="true" />
                    <span className="font-medium text-gray-900">{conversation.title}</span>
                  </div>
                  <p className="text-gray-600 text-sm line-clamp-2">{conversation.preview}</p>
                </div>
                <div className="flex flex-col sm:text-right gap-2">
                  <div className="flex items-center sm:justify-end gap-2 text-sm text-gray-500">
                    <Calendar className="h-4 w-4" aria-hidden="true" />
                    <span>{conversation.date}</span>
                  </div>
                  <div className="flex items-center sm:justify-end gap-2 text-sm text-gray-500">
                    <Clock className="h-4 w-4" aria-hidden="true" />
                    <span>{conversation.time}</span>
                  </div>
                </div>
              </div>
            </div>
          ))}
          {hasMore && (
            <div className="text-center mt-4">
              <button
                onClick={handleLoadMore}
                className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 outline outline-1 outline-blue-200 transition-colors text-sm"
                disabled={loading || authLoading}
                aria-label="Load more conversations"
              >
                {loading ? 'Loading...' : 'Load More'}
              </button>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default History;