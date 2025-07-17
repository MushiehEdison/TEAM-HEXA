import React, { useState, useEffect, useRef } from 'react';
import { Mic, Send, User, Heart, Menu, X, Edit3, Moon, Sun, Phone, Calendar, Activity } from 'lucide-react';
import { Link } from "react-router-dom";

const Sidebar = ({ isOpen, onClose, isDarkMode }) => {
  const patientInfo = {
    name: 'Sarah Johnson',
    age: 29,
    language: 'English',
    condition: 'Diabetes Type 2',
    lastVisit: '2 days ago'
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
                {patientInfo.name}
              </h3>
              <p className={`text-sm ${isDarkMode ? 'text-gray-300' : 'text-gray-600'}`}>
                Age {patientInfo.age}
              </p>
            </div>
          </div>
          
          <div className="space-y-4">
            <div className={`p-3 rounded-lg ${isDarkMode ? 'bg-gray-700' : 'bg-gray-50'}`}>
              <Link to="/history" className="flex items-center space-x-2 mb-1">
                <Activity className="w-4 h-4 text-blue-500" />
                <span className={`text-sm font-medium ${isDarkMode ? 'text-white' : 'text-gray-800'}`}>
                  Current Condition
                </span>
              </Link>
              <p className={`text-sm ${isDarkMode ? 'text-gray-300' : 'text-gray-600'}`}>
                {patientInfo.condition}
              </p>
            </div>
            
            <div className={`p-3 rounded-lg ${isDarkMode ? 'bg-gray-700' : 'bg-gray-50'}`}>
              <Link to="/visits" className="flex items-center space-x-2 mb-1">
                <Calendar className="w-4 h-4 text-green-500" />
                <span className={`text-sm font-medium ${isDarkMode ? 'text-white' : 'text-gray-800'}`}>
                  Last Visit
                </span>
              </Link>
              <p className={`text-sm ${isDarkMode ? 'text-gray-300' : 'text-gray-600'}`}>
                {patientInfo.lastVisit}
              </p>
            </div>
            
            <div className={`p-3 rounded-lg ${isDarkMode ? 'bg-gray-700' : 'bg-gray-50'}`}>
              <div className="flex items-center space-x-2 mb-1">
                <Phone className="w-4 h-4 text-purple-500" />
                <span className={`text-sm font-medium ${isDarkMode ? 'text-white' : 'text-gray-800'}`}>
                  Language
                </span>
              </div>
              <p className={`text-sm ${isDarkMode ? 'text-gray-300' : 'text-gray-600'}`}>
                {patientInfo.language}
              </p>
            </div>
          </div>
          
          <div className="space-y-3">
            <button 
              className="w-full bg-blue-500 hover:bg-blue-600 text-white py-3 px-4 rounded-lg flex items-center justify-center space-x-2 transition-colors"
              onClick={() => {
                // Add your new chat functionality here
                console.log("Starting new chat...");
              }}
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
          </div>
        </div>
      </div>
    </>
  );
};

export default Sidebar;