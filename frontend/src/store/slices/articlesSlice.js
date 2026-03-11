import { createSlice } from '@reduxjs/toolkit';

const articlesSlice = createSlice({
  name: 'articles',
  initialState: {
    articles: [],
    loading: false,
    error: null,
  },
  reducers: {
    setArticles: (state, action) => {
      // Add safety check for payload
      if (action.payload !== undefined && action.payload !== null) {
        state.articles = Array.isArray(action.payload) ? action.payload : [];
      } else {
        console.warn('setArticles called with invalid payload:', action.payload);
        state.articles = [];
      }
      state.loading = false;
    },
    setLoading: (state, action) => {
      state.loading = action.payload !== undefined ? Boolean(action.payload) : false;
    },
    setError: (state, action) => {
      state.error = action.payload || null;
      state.loading = false;
    },
  },
});

export const { setArticles, setLoading, setError } = articlesSlice.actions;
export default articlesSlice.reducer;
