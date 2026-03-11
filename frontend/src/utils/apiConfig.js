// API Configuration
const envBaseUrl = import.meta.env.VITE_API_BASE_URL || import.meta.env.VITE_API_URL;
const isLocalhost =
  typeof window !== 'undefined' &&
  (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1');
const fallbackBaseUrl = isLocalhost
  ? 'http://localhost:8000'
  : 'https://official-laverdad-herald.onrender.com';

export const API_BASE_URL = (envBaseUrl || fallbackBaseUrl).replace(/\/+$/, '');

// Helper to build full URLs for non-axios requests
export const getApiUrl = (path) => {
  const cleanPath = path.startsWith('/') ? path : `/${path}`;
  return `${API_BASE_URL}${cleanPath}`;
};

// Helper for storage URLs - ensures HTTPS in production
export const getStorageUrl = (path) => {
  if (!path) return null;
  if (path.startsWith('http')) {
    // Force HTTPS in production
    if (window.location.protocol === 'https:' && path.startsWith('http://')) {
      return path.replace('http://', 'https://');
    }
    return path;
  }
  const cleanPath = path.startsWith('/') ? path : `/${path}`;
  const url = `${API_BASE_URL}${cleanPath}`;
  
  // Force HTTPS in production
  if (window.location.protocol === 'https:' && url.startsWith('http://')) {
    return url.replace('http://', 'https://');
  }
  
  return url;
};
