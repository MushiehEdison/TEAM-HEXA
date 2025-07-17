import React, { useState } from "react";
import { Calendar, Clock, MapPin, Search, Filter, X, Eye } from 'lucide-react';

const AllVisits = () => {
  const [visits, setVisits] = useState([
    {
      id: 1,
      patientName: 'John Doe',
      date: '2024-01-15',
      time: '10:30 AM',
      location: 'Room 101',
      purpose: 'Routine Checkup',
      status: 'Completed',
      doctor: 'Dr. Smith',
      notes: 'Patient in good health'
    },
    {
      id: 2,
      patientName: 'Jane Smith',
      date: '2024-01-14',
      time: '2:15 PM',
      location: 'Room 205',
      purpose: 'Follow-up',
      status: 'Completed',
      doctor: 'Dr. Johnson',
      notes: 'Medication adjustment needed'
    },
    {
      id: 3,
      patientName: 'Mike Wilson',
      date: '2024-01-13',
      time: '11:45 AM',
      location: 'Room 103',
      purpose: 'Emergency',
      status: 'Completed',
      doctor: 'Dr. Brown',
      notes: 'Treated for minor injury'
    }
  ]);

  const [searchTerm, setSearchTerm] = useState('');
  const [filterStatus, setFilterStatus] = useState('All');
  const [selectedVisit, setSelectedVisit] = useState(null);

  const filteredVisits = visits.filter(visit => {
    const matchesSearch = visit.patientName.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         visit.doctor.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesFilter = filterStatus === 'All' || visit.status === filterStatus;
    return matchesSearch && matchesFilter;
  });

  const getStatusColor = (status) => {
    switch(status) {
      case 'Completed': return 'bg-green-100 text-green-800 outline-green-200';
      case 'Scheduled': return 'bg-blue-100 text-blue-800 outline-blue-200';
      case 'Cancelled': return 'bg-red-100 text-red-800 outline-red-200';
      default: return 'bg-gray-100 text-gray-800 outline-gray-200';
    }
  };

  return (
    <div className="max-w-6xl mx-auto sm:px-6 lg:px-8 sm:py-6">
      {/* Header */}
      <div className="sm:mb-6 pt-4 sm:pt-0">
        <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 px-4 sm:px-0">All Visits</h2>
        <div className="mt-4 sm:mt-6 flex flex-col sm:flex-row gap-4 px-4 sm:px-0">
          <div className="relative flex-1">
            <Search className="absolute left-7 sm:left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
            <input
              type="text"
              placeholder="Search by patient name or doctor..."
              className="w-full pl-14 sm:pl-10 pr-4 py-3 border border-gray-300 outline outline-1 outline-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              aria-label="Search visits"
            />
          </div>
          <div className="relative">
            <Filter className="absolute left-7 sm:left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
            <select
              className="pl-14 sm:pl-10 pr-8 py-3 border border-gray-300 outline outline-1 outline-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors"
              value={filterStatus}
              onChange={(e) => setFilterStatus(e.target.value)}
              aria-label="Filter by status"
            >
              <option value="All">All Status</option>
              <option value="Completed">Completed</option>
              <option value="Scheduled">Scheduled</option>
              <option value="Cancelled">Cancelled</option>
            </select>
          </div>
        </div>
      </div>

      {/* Visits List */}
      <div className="grid gap-4 sm:gap-6 px-4 sm:px-0">
        {filteredVisits.map((visit) => (
          <div
            key={visit.id}
            className="border border-gray-200 outline outline-1 outline-gray-200 rounded-lg py-4 sm:py-6 px-4 sm:px-6 hover:bg-gray-50 transition-colors"
          >
            <div className="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-3">
              <div className="flex-1">
                <h3 className="font-semibold text-base sm:text-lg text-gray-900">{visit.patientName}</h3>
                <p className="text-gray-600 text-sm sm:text-base">{visit.purpose}</p>
              </div>
              <div className="flex items-center gap-2">
                <span className={`px-2 py-1 rounded-full text-xs font-medium outline outline-1 ${getStatusColor(visit.status)}`}>
                  {visit.status}
                </span>
                <button
                  onClick={() => setSelectedVisit(visit)}
                  className="p-2 text-gray-500 hover:text-blue-600 rounded-full outline outline-1 outline-gray-200 transition-colors"
                  aria-label={`View details for visit by ${visit.patientName}`}
                >
                  <Eye className="h-5 w-5" />
                </button>
              </div>
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm text-gray-600">
              <div className="flex items-center gap-2">
                <Calendar className="h-4 w-4" aria-hidden="true" />
                <span>{visit.date}</span>
              </div>
              <div className="flex items-center gap-2">
                <Clock className="h-4 w-4" aria-hidden="true" />
                <span>{visit.time}</span>
              </div>
              <div className="flex items-center gap-2">
                <MapPin className="h-4 w-4" aria-hidden="true" />
                <span>{visit.location}</span>
              </div>
            </div>
            <div className="mt-3 text-sm text-gray-600">
              <strong>Doctor:</strong> {visit.doctor}
            </div>
          </div>
        ))}
      </div>

      {/* Visit Details Modal */}
      {selectedVisit && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-start sm:items-center justify-center z-50">
          <div className="bg-white w-full sm:max-w-2xl rounded-b-lg sm:rounded-lg max-h-[100vh] sm:max-h-[80vh] overflow-y-auto">
            <div className="flex justify-between items-center p-4 sm:p-6 border-b border-gray-200 outline outline-1 outline-gray-200">
              <h3 className="text-lg sm:text-xl font-bold text-gray-900">Visit Details</h3>
              <button
                onClick={() => setSelectedVisit(null)}
                className="p-2 text-gray-500 hover:text-gray-700 rounded-full outline outline-1 outline-gray-200"
                aria-label="Close visit details"
              >
                <X className="h-5 w-5" />
              </button>
            </div>
            <div className="p-4 sm:p-6">
              <div className="grid gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Patient Name</label>
                  <p className="text-gray-900 text-sm sm:text-base">{selectedVisit.patientName}</p>
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <p className="text-gray-900 text-sm sm:text-base">{selectedVisit.date}</p>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Time</label>
                    <p className="text-gray-900 text-sm sm:text-base">{selectedVisit.time}</p>
                  </div>
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <p className="text-gray-900 text-sm sm:text-base">{selectedVisit.location}</p>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Doctor</label>
                    <p className="text-gray-900 text-sm sm:text-base">{selectedVisit.doctor}</p>
                  </div>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Purpose</label>
                  <p className="text-gray-900 text-sm sm:text-base">{selectedVisit.purpose}</p>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                  <span className={`inline-block px-2 py-1 rounded-full text-xs font-medium outline outline-1 ${getStatusColor(selectedVisit.status)}`}>
                    {selectedVisit.status}
                  </span>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                  <p className="text-gray-900 text-sm sm:text-base">{selectedVisit.notes}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default AllVisits;