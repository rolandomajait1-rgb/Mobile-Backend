import UserProfileCard from "../components/UserProfileCard";
import ArticleSection from "../components/ArticleSection";

// Dummy data for demonstration
const sharedArticlesData = [
  { /* article 1 data */ },
  { /* article 2 data */ },
  { /* article 3 data */ },
];

const likedArticlesData = [
  { /* article 1 data */ },
  { /* article 2 data */ },
  { /* article 3 data */ },
  { /* article 4 data */ },
  { /* article 5 data */ },
  { /* article 6 data */ },
];

export default function ProfilePage() {
  return (
    <div className="min-h-screen bg-gray-100 p-8">
      {/* You would import your main <Header /> component here */}
      
      <main className="container mx-auto">
        <div className="flex flex-col lg:flex-row gap-8">
          
          {/* Left Column */}
          <aside className="lg:w-1/3 w-full">
            <UserProfileCard />
          </aside>

          {/* Right Column */}
          <div className="lg:w-2/3 w-full flex flex-col gap-12">
            <ArticleSection
              title="Shared Articles"
              articles={sharedArticlesData}
              totalPages={3}
            />
            <ArticleSection
              title="Liked Articles"
              articles={likedArticlesData}
              totalPages={2}
            />
          </div>
        </div>
      </main>
      
      {/* You would import your main <Footer /> component here */}
    </div>
  );
}
