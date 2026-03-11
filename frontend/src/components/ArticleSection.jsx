import ArticleCard from "./ArticleCard";
import Pagination from "./Pagination";

export default function ArticleSection({ title, articles = [], totalPages = 1 }) {
  return (
    <section>
      {/* Section Header */}
      <h2 className="text-2xl font-bold mb-4">
        {title}{" "}
        <span className="text-gray-500 font-normal ml-2">
          | {articles.length}
        </span>
      </h2>

      {/* Article Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {articles.map((article, index) => (
          <ArticleCard key={index} article={article} />
        ))}
      </div>

      {/* Pagination */}
      <Pagination currentPage={1} totalPages={totalPages} />
    </section>
  );
}
