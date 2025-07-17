import React, { useState, useEffect, useRef } from 'react';
import { Mic, Send, User, Heart, Menu, X, Edit3, Moon, Sun, Phone, Calendar, Activity } from 'lucide-react';

const Navbar = ({ isDarkMode, toggleDarkMode, toggleSidebar }) => {
  return (
    <nav className={`fixed top-0 left-0 right-0 z-50 ${isDarkMode ? 'bg-gray-800' : 'bg-white'} border-b border-gray-200 shadow-sm`}>
      <div className="flex items-center justify-between px-4 py-3">
        <button
          onClick={toggleSidebar}
          className={`p-2 rounded-full ${isDarkMode ? 'hover:bg-gray-700' : 'hover:bg-gray-100'} transition-colors`}
        >
          <Menu className={`w-5 h-5 ${isDarkMode ? 'text-white' : 'text-gray-700'}`} />
        </button>
        
        <div className="flex items-center space-x-2">
          <Heart className="w-6 h-6 text-blue-500" />
          <span className={`text-lg font-semibold ${isDarkMode ? 'text-white' : 'text-gray-800'}`}>
            HealthAI
          </span>
        </div>
        
        <div className="flex items-center space-x-3">
          <button
            onClick={toggleDarkMode}
            className={`p-2 rounded-full ${isDarkMode ? 'hover:bg-gray-700' : 'hover:bg-gray-100'} transition-colors`}
          >
            {isDarkMode ? (
              <Sun className="w-5 h-5 text-yellow-500" />
            ) : (
              <Moon className="w-5 h-5 text-gray-700" />
            )}
          </button>
          <div className="w-8 h-8 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center">
            <User className="w-5 h-5 text-white" />
          </div>
        </div>
      </div>
    </nav>
  );
};

export default Navbar;