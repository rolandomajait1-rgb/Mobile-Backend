import React, { useState, useEffect } from 'react';
import { useSearchParams, Link, useNavigate } from 'react-router-dom';
import { articles } from '../data/articles';

const SearchResults = () => {
  const [searchParams] = useSearchParams();
  const [results, setResults] = useState([]);
  const [authorResults, setAuthorResults] = useState([]);
  const navigate = useNavigate();
  const query = searchParams.get('q') || '';

  useEffect(() => {
    if (query) {
      // Search articles
      const filtered = articles.filter(article => 
        article.isPublished && (
          article.title.toLowerCase().includes(query.toLowerCase()) ||
          article.description.toLowerCase().includes(query.toLowerCase()) ||
          article.category.toLowerCase().includes(query.toLowerCase())
        )
      );
      setResults(filtered);
      
      // Search authors
      const authors = [...new Set(articles.map(article => article.author))]
        .filter(author => author.toLowerCase().includes(query.toLowerCase()))
        .map(author => ({
          name: author
        }));
      setAuthorResults(authors);
    }
  }, [query]);

  return (
    <div className="max-w-4xl mx-auto p-6">
      <h1 className="text-2xl font-bold mb-6">Search Results for "{query}"</h1>
      
      {/* Authors Section */}
      {authorResults.length > 0 && (
        <div className="mb-8">
          <h2 className="text-xl font-semibold mb-4">Authors ({authorResults.length})</h2>
          <div className="space-y-3">
            {authorResults.map((author, index) => (
              <div 
                key={index} 
                onClick={() => navigate(`/author/${encodeURIComponent(author.name)}`)}
                className="border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer bg-blue-50 hover:bg-blue-100"
              >
                <div className="flex justify-between items-center">
                  <h3 className="text-lg font-medium text-blue-800">{author.name}</h3>
                  {/* article counts hidden for all users */}
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
      
      {/* Articles Section */}
      {results.length > 0 && (
        <div>
          <h2 className="text-xl font-semibold mb-4">Articles ({results.length})</h2>
          <div className="space-y-6">
            {results.map(article => (
              <Link key={article.id} to={`/article/${article.id}`} className="block">
                <div className="border rounded-lg p-4 hover:shadow-md transition-shadow">
                  <div className="flex gap-2 mb-2">
                    <span className="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">New</span>
                    <span className="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">{article.category}</span>
                  </div>
                  <h2 className="text-xl font-semibold mb-2">{article.title}</h2>
                  <p className="text-gray-600 mb-2">{article.description}</p>
                  <div className="text-sm text-gray-500">{article.date} • {article.author}</div>
                </div>
              </Link>
            ))}
          </div>
        </div>
      )}
      
      {results.length === 0 && authorResults.length === 0 && query && (
        <p className="text-gray-600">No articles or authors found matching your search.</p>
      )}
    </div>
  );
};

export default SearchResults;