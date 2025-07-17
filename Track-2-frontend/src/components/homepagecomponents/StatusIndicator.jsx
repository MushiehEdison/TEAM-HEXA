import React, { useState, useEffect, useRef } from 'react';
import { Mic, Send, User, Heart, Menu, X, Edit3, Moon, Sun, Phone, Calendar, Activity } from 'lucide-react';

const StatusIndicator = ({ status, isVisible }) => {
  const statusConfig = {
    listening: { text: 'Listening...', color: 'text-blue-500', bg: 'bg-blue-50' },
    thinking: { text: 'Framing...', color: 'text-purple-500', bg: 'bg-purple-50' },
    speaking: { text: 'Speaking...', color: 'text-green-500', bg: 'bg-green-50' }
  };

  const config = statusConfig[status] || statusConfig.thinking;

  return (
    <div className={`transition-all duration-300 ${isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-2'}`}>
      <div className={`inline-flex items-center px-4 py-2 rounded-full ${config.bg} ${config.color} text-sm font-medium`}>
        <div className="w-2 h-2 rounded-full bg-current animate-pulse mr-2"></div>
        {config.text}
      </div>
    </div>
  );
};

export default StatusIndicator;