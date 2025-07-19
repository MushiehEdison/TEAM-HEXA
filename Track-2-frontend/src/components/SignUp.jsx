import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../App';

const SignUp = () => {
  const [formData, setFormData] = useState({
    name: '',
    username: '',
    email: '',
    password: '',
    phone: '',
    language: '',
    gender: '',
  });
  const [errors, setErrors] = useState({});
  const [showPassword, setShowPassword] = useState(false);
  const navigate = useNavigate();
  const { login } = useAuth();

  const validateField = (name, value) => {
    const newErrors = { ...errors };
    switch (name) {
      case 'name':
        if (!value) newErrors.name = 'Name is required';
        else if (value.length < 2) newErrors.name = 'Name must be at least 2 characters';
        else if (value.length > 120) newErrors.name = 'Name cannot exceed 120 characters';
        else delete newErrors.name;
        break;
      case 'username':
        if (!value) newErrors.username = 'Username is required';
        else if (value.length < 3 || value.length > 20) newErrors.username = 'Username must be 3-20 characters';
        else if (!/^[a-zA-Z0-9_-]+$/.test(value)) newErrors.username = 'Username can only contain letters, numbers, underscores, or hyphens';
        else delete newErrors.username;
        break;
      case 'email':
        if (!value) newErrors.email = 'Email is required';
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) newErrors.email = 'Invalid email format';
        else if (value.length > 120) newErrors.email = 'Email cannot exceed 120 characters';
        else delete newErrors.email;
        break;
      case 'password':
        if (!value) newErrors.password = 'Password is required';
        else if (value.length < 8) newErrors.password = 'Password must be at least 8 characters';
        else delete newErrors.password;
        break;
      case 'phone':
        if (!value) newErrors.phone = 'Phone number is required';
        else if (!/^\+?\d{1,4}?[-.\s]?\d{1,3}[-.\s]?\d{1,4}[-.\s]?\d{1,4}$/.test(value))
          newErrors.phone = 'Invalid phone number format';
        else if (value.length > 15) newErrors.phone = 'Phone number cannot exceed 15 characters';
        else delete newErrors.phone;
        break;
      case 'language':
        if (!value) newErrors.language = 'Language is required';
        else if (value.length > 32) newErrors.language = 'Language cannot exceed 32 characters';
        else delete newErrors.language;
        break;
      case 'gender':
        if (!value) newErrors.gender = 'Gender is required';
        else delete newErrors.gender;
        break;
      default:
        break;
    }
    return newErrors;
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData({ ...formData, [name]: value });
    setErrors(validateField(name, value));
  };

  const toggleShowPassword = () => {
    setShowPassword(!showPassword);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    const newErrors = Object.keys(formData).reduce((acc, key) => ({
      ...acc,
      ...validateField(key, formData[key]),
    }), {});
    setErrors(newErrors);

    if (Object.keys(newErrors).length === 0) {
      try {
        const response = await fetch('/api/auth/signup', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(formData),
        });

        const data = await response.json();
        if (!response.ok) {
          throw new Error(data.message || 'Sign-up failed');
        }

        login(data.token, data.user);
        navigate('/');
      } catch (err) {
        setErrors({ submit: err.message });
      }
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-transparent p-4">
      <div className="bg-transparent border border-gray-300 rounded-2xl p-8 w-full max-w-md transition-all duration-300">
        <h2 className="text-3xl font-semibold text-gray-800 text-center mb-6">Sign Up</h2>
        {errors.submit && (
          <p className="text-red-500 mb-4 text-center animate-fade-in">{errors.submit}</p>
        )}
        <form onSubmit={handleSubmit} className="space-y-5">
          <div>
            <label htmlFor="name" className="block text-sm font-medium text-gray-700">Name</label>
            <input
              id="name"
              type="text"
              name="name"
              value={formData.name}
              onChange={handleChange}
              required
              className="mt-1 block w-full p-3 bg-transparent border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 placeholder-gray-500 transition-all duration-200 hover:border-gray-400 focus:scale-[1.01]"
              placeholder="Enter your name"
              aria-invalid={errors.name ? 'true' : 'false'}
              aria-describedby={errors.name ? 'name-error' : undefined}
            />
            {errors.name && (
              <p id="name-error" className="text-red-500 text-sm mt-1 animate-fade-in">{errors.name}</p>
            )}
          </div>
          <div>
            <label htmlFor="username" className="block text-sm font-medium text-gray-700">Username</label>
            <input
              id="username"
              type="text"
              name="username"
              value={formData.username}
              onChange={handleChange}
              required
              className="mt-1 block w-full p-3 bg-transparent border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 placeholder-gray-500 transition-all duration-200 hover:border-gray-400 focus:scale-[1.01]"
              placeholder="Choose a username"
              aria-invalid={errors.username ? 'true' : 'false'}
              aria-describedby={errors.username ? 'username-error' : undefined}
            />
            {errors.username && (
              <p id="username-error" className="text-red-500 text-sm mt-1 animate-fade-in">{errors.username}</p>
            )}
          </div>
          <div>
            <label htmlFor="email" className="block text-sm font-medium text-gray-700">Email</label>
            <input
              id="email"
              type="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              required
              className="mt-1 block w-full p-3 bg-transparent border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 placeholder-gray-500 transition-all duration-200 hover:border-gray-400 focus:scale-[1.01]"
              placeholder="Enter your email"
              aria-invalid={errors.email ? 'true' : 'false'}
              aria-describedby={errors.email ? 'email-error' : undefined}
            />
            {errors.email && (
              <p id="email-error" className="text-red-500 text-sm mt-1 animate-fade-in">{errors.email}</p>
            )}
          </div>
          <div className="relative">
            <label htmlFor="password" className="block text-sm font-medium text-gray-700">Password</label>
            <input
              id="password"
              type={showPassword ? 'text' : 'password'}
              name="password"
              value={formData.password}
              onChange={handleChange}
              required
              className="mt-1 block w-full p-3 bg-transparent border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 placeholder-gray-500 transition-all duration-200 hover:border-gray-400 focus:scale-[1.01]"
              placeholder="Create a password"
              aria-invalid={errors.password ? 'true' : 'false'}
              aria-describedby={errors.password ? 'password-error' : undefined}
            />
            <button
              type="button"
              onClick={toggleShowPassword}
              className="absolute right-3 top-9 text-gray-500 hover:text-gray-700 focus:outline-none"
              aria-label={showPassword ? 'Hide password' : 'Show password'}
            >
              {showPassword ? 'Hide' : 'Show'}
            </button>
            {errors.password && (
              <p id="password-error" className="text-red-500 text-sm mt-1 animate-fade-in">{errors.password}</p>
            )}
          </div>
          <div>
            <label htmlFor="phone" className="block text-sm font-medium text-gray-700">Phone</label>
            <input
              id="phone"
              type="text"
              name="phone"
              value={formData.phone}
              onChange={handleChange}
              required
              className="mt-1 block w-full p-3 bg-transparent border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 placeholder-gray-500 transition-all duration-200 hover:border-gray-400 focus:scale-[1.01]"
              placeholder="Enter your phone number"
              aria-invalid={errors.phone ? 'true' : 'false'}
              aria-describedby={errors.phone ? 'phone-error' : undefined}
            />
            {errors.phone && (
              <p id="phone-error" className="text-red-500 text-sm mt-1 animate-fade-in">{errors.phone}</p>
            )}
          </div>
          <div>
            <label htmlFor="language" className="block text-sm font-medium text-gray-700">Language</label>
            <input
              id="language"
              type="text"
              name="language"
              value={formData.language}
              onChange={handleChange}
              required
              className="mt-1 block w-full p-3 bg-transparent border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 placeholder-gray-500 transition-all duration-200 hover:border-gray-400 focus:scale-[1.01]"
              placeholder="Preferred language"
              aria-invalid={errors.language ? 'true' : 'false'}
              aria-describedby={errors.language ? 'language-error' : undefined}
            />
            {errors.language && (
              <p id="language-error" className="text-red-500 text-sm mt-1 animate-fade-in">{errors.language}</p>
            )}
          </div>
          <div>
            <label htmlFor="gender" className="block text-sm font-medium text-gray-700">Gender</label>
            <select
              id="gender"
              name="gender"
              value={formData.gender}
              onChange={handleChange}
              required
              className="mt-1 block w-full p-3 bg-transparent border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-gray-700 placeholder-gray-500 transition-all duration-200 hover:border-gray-400 focus:scale-[1.01]"
              aria-invalid={errors.gender ? 'true' : 'false'}
              aria-describedby={errors.gender ? 'gender-error' : undefined}
            >
              <option value="" className="text-gray-500">Select Gender</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Other">Other</option>
            </select>
            {errors.gender && (
              <p id="gender-error" className="text-red-500 text-sm mt-1 animate-fade-in">{errors.gender}</p>
            )}
          </div>
          <button
            type="submit"
            className="w-full bg-transparent border border-gray-300 text-gray-800 p-3 rounded-lg hover:border-gray-400 hover:text-gray-900 transition-all duration-200 disabled:border-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed"
            disabled={Object.keys(errors).length > 0}
          >
            Sign Up
          </button>
        </form>
        <p className="mt-4 text-center text-gray-600 text-sm">
          Already have an account?{' '}
          <a href="/signin" className="text-gray-700 hover:text-gray-900 hover:underline transition-all duration-200">
            Sign In
          </a>
        </p>
      </div>
    </div>
  );
};

export default SignUp;