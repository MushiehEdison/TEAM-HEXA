import React, { useState, useRef } from 'react';
import { Mic, Send } from 'lucide-react';

const InputArea = ({ onSendMessage, isListening, onToggleListening, isDarkMode }) => {
  const [message, setMessage] = useState('');
  const textareaRef = useRef(null);

  const handleSend = () => {
    if (message.trim()) {
      onSendMessage(message);
      setMessage('');
      // Reset height
      if (textareaRef.current) {
        textareaRef.current.style.height = 'auto';
      }
    }
  };

  const handleKeyDown = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      handleSend();
    }
  };

  const handleInputChange = (e) => {
    setMessage(e.target.value);
    
    // Auto-resize
    const textarea = textareaRef.current;
    if (textarea) {
      textarea.style.height = 'auto';
      textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    }
  };

  return (
    <>
      <style>
        {`
          .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
          }
          .scrollbar-hide::-webkit-scrollbar {
            display: none;
          }
        `}
      </style>
      <div className={`fixed bottom-0 left-0 right-0 ${isDarkMode ? 'bg-gray-800' : 'bg-white'} border-t border-gray-200 p-4`}>
        <div className="max-w-4xl mx-auto">
          <div className="flex items-center space-x-3">
            <div className="flex-1 relative">
              <textarea
                ref={textareaRef}
                value={message}
                onChange={handleInputChange}
                onKeyDown={handleKeyDown}
                placeholder="Enter diagnoses..."
                rows={1}
                className={`w-full px-4 py-3 rounded border resize-none max-h-32 scrollbar-hide ${
                  isDarkMode 
                    ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' 
                    : 'bg-gray-50 border-gray-200 text-gray-800 placeholder-gray-500'
                } focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent`}
                style={{ minHeight: '48px' }}
              />
              {message.trim() && (
                <button
                  onClick={handleSend}
                  className="absolute right-2 top-1/2 transform -translate-y-1/2 bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-full transition-colors"
                >
                  <Send className="w-4 h-4" />
                </button>
              )}
            </div>
            
            <button
              onClick={onToggleListening}
              className={`p-4 rounded-full transition-all duration-200 ${
                isListening
                  ? 'bg-red-500 hover:bg-red-600 scale-110'
                  : 'bg-blue-500 hover:bg-blue-600'
              } text-white shadow-lg`}
            >
              <Mic className={`w-6 h-6 ${isListening ? 'animate-pulse' : ''}`} />
            </button>
          </div>
        </div>
      </div>
    </>
  );
};

export default InputArea;