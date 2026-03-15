import axios from "axios";

const envBaseUrl =
  import.meta.env.VITE_API_BASE_URL || import.meta.env.VITE_API_URL;
const isLocalhost =
  typeof window !== "undefined" &&
  (window.location.hostname === "localhost" ||
    window.location.hostname === "127.0.0.1");
const fallbackBaseUrl = isLocalhost
  ? "http://localhost:8000"
  : "https://mobile-backend-r0ut.onrender.com";
const BASE_URL = (envBaseUrl || fallbackBaseUrl).replace(/\/+$/, "");

axios.defaults.baseURL = BASE_URL;
axios.defaults.withCredentials = true;
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
// Increased timeout for Render free tier cold starts (can take 30-60s)
axios.defaults.timeout = 90000; // 90 seconds

// Add auth token to requests
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("auth_token");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error),
);

// Handle 401 responses globally
axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem("auth_token");
      localStorage.removeItem("user_email");
      window.location.href = "/login";
    }
    return Promise.reject(error);
  },
);

export default axios;
