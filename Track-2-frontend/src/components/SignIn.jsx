import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../App';

const SignIn = () => {
  const [formData, setFormData] = useState({
    email: '',
    password: '',
  });
  const [errors, setErrors] = useState({});
  const [showPassword, setShowPassword] = useState(false); // State for show/hide password
  const navigate = useNavigate();
  const { login } = useAuth();

  const validateField = (name, value) => {
    const newErrors = { ...errors };
    switch (name) {
      case 'email':
        if (!value) newErrors.email = 'Email is required';
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) newErrors.email = 'Invalid email format';
        else if (value.length > 120) newErrors.email = 'Email cannot exceed 120 characters';
        else delete newErrors.email;
        break;
      case 'password':
        if (!value) newErrors.password = 'Password is required';
        else delete newErrors.password;
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

  const handleSubmit = async (e) => {
    e.preventDefault();
    const newErrors = Object.keys(formData).reduce((acc, key) => ({
      ...acc,
      ...validateField(key, formData[key]),
    }), {});
    setErrors(newErrors);

    if (Object.keys(newErrors).length === 0) {
      try {
        const response = await fetch('http://localhost:5000/api/auth/signin', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(formData),
        });

        const data = await response.json();
        if (!response.ok) {
          throw new Error(data.message || 'Sign-in failed');
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
        <h2 className="text-3xl font-semibold text-gray-800 text-center mb-6">Sign In</h2>
        {errors.submit && (
          <p className="text-red-500 mb-4 text-center animate-fade-in">{errors.submit}</p>
        )}
        <form onSubmit={handleSubmit} className="space-y-5">
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
              placeholder="Enter your password"
              aria-invalid={errors.password ? 'true' : 'false'}
              aria-describedby={errors.password ? 'password-error' : undefined}
            />
            <button
              type="button"
              onClick={() => setShowPassword(!showPassword)}
              className="absolute right-3 top-9 text-gray-500 hover:text-gray-700"
            >
              {showPassword ? 'Hide' : 'Show'}
            </button>
            {errors.password && (
              <p id="password-error" className="text-red-500 text-sm mt-1 animate-fade-in">{errors.password}</p>
            )}
          </div>
          <button
            type="submit"
            className="w-full bg-transparent border border-gray-300 text-gray-800 p-3 rounded-lg hover:border-gray-400 hover:text-gray-900 transition-all duration-200 disabled:border-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed"
            disabled={Object.keys(errors).length > 0}
          >
            Sign In
          </button>
        </form>
        <p className="mt-4 text-center text-gray-600 text-sm">
          Don't have an account?{' '}
          <a href="/signup" className="text-gray-700 hover:text-gray-900 hover:underline transition-all duration-200">
            Sign Up
          </a>
        </p>
      </div>
    </div>
  );
};

export default SignIn;