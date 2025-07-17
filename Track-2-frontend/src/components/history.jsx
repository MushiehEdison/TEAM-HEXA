import React, { useState } from "react";
import { Calendar, Clock, MessageCircle, Search, X } from 'lucide-react';

const History = () => {
  const [conversations, setConversations] = useState([
    {
      id: 1,
      date: '2024-01-15',
      time: '10:30 AM',
      messages: [
        { sender: 'user', text: 'Hello, I need help with my appointment', time: '10:30 AM' },
        { sender: 'app', text: 'Hi! I\'d be happy to help you with your appointment. What do you need assistance with?', time: '10:31 AM' },
        { sender: 'user', text: 'I want to reschedule my appointment for tomorrow', time: '10:32 AM' },
        { sender: 'app', text: 'I can help you reschedule. Let me check available slots for you.', time: '10:32 AM' }
      ]
    },
    {
      id: 2,
      date: '2024-01-14',
      time: '2:15 PM',
      messages: [
        { sender: 'user', text: 'Can you remind me about my medication schedule?', time: '2:15 PM' },
        { sender: 'app', text: 'Of course! Your current medication schedule is: Morning - 8 AM, Afternoon - 2 PM, Evening - 8 PM', time: '2:16 PM' },
        { sender: 'user', text: 'Thank you, that\'s very helpful', time: '2:17 PM' }
      ]
    }
  ]);

  const [selectedConversation, setSelectedConversation] = useState(null);
  const [searchTerm, setSearchTerm] = useState('');

  const filteredConversations = conversations.filter(conversation =>
    conversation.messages.some(message => 
      message.text.toLowerCase().includes(searchTerm.toLowerCase())
    )
  );

  return (
    <div className="max-w-6xl mx-auto sm:px-6 lg:px-8 sm:py-6">
      {/* Header */}
      <div className="sm:mb-6 pt-4 sm:pt-0">
        <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 px-4 sm:px-0">Conversation History</h2>
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

      {/* Conversations List */}
      <div className="grid gap-4 sm:gap-6 px-4 sm:px-0">
        {filteredConversations.map((conversation) => (
          <div
            key={conversation.id}
            className="border border-gray-200 outline outline-1 outline-gray-200 rounded-lg py-4 sm:py-6 px-4 sm:px-6 hover:bg-gray-50 transition-colors"
          >
            <div className="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
              <div className="flex-1">
                <div className="flex items-center gap-2 mb-3">
                  <MessageCircle className="h-5 w-5 text-blue-600" aria-hidden="true" />
                  <span className="font-medium text-gray-900">Conversation {conversation.id}</span>
                </div>
                <p className="text-gray-600 text-sm line-clamp-2">
                  {conversation.messages[0].text}
                </p>
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
              <span className="text-sm text-gray-500">
                {conversation.messages.length} messages
              </span>
              <button
                onClick={() => setSelectedConversation(conversation)}
                className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 outline outline-1 outline-blue-200 transition-colors text-sm"
                aria-label={`View details for conversation ${conversation.id}`}
              >
                View Details
              </button>
            </div>
          </div>
        ))}
      </div>

      {/* Conversation Details Modal */}
      {selectedConversation && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-start sm:items-center justify-center z-50">
          <div className="bg-white w-full sm:max-w-4xl rounded-b-lg sm:rounded-lg max-h-[100vh] sm:max-h-[80vh] overflow-y-auto">
            <div className="flex justify-between items-center p-4 sm:p-6 border-b border-gray-200 outline outline-1 outline-gray-200">
              <h3 className="text-lg sm:text-xl font-bold text-gray-900">
                Conversation Details - {selectedConversation.date}
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
                {selectedConversation.messages.map((message, index) => (
                  <div
                    key={index}
                    className={`flex ${message.sender === 'user' ? 'justify-end' : 'justify-start'}`}
                  >
                    <div
                      className={`max-w-[80%] sm:max-w-xs lg:max-w-md px-4 py-3 rounded-lg outline outline-1 ${
                        message.sender === 'user'
                          ? 'bg-blue-600 text-white outline-blue-200'
                          : 'bg-gray-100 text-gray-800 outline-gray-200'
                      }`}
                    >
                      <p className="text-sm">{message.text}</p>
                      <p
                        className={`text-xs mt-1 ${
                          message.sender === 'user' ? 'text-blue-200' : 'text-gray-500'
                        }`}
                      >
                        {message.time}
                      </p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default History;