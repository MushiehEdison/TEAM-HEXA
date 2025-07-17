import React, { useState } from 'react';
import { User, X, Edit3, Save } from 'lucide-react';

const EditProfile = () => {
  const [isEditing, setIsEditing] = useState(false);
  const [profile, setProfile] = useState({
    firstName: 'Jean',
    lastName: 'Mbarga',
    email: 'jean.mbarga@example.com',
    phone: '+237 6 12 34 56 78',
    dateOfBirth: '1990-05-15',
    gender: 'Male',
    maritalStatus: 'Married',
    nationality: 'Cameroonian',
    region: 'Littoral',
    city: 'Douala',
    quarter: 'Bonanjo',
    address: '123 Rue de Bonanjo, Douala',
    profession: 'Business Owner',
    emergencyContact: 'Marie Mbarga',
    emergencyRelation: 'Wife',
    emergencyPhone: '+237 6 98 76 54 32',
    bloodType: 'O+',
    genotype: 'AA',
    allergies: 'Penicillin, Shellfish',
    chronicConditions: 'Hypertension',
    medications: 'Lisinopril 10mg daily',
    insuranceProvider: 'CNPS',
    insuranceNumber: 'CN123456789',
    primaryHospital: 'Laquintinie Hospital',
    primaryPhysician: 'Dr. Samuel Enow',
    medicalHistory: 'Hypertension diagnosed in 2015, Malaria episodes (2018, 2020)',
    vaccinationHistory: 'BCG, Hepatitis B, Yellow Fever, COVID-19 (2021)',
    lastDentalVisit: '2023-06-15',
    lastEyeExam: '2023-03-10',
    lifestyle: {
      smokes: false,
      alcohol: 'Occasionally',
      exercise: '3 times weekly',
      diet: 'Balanced'
    },
    familyHistory: 'Father: Hypertension, Mother: Diabetes'
  });

  const [editedProfile, setEditedProfile] = useState({...profile});

  const handleInputChange = (field, value) => {
    setEditedProfile(prev => ({
      ...prev,
      [field]: value
    }));
  };

  const handleNestedInputChange = (parentField, field, value) => {
    setEditedProfile(prev => ({
      ...prev,
      [parentField]: {
        ...prev[parentField],
        [field]: value
      }
    }));
  };

  const handleSave = () => {
    setProfile(editedProfile);
    setIsEditing(false);
  };

  const handleCancel = () => {
    setEditedProfile({...profile});
    setIsEditing(false);
  };

  // Cameroon-specific regions
  const cameroonRegions = [
    'Adamawa', 'Centre', 'East', 'Far North', 'Littoral', 
    'North', 'Northwest', 'South', 'Southwest', 'West'
  ];

  return (
    <div className="w-full px-4 sm:px-6 lg:px-8 py-6">
      {/* Header with Edit/Save/Cancel buttons */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h2 className="text-2xl font-bold text-gray-800">Profile Details</h2>
        <div className="flex flex-col xs:flex-row gap-2 w-full sm:w-auto">
          {!isEditing ? (
            <button
              onClick={() => setIsEditing(true)}
              className="flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors outline outline-2 outline-blue-600 w-full sm:w-auto"
            >
              <Edit3 className="h-4 w-4" />
              Edit Profile
            </button>
          ) : (
            <div className="flex flex-col xs:flex-row gap-2 w-full">
              <button
                onClick={handleSave}
                className="flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors outline outline-2 outline-green-600 flex-1"
              >
                <Save className="h-4 w-4" />
                Save
              </button>
              <button
                onClick={handleCancel}
                className="flex items-center justify-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors outline outline-2 outline-gray-600 flex-1"
              >
                <X className="h-4 w-4" />
                Cancel
              </button>
            </div>
          )}
        </div>
      </div>

      {/* Main content */}
      <div className="space-y-6">
        {/* Personal Information Section */}
        <div className="p-4 outline outline-1 outline-gray-300 rounded-lg">
          <h3 className="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2 outline outline-1 outline-gray-300 p-2 rounded">
            <User className="h-5 w-5" />
            Personal Information
          </h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {/* First Name */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">First Name</label>
              {isEditing ? (
                <input
                  type="text"
                  value={editedProfile.firstName}
                  onChange={(e) => handleInputChange('firstName', e.target.value)}
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.firstName}</p>
              )}
            </div>

            {/* Last Name */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
              {isEditing ? (
                <input
                  type="text"
                  value={editedProfile.lastName}
                  onChange={(e) => handleInputChange('lastName', e.target.value)}
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.lastName}</p>
              )}
            </div>

            {/* Email */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
              {isEditing ? (
                <input
                  type="email"
                  value={editedProfile.email}
                  onChange={(e) => handleInputChange('email', e.target.value)}
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.email}</p>
              )}
            </div>

            {/* Phone */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Phone (Cameroon format)</label>
              {isEditing ? (
                <input
                  type="tel"
                  value={editedProfile.phone}
                  onChange={(e) => handleInputChange('phone', e.target.value)}
                  placeholder="+237 6 XX XX XX XX"
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.phone}</p>
              )}
            </div>

            {/* Date of Birth */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
              {isEditing ? (
                <input
                  type="date"
                  value={editedProfile.dateOfBirth}
                  onChange={(e) => handleInputChange('dateOfBirth', e.target.value)}
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.dateOfBirth}</p>
              )}
            </div>

            {/* Gender */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Gender</label>
              {isEditing ? (
                <select
                  value={editedProfile.gender}
                  onChange={(e) => handleInputChange('gender', e.target.value)}
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                >
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                  <option value="Other">Other</option>
                </select>
              ) : (
                <p className="text-gray-900 py-2">{profile.gender}</p>
              )}
            </div>

            {/* Marital Status */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Marital Status</label>
              {isEditing ? (
                <select
                  value={editedProfile.maritalStatus}
                  onChange={(e) => handleInputChange('maritalStatus', e.target.value)}
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                >
                  <option value="Single">Single</option>
                  <option value="Married">Married</option>
                  <option value="Divorced">Divorced</option>
                  <option value="Widowed">Widowed</option>
                </select>
              ) : (
                <p className="text-gray-900 py-2">{profile.maritalStatus}</p>
              )}
            </div>

            {/* Nationality */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Nationality</label>
              {isEditing ? (
                <input
                  type="text"
                  value={editedProfile.nationality}
                  onChange={(e) => handleInputChange('nationality', e.target.value)}
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.nationality}</p>
              )}
            </div>
          </div>
          
          {/* Address Section */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            {/* Region */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Region</label>
              {isEditing ? (
                <select
                  value={editedProfile.region}
                  onChange={(e) => handleInputChange('region', e.target.value)}
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                >
                  {cameroonRegions.map(region => (
                    <option key={region} value={region}>{region}</option>
                  ))}
                </select>
              ) : (
                <p className="text-gray-900 py-2">{profile.region}</p>
              )}
            </div>

            {/* City */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">City</label>
              {isEditing ? (
                <input
                  type="text"
                  value={editedProfile.city}
                  onChange={(e) => handleInputChange('city', e.target.value)}
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.city}</p>
              )}
            </div>

            {/* Quarter/Neighborhood */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Quarter/Neighborhood</label>
              {isEditing ? (
                <input
                  type="text"
                  value={editedProfile.quarter}
                  onChange={(e) => handleInputChange('quarter', e.target.value)}
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.quarter}</p>
              )}
            </div>

            {/* Profession */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Profession</label>
              {isEditing ? (
                <input
                  type="text"
                  value={editedProfile.profession}
                  onChange={(e) => handleInputChange('profession', e.target.value)}
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.profession}</p>
              )}
            </div>
          </div>
          
          {/* Full Address */}
          <div className="mt-4 outline outline-1 outline-gray-200 p-3 rounded">
            <label className="block text-sm font-medium text-gray-700 mb-1">Full Address</label>
            {isEditing ? (
              <textarea
                value={editedProfile.address}
                onChange={(e) => handleInputChange('address', e.target.value)}
                rows="2"
                className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
              />
            ) : (
              <p className="text-gray-900 py-2">{profile.address}</p>
            )}
          </div>
        </div>

        {/* Emergency Contact Section */}
        <div className="p-4 outline outline-1 outline-gray-300 rounded-lg">
          <h3 className="text-lg font-semibold text-gray-800 mb-4 outline outline-1 outline-gray-300 p-2 rounded">Emergency Contact</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {/* Contact Name */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Contact Name</label>
              {isEditing ? (
                <input
                  type="text"
                  value={editedProfile.emergencyContact}
                  onChange={(e) => handleInputChange('emergencyContact', e.target.value)}
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.emergencyContact}</p>
              )}
            </div>

            {/* Relationship */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Relationship</label>
              {isEditing ? (
                <input
                  type="text"
                  value={editedProfile.emergencyRelation}
                  onChange={(e) => handleInputChange('emergencyRelation', e.target.value)}
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.emergencyRelation}</p>
              )}
            </div>

            {/* Contact Phone */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
              {isEditing ? (
                <input
                  type="tel"
                  value={editedProfile.emergencyPhone}
                  onChange={(e) => handleInputChange('emergencyPhone', e.target.value)}
                  placeholder="+237 6 XX XX XX XX"
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.emergencyPhone}</p>
              )}
            </div>
          </div>
        </div>

        {/* Medical Information Section */}
        <div className="p-4 outline outline-1 outline-gray-300 rounded-lg">
          <h3 className="text-lg font-semibold text-gray-800 mb-4 outline outline-1 outline-gray-300 p-2 rounded">Medical Information</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {/* Blood Type */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Blood Type</label>
              {isEditing ? (
                <select
                  value={editedProfile.bloodType}
                  onChange={(e) => handleInputChange('bloodType', e.target.value)}
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                >
                  <option value="A+">A+</option>
                  <option value="A-">A-</option>
                  <option value="B+">B+</option>
                  <option value="B-">B-</option>
                  <option value="AB+">AB+</option>
                  <option value="AB-">AB-</option>
                  <option value="O+">O+</option>
                  <option value="O-">O-</option>
                </select>
              ) : (
                <p className="text-gray-900 py-2">{profile.bloodType}</p>
              )}
            </div>

            {/* Genotype */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Genotype</label>
              {isEditing ? (
                <select
                  value={editedProfile.genotype}
                  onChange={(e) => handleInputChange('genotype', e.target.value)}
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                >
                  <option value="AA">AA</option>
                  <option value="AS">AS</option>
                  <option value="SS">SS</option>
                  <option value="AC">AC</option>
                  <option value="SC">SC</option>
                </select>
              ) : (
                <p className="text-gray-900 py-2">{profile.genotype}</p>
              )}
            </div>

            {/* Primary Hospital */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Primary Hospital</label>
              {isEditing ? (
                <input
                  type="text"
                  value={editedProfile.primaryHospital}
                  onChange={(e) => handleInputChange('primaryHospital', e.target.value)}
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.primaryHospital}</p>
              )}
            </div>

            {/* Primary Physician */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Primary Physician</label>
              {isEditing ? (
                <input
                  type="text"
                  value={editedProfile.primaryPhysician}
                  onChange={(e) => handleInputChange('primaryPhysician', e.target.value)}
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.primaryPhysician}</p>
              )}
            </div>
          </div>
          
          {/* Medical Details */}
          <div className="grid grid-cols-1 gap-4 mt-4">
            {/* Allergies */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Allergies</label>
              {isEditing ? (
                <textarea
                  value={editedProfile.allergies}
                  onChange={(e) => handleInputChange('allergies', e.target.value)}
                  rows="2"
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.allergies}</p>
              )}
            </div>

            {/* Chronic Conditions */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Chronic Conditions</label>
              {isEditing ? (
                <textarea
                  value={editedProfile.chronicConditions}
                  onChange={(e) => handleInputChange('chronicConditions', e.target.value)}
                  rows="2"
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.chronicConditions}</p>
              )}
            </div>

            {/* Current Medications */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Current Medications</label>
              {isEditing ? (
                <textarea
                  value={editedProfile.medications}
                  onChange={(e) => handleInputChange('medications', e.target.value)}
                  rows="2"
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.medications}</p>
              )}
            </div>

            {/* Medical History */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Medical History</label>
              {isEditing ? (
                <textarea
                  value={editedProfile.medicalHistory}
                  onChange={(e) => handleInputChange('medicalHistory', e.target.value)}
                  rows="3"
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.medicalHistory}</p>
              )}
            </div>

            {/* Vaccination History */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Vaccination History</label>
              {isEditing ? (
                <textarea
                  value={editedProfile.vaccinationHistory}
                  onChange={(e) => handleInputChange('vaccinationHistory', e.target.value)}
                  rows="2"
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.vaccinationHistory}</p>
              )}
            </div>

            {/* Last Dental/Eye Visit */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="outline outline-1 outline-gray-200 p-3 rounded">
                <label className="block text-sm font-medium text-gray-700 mb-1">Last Dental Visit</label>
                {isEditing ? (
                  <input
                    type="date"
                    value={editedProfile.lastDentalVisit}
                    onChange={(e) => handleInputChange('lastDentalVisit', e.target.value)}
                    className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                  />
                ) : (
                  <p className="text-gray-900 py-2">{profile.lastDentalVisit}</p>
                )}
              </div>
              <div className="outline outline-1 outline-gray-200 p-3 rounded">
                <label className="block text-sm font-medium text-gray-700 mb-1">Last Eye Exam</label>
                {isEditing ? (
                  <input
                    type="date"
                    value={editedProfile.lastEyeExam}
                    onChange={(e) => handleInputChange('lastEyeExam', e.target.value)}
                    className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                  />
                ) : (
                  <p className="text-gray-900 py-2">{profile.lastEyeExam}</p>
                )}
              </div>
            </div>

            {/* Family Medical History */}
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Family Medical History</label>
              {isEditing ? (
                <textarea
                  value={editedProfile.familyHistory}
                  onChange={(e) => handleInputChange('familyHistory', e.target.value)}
                  rows="2"
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.familyHistory}</p>
              )}
            </div>
          </div>
          
          {/* Lifestyle Information */}
          <div className="mt-4">
            <h4 className="text-md font-medium text-gray-800 mb-2 outline outline-1 outline-gray-300 p-2 rounded">Lifestyle Information</h4>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="outline outline-1 outline-gray-200 p-3 rounded">
                <label className="block text-sm font-medium text-gray-700 mb-1">Smokes</label>
                {isEditing ? (
                  <select
                    value={editedProfile.lifestyle.smokes}
                    onChange={(e) => handleNestedInputChange('lifestyle', 'smokes', e.target.value === 'true')}
                    className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                  >
                    <option value="false">No</option>
                    <option value="true">Yes</option>
                  </select>
                ) : (
                  <p className="text-gray-900 py-2">{profile.lifestyle.smokes ? 'Yes' : 'No'}</p>
                )}
              </div>
              <div className="outline outline-1 outline-gray-200 p-3 rounded">
                <label className="block text-sm font-medium text-gray-700 mb-1">Alcohol Consumption</label>
                {isEditing ? (
                  <select
                    value={editedProfile.lifestyle.alcohol}
                    onChange={(e) => handleNestedInputChange('lifestyle', 'alcohol', e.target.value)}
                    className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                  >
                    <option value="Never">Never</option>
                    <option value="Occasionally">Occasionally</option>
                    <option value="Regularly">Regularly</option>
                  </select>
                ) : (
                <p className="text-gray-900 py-2">{profile.lifestyle.alcohol}</p>
                )}
              </div>
              <div className="outline outline-1 outline-gray-200 p-3 rounded">
                <label className="block text-sm font-medium text-gray-700 mb-1">Exercise Frequency</label>
                {isEditing ? (
                  <select
                    value={editedProfile.lifestyle.exercise}
                    onChange={(e) => handleNestedInputChange('lifestyle', 'exercise', e.target.value)}
                    className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                  >
                    <option value="Never">Never</option>
                    <option value="Rarely">Rarely</option>
                    <option value="1-2 times weekly">1-2 times weekly</option>
                    <option value="3 times weekly">3 times weekly</option>
                    <option value="4+ times weekly">4+ times weekly</option>
                  </select>
                ) : (
                  <p className="text-gray-900 py-2">{profile.lifestyle.exercise}</p>
                )}
              </div>
              <div className="outline outline-1 outline-gray-200 p-3 rounded">
                <label className="block text-sm font-medium text-gray-700 mb-1">Diet</label>
                {isEditing ? (
                  <select
                    value={editedProfile.lifestyle.diet}
                    onChange={(e) => handleNestedInputChange('lifestyle', 'diet', e.target.value)}
                    className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                  >
                    <option value="Balanced">Balanced</option>
                    <option value="Vegetarian">Vegetarian</option>
                    <option value="Vegan">Vegan</option>
                    <option value="High-protein">High-protein</option>
                    <option value="Low-carb">Low-carb</option>
                  </select>
                ) : (
                  <p className="text-gray-900 py-2">{profile.lifestyle.diet}</p>
                )}
              </div>
            </div>
          </div>
        </div>

        {/* Insurance Information Section */}
        <div className="p-4 outline outline-1 outline-gray-300 rounded-lg">
          <h3 className="text-lg font-semibold text-gray-800 mb-4 outline outline-1 outline-gray-300 p-2 rounded">Insurance Information</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Insurance Provider</label>
              {isEditing ? (
                <input
                  type="text"
                  value={editedProfile.insuranceProvider}
                  onChange={(e) => handleInputChange('insuranceProvider', e.target.value)}
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.insuranceProvider}</p>
              )}
            </div>
            <div className="outline outline-1 outline-gray-200 p-3 rounded">
              <label className="block text-sm font-medium text-gray-700 mb-1">Insurance Number</label>
              {isEditing ? (
                <input
                  type="text"
                  value={editedProfile.insuranceNumber}
                  onChange={(e) => handleInputChange('insuranceNumber', e.target.value)}
                  className="w-full px-3 py-2 outline outline-1 outline-gray-300 rounded-lg focus:outline-2 focus:outline-blue-500"
                />
              ) : (
                <p className="text-gray-900 py-2">{profile.insuranceNumber}</p>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default EditProfile;