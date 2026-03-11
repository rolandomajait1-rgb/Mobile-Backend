import axios from './axiosConfig';

// Auth utility functions
export const isAdmin = () => {
    const token = localStorage.getItem('auth_token');
    const userRole = localStorage.getItem('user_role');
    return token && userRole === 'admin';
};

export const isModerator = () => {
    const token = localStorage.getItem('auth_token');
    const userRole = localStorage.getItem('user_role');
    return token && userRole === 'moderator';
};

export const getAuthToken = () => {
    return localStorage.getItem('auth_token');
};

export const getUserRole = () => {
    return localStorage.getItem('user_role');
};

// Article management functions
export const editArticle = (articleId) => {
    window.location.href = `/admin/edit-article/${articleId}`;
};

export const deleteArticle = async(articleId) => {
    if (!window.confirm('Are you sure you want to delete this article?')) {
        return false;
    }

    try {
        const response = await axios.delete(`/api/articles/${articleId}`);

        if (response.status >= 200 && response.status < 300) {
            alert('Article deleted successfully');
            window.location.reload();
            return true;
        } else {
            alert('Failed to delete article');
            return false;
        }
    } catch (error) {
        console.error('Error deleting article:', error);
        alert('Error deleting article');
        return false;
    }
};
