/**
 * Image Helper Utilities
 * Handles image URLs for both static assets and backend storage
 */

import { API_BASE_URL } from './apiConfig';

/**
 * Get URL for static assets in public/images folder
 * @param {string} filename - Image filename (e.g., 'logo.svg', 'bg.jpg')
 * @returns {string} - Full path to image
 */
export const getAssetUrl = (filename) => {
  if (!filename) return null;
  return `/images/${filename}`;
};

/**
 * Get URL for images stored in backend storage
 * @param {string} path - Image path from backend (e.g., '/storage/articles/image.jpg')
 * @returns {string} - Full URL to image
 */
export const getStorageUrl = (path) => {
  if (!path) return 'https://placehold.co/400x250/e2e8f0/64748b?text=No+Image';
  
  // If it's already a full URL
  if (path.startsWith('http://') || path.startsWith('https://')) {
    // Replace localhost with production URL
    return path.replace('http://localhost:8000', API_BASE_URL);
  }
  
  // If it's a relative path, prepend API base URL
  return `${API_BASE_URL}${path}`;
};

/**
 * Get placeholder image URL
 * @param {string} text - Text to display in placeholder
 * @param {number} width - Image width
 * @param {number} height - Image height
 * @returns {string} - Placeholder image URL
 */
export const getPlaceholderUrl = (text = 'No Image', width = 400, height = 250) => {
  return `https://placehold.co/${width}x${height}/e2e8f0/64748b?text=${encodeURIComponent(text)}`;
};

/**
 * Get avatar URL (for user profiles)
 * @param {string} avatarUrl - Avatar URL from backend
 * @param {string} name - User name for fallback
 * @returns {string} - Avatar URL or generated avatar
 */
export const getAvatarUrl = (avatarUrl, name = 'User') => {
  if (avatarUrl) {
    return getStorageUrl(avatarUrl);
  }
  // Generate avatar using UI Avatars service
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=0D47A1&color=fff&size=128`;
};

export default {
  getAssetUrl,
  getStorageUrl,
  getPlaceholderUrl,
  getAvatarUrl,
};
