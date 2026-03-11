import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Calendar, Pencil, Trash2 } from 'lucide-react';
import Header from '../components/Header';
import Navigation from '../components/HeaderLink';
import Footer from '../components/Footer';
import { isAdmin, editArticle, deleteArticle } from '../utils/auth';

const SearchResultCard = ({ imageUrl, title, excerpt, category, date, author, articleId, slug, onClick }) => (
  <div onClick={onClick} className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden flex flex-col md:flex-row mb-6 hover:shadow-md transition-shadow cursor-pointer group">
    <div className="md:w-1/3 relative overflow-hidden h-48 md:h-auto">
      <img 
        src={imageUrl} 
        alt={title} 
        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
      />
      {isAdmin() && (
        <div className="absolute top-2 right-2 flex gap-1">
          <button 
            onClick={(e) => { e.stopPropagation(); editArticle(articleId); }}
            className="p-1.5 bg-blue-500 text-white rounded hover:bg-blue-600 shadow-sm"
          >
            <Pencil size={14} />
          </button>
          <button 
            onClick={(e) => { e.stopPropagation(); deleteArticle(articleId); }}
            className="p-1.5 bg-red-500 text-white rounded hover:bg-red-600 shadow-sm"
          >
            <Trash2 size={14} />
          </button>
        </div>
      )}
    </div>
    <div className="p-6 md:w-2/3 flex flex-col justify-between">
      <div>
        <div className="flex justify-between items-center mb-2">
          <span className="bg-blue-100 text-blue-800 text-xs font-bold px-2 py-0.5 rounded uppercase">
            {category}
          </span>
          <div className="flex items-center text-gray-400 text-xs">
            <Calendar size={12} className="mr-1" />
            {date}
          </div>
        </div>
        <h3 className="text-2xl font-serif font-bold text-gray-900 mb-3 group-hover:text-blue-800 transition-colors">
          {title}
        </h3>
        <p className="text-gray-600 text-sm leading-relaxed mb-4">
          {excerpt}
        </p>
      </div>
      <div className="text-right text-xs text-gray-500 font-medium">
        {author}
      </div>
    </div>
  </div>
);

export default function TagSearchResults() {
  const { tag } = useParams();
  const navigate = useNavigate();
  const [articles, setArticles] = useState([]);
  const [loading, setLoading] = useState(true);

  const hashtags = [
    "#LVCCTeachers", "#MyTeacherMyHero", "#LVCCSocialWork", "#LVCCSWSAP",
    "#NSED2025", "#EarthquakeDrillSeminar", "#BasicJournalismTraining",
    "#NationalPressFreedomDay", "#RamonMagsaysay118", "#CoroDeLaVerdad",
    "#HappyNationalHeroesDay"
  ];

  useEffect(() => {
    // Mock fetch articles by tag
    const fetchArticlesByTag = async () => {
      setLoading(true);
      // Simulate API call
      setTimeout(() => {
        let mockArticles = [];
        
        // Return different articles based on tag
        if (tag?.toLowerCase() === 'cyber' || tag?.toLowerCase() === 'security') {
          mockArticles = [
            {
              id: 1,
              title: "CYBER SECURITY",
              category: "SPORTS",
              date: "November 18, 2025 at 7:56 PM",
              excerpt: "Cybersecurity is the practice of protecting systems, networks, and programs from digital attacks. These cyberattacks are usually aimed at accessing, changing, or destroying sensitive information; extorting money from users through ransomware; or interrupting normal business processes.",
              author: "Admin User",
              imageUrl: 'https://placehold.co/600x400/3b82f6/ffffff?text=Cyber+Security'
            }
          ];
        } else {
          mockArticles = [
            {
              id: 2,
              title: "MDRRMO APALIT HOLDS EARTHQUAKE DRILL SEMINAR AT LVCC",
              category: "NEWS",
              date: "September 10, 2025 at 5:52 PM",
              excerpt: "In preparation for the upcoming Q3 Nationwide Simultaneous Earthquake Drill, the Municipal Disaster Risk Reduction and Management Office...",
              author: "Hannah J. Gallego",
              imageUrl: 'https://placehold.co/600x400/3b82f6/ffffff?text=Earthquake+Drill'
            }
          ];
        }
        
        setArticles(mockArticles);
        setLoading(false);
      }, 800);
    };

    fetchArticlesByTag();
  }, [tag]);

  return (
    <div className="flex flex-col min-h-screen">
      <Header />
      <Navigation />

      <main className="grow">
        {/* Banner */}
        <div className="text-white py-8 px-4 md:px-12 mb-8 shadow-inner" style={{backgroundImage: 'linear-gradient(to right, rgba(59, 130, 246, 0.5), rgba(165, 243, 252, 0.5)), url(/images/bg.jpg)', backgroundSize: 'cover', backgroundPosition: 'center'}}>
          <div className="container mx-auto flex justify-between items-center">
            <h2 className="text-4xl font-bold font-sans">#{tag || 'EarthquakePH'}</h2>
            <span className="font-medium bg-white/20 px-4 py-1 rounded-full text-sm backdrop-blur-sm">
              Articles Found: {articles.length}
            </span>
          </div>
        </div>

        <div className="container mx-auto px-4 md:px-12 flex flex-col lg:flex-row gap-12">
          
          {/* Left Column: Latest Articles */}
          <div className="lg:w-2/3">
            <h3 className="text-2xl font-serif text-gray-800 mb-6 pb-2 border-b border-gray-300 text-left">
              Latest Articles
            </h3>

            {loading ? (
              <div className="flex justify-center items-center h-15 text-gray-500 animate-pulse">
                Loading articles...
              </div>
            ) : articles.length > 0 ? (
              articles.map(article => (
                <SearchResultCard 
                  key={article.id}
                  articleId={article.id}
                  imageUrl={article.imageUrl}
                  title={article.title}
                  category={article.category}
                  date={article.date}
                  excerpt={article.excerpt}
                  author={article.author}
                  slug={article.slug}
                  onClick={() => navigate(`/article/${article.slug || article.id}`)}
                />
              ))
            ) : (
              <div className="text-center text-gray-500 py-8">
                No articles found for this tag.
              </div>
            )}
          </div>

          {/* Right Column: Explore */}
          <div className="lg:w-1/3">
            <h3 className="text-2xl font-serif text-gray-800 mb-6 pb-2 border-b border-gray-300">
              Explore
            </h3>
            <div className="flex flex-wrap gap-2">
              {hashtags.map(hashtag => (
                <button 
                  key={hashtag}
                  className="px-3 py-1 bg-white border border-gray-300 rounded-full text-sm text-gray-600 hover:bg-gray-100 hover:border-gray-400 transition-all"
                  onClick={() => navigate(`/tag/${hashtag.replace('#', '')}`)}
                >
                  {hashtag}
                </button>
              ))}
            </div>
          </div>

        </div>
      </main>

      <Footer />
    </div>
  );
}