import React, { useState, useEffect, useRef, createContext } from 'react';
import { Mic, Send, User, Heart, Menu, X, Edit3, Moon, Sun, Phone, Calendar, Activity, WifiOff } from 'lucide-react';
import { Link } from 'react-router-dom';
import SpeechRecognition, { useSpeechRecognition } from 'react-speech-recognition';
import StatusIndicator from './components/homepagecomponents/StatusIndicator';
import MessageBubble from './components/homepagecomponents/MessageBubble';
import Sidebar from './components/homepagecomponents/Sidebar';
import InputArea from './components/homepagecomponents/InputArea';
import Navbar from './components/homepagecomponents/Navbar';
import { useAuth } from './App';

// Create a context for resetting messages
export const ChatContext = createContext();

const Home = () => {
  const { user, loading } = useAuth();
  const [messages, setMessages] = useState(() => {
    // Load messages from localStorage if available
    const savedMessages = localStorage.getItem('chatMessages');
    return savedMessages
      ? JSON.parse(savedMessages)
      : [
          {
            id: 'msg-1',
            text: "Hello! I'm your AI healthcare assistant. How can I help you today?",
            isUser: false,
            timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
          },
        ];
  });
  const [isListening, setIsListening] = useState(false);
  const [status, setStatus] = useState('');
  const [showStatus, setShowStatus] = useState(false);
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [isDarkMode, setIsDarkMode] = useState(false);
  const [networkStatus, setNetworkStatus] = useState(navigator.onLine ? 'online' : 'offline');
  const messagesEndRef = useRef(null);
  const silenceTimerRef = useRef(null);
  const messageIdCounter = useRef(messages.length + 1); // Initialize based on loaded messages

  const { transcript, interimTranscript, finalTranscript, resetTranscript, listening } = useSpeechRecognition();

  const generateUniqueId = () => {
    return `msg-${messageIdCounter.current++}`;
  };

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  // Function to reset messages to the default state
  const resetMessages = () => {
    const defaultMessage = {
      id: 'msg-1',
      text: "Hello! I'm your AI healthcare assistant. How can I help you today?",
      isUser: false,
      timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    };
    setMessages([defaultMessage]);
    messageIdCounter.current = 2;
    localStorage.removeItem('chatMessages'); // Clear saved messages
  };

  // Function to handle microphone toggle
  const handleToggleListening = () => {
    if (networkStatus === 'offline') {
      return; // Don't allow voice input when offline
    }

    if (isListening) {
      setIsListening(false);
      SpeechRecognition.stopListening();
      resetTranscript();
      if (silenceTimerRef.current) {
        clearTimeout(silenceTimerRef.current);
      }
    } else {
      setIsListening(true);
      resetTranscript();
      SpeechRecognition.startListening({ 
        continuous: true, 
        interimResults: true, 
        language: 'en-US' 
      });
    }
  };

  // Save messages to localStorage whenever they change
  useEffect(() => {
    localStorage.setItem('chatMessages', JSON.stringify(messages));
  }, [messages]);

  // Fetch messages from server only if localStorage is empty
  useEffect(() => {
    if (!user || loading) return;
    const savedMessages = localStorage.getItem('chatMessages');
    if (savedMessages && JSON.parse(savedMessages).length > 1) {
      // If local messages exist and are more than the default message, skip server fetch
      return;
    }

    const token = localStorage.getItem('token');
    fetch('http://localhost:5000/api/auth/conversation', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
      },
    })
      .then((res) => {
        if (!res.ok) throw new Error('Failed to fetch messages');
        return res.json();
      })
      .then((data) => {
        const fetchedMessages = data.messages.length
          ? data.messages.map((msg, index) => ({
              ...msg,
              id: `msg-${index + 1}`,
            }))
          : [
              {
                id: 'msg-1',
                text: "Hello! I'm your AI healthcare assistant. How can I help you today?",
                isUser: false,
                timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
              },
            ];
        setMessages(fetchedMessages);
        messageIdCounter.current = fetchedMessages.length + 1;
      })
      .catch((error) => {
        console.error('Error fetching messages:', error);
        // Fallback to default message if fetch fails
        if (!savedMessages) {
          setMessages([
            {
              id: 'msg-1',
              text: "Hello! I'm your AI healthcare assistant. How can I help you today?",
              isUser: false,
              timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            },
          ]);
          messageIdCounter.current = 2;
        }
      });
  }, [user, loading]);

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  useEffect(() => {
    if (finalTranscript.trim()) {
      if (silenceTimerRef.current) {
        clearTimeout(silenceTimerRef.current);
      }
      silenceTimerRef.current = setTimeout(() => {
        handleSendMessage(finalTranscript.trim());
        resetTranscript();
        if (isListening && networkStatus === 'online') {
          SpeechRecognition.startListening({ continuous: true, interimResults: true, language: 'en-US' });
        }
      }, 3000); // 3 seconds of silence
    }
  }, [finalTranscript, isListening, networkStatus]);

  useEffect(() => {
    const handleNetworkChange = () => {
      setNetworkStatus(navigator.onLine ? 'online' : 'offline');
      if (!navigator.onLine && isListening) {
        setIsListening(false);
        SpeechRecognition.stopListening();
        resetTranscript();
        if (silenceTimerRef.current) {
          clearTimeout(silenceTimerRef.current);
        }
      }
    };

    window.addEventListener('online', handleNetworkChange);
    window.addEventListener('offline', handleNetworkChange);

    return () => {
      window.removeEventListener('online', handleNetworkChange);
      window.removeEventListener('offline', handleNetworkChange);
      if (silenceTimerRef.current) {
        clearTimeout(silenceTimerRef.current);
      }
    };
  }, [isListening]);

