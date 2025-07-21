import React, { useState, useEffect, useRef } from "react";
import { Calendar, Clock, MessageCircle, Search, X, Send } from 'lucide-react';
import { useAuth } from '../App';

const History = () => {
  const { user, token, loading: authLoading } = useAuth();
  const [conversations, setConversations] = useState([]);
  const [selectedConversation, setSelectedConversation] = useState(null);
  const [searchTerm, setSearchTerm] = useState('');
  const [newMessage, setNewMessage] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const messageEndRef = useRef(null);

  useEffect(() => {
    if (selectedConversation) {
      messageEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }
  }, [selectedConversation?.messages]);

  useEffect(() => {
    console.log('Auth state:', { user, token, authLoading }); // Debug auth state
    const fetchConversations = async () => {
      if (!token || authLoading) {
        console.log('No token or still loading auth, skipping fetch');
        return;
      }
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
        console.log('Fetched conversations:', conversations); // Debug response
        const transformedConversations = conversations.map(conv => ({
          id: conv.id,
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
          preview: conv.preview || 'No messages yet',
          message_count: conv.message_count || 0,
          messages: Array.isArray(conv.messages) ? conv.messages : []
        }));
        setConversations(prev => page === 1 ? transformedConversations : [...prev, ...transformedConversations]);
        setHasMore(page < pages);
      } catch (err) {
        console.error('Fetch conversations error:', err.message);
        setError(err.message || 'Failed to load conversations');
      } finally {
        setLoading(false);
      }
    };
    fetchConversations();
  }, [token, authLoading, page]);

  const filteredConversations = conversations.filter(conversation =>
    conversation.preview.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const handleSendMessage = async (conversationId) => {
    if (!newMessage.trim()) return;
    if (!token) {
      setError('Please sign in to send messages');
      return;
    }
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(`http://localhost:5000/api/auth/conversation/${conversationId}`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ message: newMessage })
      });
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to send message');
      }
      const { id, created_at, updated_at, messages, preview } = await response.json();
      setConversations(prev =>
        prev.map(conv =>
          conv.id === conversationId
            ? {
                ...conv,
                date: new Date(updated_at || created_at).toLocaleDateString('en-US', {
                  year: 'numeric',
                  month: 'long',
                  day: 'numeric'
                }),
                time: new Date(updated_at || created_at).toLocaleTimeString('en-US', {
                  hour: 'numeric',
                  minute: 'numeric',
                  hour12: true
                }),
                preview,
                message_count: messages.length,
                messages
              }
            : conv
        ).sort((a, b) => new Date(b.updated_at || b.created_at) - new Date(a.updated_at || a.created_at))
      );
      setSelectedConversation({
        id,
        date: new Date(updated_at || created_at).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        }),
        time: new Date(updated_at || created_at).toLocaleTimeString('en-US', {
          hour: 'numeric',
          minute: 'numeric',
          hour12: true
        }),
        preview,
        message_count: messages.length,
        messages
      });
      setNewMessage('');
    } catch (err) {
      console.error('Send message error:', err.message);
      setError(err.message || 'Failed to send message');
    } finally {
      setLoading(false);
    }
  };

  const handleNewConversation = async () => {
    if (!token) {
      setError('Please sign in to start a new conversation');
      return;
    }
    setLoading(true);
    setError(null);
    try {
      const response = await fetch('http://localhost:5000/api/auth/conversation/new', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      });
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to start new conversation');
      }
      const { id, created_at, messages, preview } = await response.json();
      const newConv = {
        id,
        date: new Date(created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }),
        time: new Date(created_at).toLocaleTimeString('en-US', { hour: 'numeric', minute: 'numeric', hour12: true }),
        preview,
        message_count: messages.length,
        messages
      };
      setConversations([newConv, ...conversations].sort((a, b) => new Date(b.updated_at || b.created_at) - new Date(a.updated_at || a.created_at)));
      setSelectedConversation(newConv);
      setPage(1);
    } catch (err) {
      console.error('New conversation error:', err.message);
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
              className={`border border-gray-200 outline outline-1 outline-gray-200 rounded-lg py-4 sm:py-6 px-4 sm:px-6 hover:bg-gray-50 transition-colors cursor-pointer ${
                selectedConversation?.id === conversation.id ? 'bg-blue-50 outline-blue-300' : ''
              }`}
              onClick={() => setSelectedConversation(conversation)}
              role="button"
              tabIndex={0}
              onKeyPress={(e) => e.key === 'Enter' && setSelectedConversation(conversation)}
              aria-label={`Select conversation ${conversation.id}`}
            >
              <div className="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
                <div className="flex-1">
                  <div className="flex items-center gap-2 mb-3">
                    <MessageCircle className="h-5 w-5 text-blue-600" aria-hidden="true" />
                    <span className="font-medium text-gray-900">Conversation {conversation.id}</span>
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
              <div className="flex justify-between items-center mt-4">
                <span className="text-sm text-gray-500">{conversation.message_count} messages</span>
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

      {selectedConversation && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-start sm:items-center justify-center z-50">
          <div className="bg-white w-full sm:max-w-4xl rounded-t-[2rem] sm:rounded-lg max-h-[100vh] sm:max-h-[80vh] overflow-y-auto">
            <div className="flex justify-between items-center p-4 sm:p-6 border-b border-gray-200 outline outline-1 outline-gray-200">
              <h3 className="text-lg sm:text-xl font-bold text-gray-900">
                Conversation {selectedConversation.id} - {selectedConversation.date}
              </h3>
              <button
                onClick={() => setSelectedConversation(null)}
                className="p-2 text-gray-500 hover:text-gray-700 rounded-full outline outline-1 outline-gray-200"
                aria-label="Close conversation details"
              >
                <X className="h-5 w-5" />
              </button>
            </div>
            <div className="p-4 sm:p-6">
              <div className="space-y-4">
                {(selectedConversation.messages || []).map((message) => (
                  <div
                    key={message.id}
                    className={`flex ${message.isUser ? 'justify-end' : 'justify-start'}`}
                  >
                    <div
                      className={`max-w-[80%] sm:max-w-xs lg:max-w-md px-4 py-3 rounded-lg outline outline-1 ${
                        message.isUser
                          ? 'bg-blue-600 text-white outline-blue-200'
                          : 'bg-gray-100 text-gray-800 outline-gray-200'
                      }`}
                    >
                      <p className="text-sm">{message.text}</p>
                      <p
                        className={`text-xs mt-1 ${
                          message.isUser ? 'text-blue-200' : 'text-gray-500'
                        }`}
                      >
                        {new Date(message.timestamp).toLocaleTimeString('en-US', { hour: 'numeric', minute: 'numeric', hour12: true })}
                      </p>
                    </div>
                  </div>
                ))}
                <div ref={messageEndRef} />
              </div>
            </div>
            <div className="p-4 sm:p-6 border-t border-gray-200 outline outline-1 outline-gray-200">
              <div className="flex items-center gap-2">
                <input
                  type="text"
                  placeholder="Type your message..."
                  className="flex-1 px-4 py-3 border border-gray-300 outline outline-1 outline-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors"
                  value={newMessage}
                  onChange={(e) => setNewMessage(e.target.value)}
                  onKeyPress={(e) => e.key === 'Enter' && handleSendMessage(selectedConversation.id)}
                  aria-label="Type a message"
                  disabled={loading || authLoading}
                />
                <button
                  onClick={() => handleSendMessage(selectedConversation.id)}
                  className="px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 outline outline-1 outline-blue-200 transition-colors disabled:opacity-50"
                  disabled={loading || authLoading || !newMessage.trim()}
                  aria-label="Send message"
                >
                  <Send className="h-5 w-5" />
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default History;