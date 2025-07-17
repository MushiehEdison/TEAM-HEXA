import React, { useState, useEffect, useRef } from 'react';
import { Mic, Send, User, Heart, Menu, X, Edit3, Moon, Sun, Phone, Calendar, Activity, WifiOff } from 'lucide-react';
import { Link } from 'react-router-dom';
import SpeechRecognition, { useSpeechRecognition } from 'react-speech-recognition';
import StatusIndicator from './components/homepagecomponents/StatusIndicator';
import MessageBubble from './components/homepagecomponents/MessageBubble';
import Sidebar from './components/homepagecomponents/Sidebar';
import InputArea from './components/homepagecomponents/InputArea';
import Navbar from './components/homepagecomponents/Navbar';

// App.jsx - Main Application Component
const Home = () => {
  const [messages, setMessages] = useState([
    {
      id: 1,
      text: "Hello! I'm your AI healthcare assistant. How can I help you today?",
      isUser: false,
      timestamp: "2:30 PM"
    }
  ]);
  const [isListening, setIsListening] = useState(false);
  const [status, setStatus] = useState('');
  const [showStatus, setShowStatus] = useState(false);
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [isDarkMode, setIsDarkMode] = useState(false);
  const [networkStatus, setNetworkStatus] = useState(navigator.onLine ? 'online' : 'offline');
  const messagesEndRef = useRef(null);
  const silenceTimerRef = useRef(null);

  const { transcript, interimTranscript, finalTranscript, resetTranscript, listening } = useSpeechRecognition();

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

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
        // Restart listening to keep popup open
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
    const newMessage = {
      id: messages.length + 1,
      text,
      isUser: true,
      timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    };
    
    setMessages(prev => [...prev, newMessage]);
    
    // Simulate AI thinking
    setStatus('framing...');
    setShowStatus(true);
    
    setTimeout(() => {
      const aiResponse = {
        id: messages.length + 2,
        text: "I understand your concern. Based on what you've shared, I'd recommend monitoring your symptoms and consulting with your healthcare provider if they persist. Is there anything specific I'd like me to help you track?",
        isUser: false,
        timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
      };
      
      setMessages(prev => [...prev, aiResponse]);
      setShowStatus(false);
    }, 2000);
  };

  const handleToggleListening = () => {
    if (!SpeechRecognition.browserSupportsSpeechRecognition()) {
      alert('Speech recognition is not supported in this browser.');
      return;
    }

    if (!isListening) {
      if (networkStatus === 'offline') {
        alert('No internet connection. Voice input requires an active connection.');
        return;
      }
      setIsListening(true);
      setStatus('listening');
      setShowStatus(true);
      SpeechRecognition.startListening({ continuous: true, interimResults: true, language: 'en-US' });
    } else {
      setIsListening(false);
      setStatus('');
      setShowStatus(false);
      SpeechRecognition.stopListening();
      resetTranscript();
      if (silenceTimerRef.current) {
        clearTimeout(silenceTimerRef.current);
      }
    }
  };

  return (
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
      
      <main className="flex-1 pt-16 pb-20">
        <div className="container mx-auto px-2 sm:px-4 lg:px-6 max-w-3xl flex flex-col min-h-0">
          <div className="flex justify-center my-4">
            <StatusIndicator status={status} isVisible={showStatus} />
          </div>
          
          <div className="flex-1 space-y-4 overflow-y-auto overscroll-y-contain scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-transparent">
            {messages.map((message) => (
              <MessageBubble
                key={message.id}
                message={message.text}
                isUser={message.isUser}
                timestamp={message.timestamp}
              />
            ))}
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
                <p className="text-sm font-medium">No internet connection. Voice input may not work.</p>
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
  );
};

export default Home;