const handleSendMessage = (text) => {
  const token = localStorage.getItem('token');
  const newMessage = {
    id: generateUniqueId(),
    text,
    isUser: true,
    timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
  };
  setMessages((prev) => [...prev, newMessage]);

  // Show loading status
  setStatus('framing...');
  setShowStatus(true);

  fetch('http://localhost:5000/api/auth/conversation', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
    },
    body: JSON.stringify({ message: text }),
  })
    .then((res) => {
      if (!res.ok) throw new Error('Failed to send message');
      return res.json();
    })
    .then((data) => {
      console.log('Server response:', data); // Debug log
      setShowStatus(false);
      
      // Use the messages from the server response
      if (data.messages && Array.isArray(data.messages) && data.messages.length > 0) {
        // Update the entire messages array with server data
        const formattedMessages = data.messages.map((msg, index) => ({
          ...msg,
          id: msg.id || `msg-${index + 1}`,
          timestamp: msg.timestamp || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        }));
        setMessages(formattedMessages);
        messageIdCounter.current = formattedMessages.length + 1;
        console.log('Updated messages:', formattedMessages);
      } else {
        console.error('Invalid response format:', data);
        // Fallback to showing an error message
        const errorResponse = {
          id: generateUniqueId(),
          text: "I apologize, but I encountered an issue processing your request. Please try again.",
          isUser: false,
          timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        };
        setMessages((prev) => [...prev, errorResponse]);
      }
    })
    .catch((error) => {
      console.error('Error sending message:', error);
      setShowStatus(false);
      
      // Show error message to user
      const errorResponse = {
        id: generateUniqueId(),
        text: "I apologize, but I'm having trouble connecting right now. Please check your internet connection and try again.",
        isUser: false,
        timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      };
      setMessages((prev) => [...prev, errorResponse]);
    });
};

  return (
    <ChatContext.Provider value={{ resetMessages }}>
      <div className={`min-h-[100dvh] ${isDarkMode ? 'bg-gray-900' : 'bg-gray-50'} transition-colors duration-300 flex flex-col`}>
        <style>
          {`
            .ripple-container {
              position: relative;
              width: 100px;
              height: 100px;
              display: flex;
              justify-content: center;
              align-items: center;
            }
            .ripple {
              position: absolute;
              border-radius: 50%;
              background: rgba(59, 130, 246, 0.3);
              width: 60px;
              height: 60px;
              animation: ripple-effect 1.5s infinite ease-out;
            }
            .ripple:nth-child(2) { animation-delay: 0.3s; }
            .ripple:nth-child(3) { animation-delay: 0.6s; }
            @keyframes ripple-effect {
              0% { transform: scale(0); opacity: 0.8; }
              100% { transform: scale(2); opacity: 0; }
            }
          `}
        </style>

        <Navbar 
          isDarkMode={isDarkMode}
          toggleDarkMode={() => setIsDarkMode(!isDarkMode)}
          toggleSidebar={() => setSidebarOpen(true)}
        />
        
        <Sidebar 
          isOpen={sidebarOpen}
          onClose={() => setSidebarOpen(false)}
          isDarkMode={isDarkMode}
        />
        
        <main className="flex-1 pt-16 pb-20 overflow-hidden">
          <div className="container mx-auto px-2 sm:px-4 lg:px-6 max-w-3xl flex flex-col min-h-0 h-full">
            <div className="flex justify-center my-4">
              <StatusIndicator status={status} isVisible={showStatus} />
            </div>
            
            <div className="flex-1 space-y-4 overflow-y-auto overflow-x-hidden overscroll-y-contain scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-transparent px-1">
              <div className="w-full max-w-full">
                {messages.map((message) => (
                  <div key={message.id} className="w-full max-w-full mb-4">
                    <MessageBubble
                      message={message.text}
                      isUser={message.isUser}
                      timestamp={message.timestamp}
                    />
                  </div>
                ))}
              </div>
              <div ref={messagesEndRef} />
            </div>
          </div>
        </main>
        
        {isListening ? (
          <div className={`fixed bottom-0 left-0 right-0 h-1/3 ${isDarkMode ? 'bg-gray-800' : 'bg-white'} border-t ${isDarkMode ? 'border-gray-700' : 'border-gray-200'} rounded-t-xl transition-transform duration-300 ease-in-out transform translate-y-0 z-50`}>
            <div className="flex flex-col items-center justify-center h-full px-2 sm:px-4">
              {networkStatus === 'offline' && (
                <div className="flex items-center text-red-500 mb-2">
                  <WifiOff className="w-5 h-5 mr-2" />
                  <p className="text-sm font-medium">No internet connection. Voice input requires an active connection.</p>
                </div>
              )}
              <div className="ripple-container">
                <div className="ripple"></div>
                <div className="ripple"></div>
                <div className="ripple"></div>
                <Mic className={`w-12 h-12 ${isDarkMode ? 'text-blue-400' : 'text-blue-500'} ${listening ? '' : 'opacity-50'}`} />
              </div>
              <p className={`mt-4 text-lg font-medium ${isDarkMode ? 'text-gray-200' : 'text-gray-800'} text-center max-w-full overflow-hidden text-ellipsis`}>
                {transcript || (status === 'listening' ? 'Listening...' : 'Processing...')}
              </p>
              <button
                onClick={handleToggleListening}
                className={`mt-4 p-2 rounded-full border-2 border-red-500 ${isDarkMode ? 'text-red-400 hover:text-white active:text-white' : 'text-red-500 hover:text-white active:text-white'} hover:bg-red-500 active:bg-red-500 transition-colors`}
              >
                <X className="w-6 h-6" />
              </button>
            </div>
          </div>
        ) : (
          <div className="fixed bottom-0 left-0 right-0 bg-inherit px-2 sm:px-4">
            <InputArea
              onSendMessage={handleSendMessage}
              isListening={isListening}
              onToggleListening={handleToggleListening}
              isDarkMode={isDarkMode}
            />
          </div>
        )}
      </div>
    </ChatContext.Provider>
  );
};

export default Home;