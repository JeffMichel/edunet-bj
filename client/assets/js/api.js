// Wrapper Fetch API réutilisable pour EduNet BJ

// Déterminer l'URL de base pour l'API de manière dynamique (supporte le protocole file:// et les sous-dossiers sous Wamp)
let API_BASE_URL;
if (window.location.protocol === 'file:' || window.location.port === '3000') {
    // Si ouvert en double-cliquant sur le fichier HTML (file://) ou en serveur de dev sur le port 3000
    API_BASE_URL = 'http://localhost:8000';
} else {
    const pathParts = window.location.pathname.split('/');
    const clientIndex = pathParts.indexOf('client');
    if (clientIndex !== -1) {
        // Si on est dans un sous-dossier (ex: http://localhost/EDUNET/client/)
        const basePath = pathParts.slice(0, clientIndex).join('/');
        API_BASE_URL = window.location.origin + basePath + '/api';
    } else {
        API_BASE_URL = window.location.origin + '/api';
    }
}

const API = {
    getToken: () => localStorage.getItem('edunet_token'),
    getRefreshToken: () => localStorage.getItem('edunet_refresh_token'),
    getUser: () => {
        const user = localStorage.getItem('edunet_user');
        return user ? JSON.parse(user) : null;
    },
    
    setSession: (token, refreshToken, user = null) => {
        if (token) localStorage.setItem('edunet_token', token);
        if (refreshToken) localStorage.setItem('edunet_refresh_token', refreshToken);
        if (user) localStorage.setItem('edunet_user', JSON.stringify(user));
    },
    
    clearSession: () => {
        localStorage.removeItem('edunet_token');
        localStorage.removeItem('edunet_refresh_token');
        localStorage.removeItem('edunet_user');
    },

    request: async (endpoint, options = {}) => {
        const url = API_BASE_URL + endpoint;
        options.headers = options.headers || {};
        
        // Gérer le Content-Type automatiquement sauf si FormData (qui définit ses propres frontières)
        if (!(options.body instanceof FormData)) {
            options.headers['Content-Type'] = options.headers['Content-Type'] || 'application/json';
        }
        
        const token = API.getToken();
        if (token) {
            options.headers['Authorization'] = `Bearer ${token}`;
        }

        try {
            let response = await fetch(url, options);
            
            // Gérer le rafraîchissement automatique sur erreur 401
            if (response.status === 401 && API.getRefreshToken() && endpoint !== '/auth/refresh') {
                console.warn("Session expirée. Tentative de rafraîchissement...");
                const refreshed = await API.refreshToken();
                if (refreshed) {
                    options.headers['Authorization'] = `Bearer ${API.getToken()}`;
                    response = await fetch(url, options);
                } else {
                    API.clearSession();
                    // Rediriger vers la page de login en préservant le chemin relatif correct
                    const currentPath = window.location.pathname;
                    if (!currentPath.includes('login.html')) {
                        // Chercher le chemin relatif vers login.html selon la profondeur
                        let prefix = './';
                        if (currentPath.includes('/pages/eleve/') || currentPath.includes('/pages/enseignant/') || currentPath.includes('/pages/censeur/') || currentPath.includes('/pages/admin/')) {
                            prefix = '../../';
                        } else if (currentPath.includes('/pages/')) {
                            prefix = '../';
                        }
                        window.location.href = prefix + 'pages/login.html';
                    }
                    return { success: false, message: "Session expirée. Veuillez vous reconnecter." };
                }
            }

            const result = await response.json();
            return result;
        } catch (error) {
            console.error("Erreur API:", error);
            return { success: false, message: "Impossible de contacter le serveur d'API." };
        }
    },

    refreshToken: async () => {
        const refreshToken = API.getRefreshToken();
        if (!refreshToken) return false;

        try {
            const response = await fetch(API_BASE_URL + '/auth/refresh', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ refresh_token: refreshToken })
            });
            const result = await response.json();
            if (result.success) {
                API.setSession(result.data.token, result.data.refresh_token);
                return true;
            }
        } catch (e) {
            console.error("Erreur lors du rafraîchissement du token:", e);
        }
        return false;
    },

    get: (endpoint, options = {}) => {
        return API.request(endpoint, { ...options, method: 'GET' });
    },

    post: (body, endpoint, options = {}) => {
        // Attention : body en premier argument, ou standardiser.
        // Le README ou les conventions suggèrent (endpoint, body)
        // Standardisons avec (endpoint, body, options) pour correspondre au design du plan d'implémentation
        const isFormData = body instanceof FormData;
        return API.request(endpoint, {
            ...options,
            method: 'POST',
            body: isFormData ? body : JSON.stringify(body)
        });
    },

    put: (endpoint, body, options = {}) => {
        const isFormData = body instanceof FormData;
        return API.request(endpoint, {
            ...options,
            method: 'PUT',
            body: isFormData ? body : JSON.stringify(body)
        });
    },

    delete: (endpoint, options = {}) => {
        return API.request(endpoint, { ...options, method: 'DELETE' });
    }
};

// Pour rétrocompatibilité ou si l'ordre des arguments est inversé
API.postData = (endpoint, body, options = {}) => {
    const isFormData = body instanceof FormData;
    return API.request(endpoint, {
        ...options,
        method: 'POST',
        body: isFormData ? body : JSON.stringify(body)
    });
};
