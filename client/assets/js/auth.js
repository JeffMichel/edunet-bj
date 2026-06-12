// Gestionnaire d'authentification et de routage côté client

const Auth = {
    // Vérifier si l'utilisateur est connecté et possède le rôle requis
    checkSession: (allowedRoles = []) => {
        const token = API.getToken();
        const user = API.getUser();
        const currentPath = window.location.pathname;

        if (!token || !user) {
            Auth.redirectToLogin();
            return null;
        }

        // Si c'est la première connexion, forcer la redirection vers la page de changement de mot de passe
        if (user.premier_connexion === 1 && !currentPath.includes('change-password.html')) {
            let prefix = './';
            if (currentPath.includes('/pages/eleve/') || currentPath.includes('/pages/enseignant/') || currentPath.includes('/pages/censeur/') || currentPath.includes('/pages/admin/')) {
                prefix = '../../';
            } else if (currentPath.includes('/pages/')) {
                prefix = '../';
            }
            window.location.href = prefix + 'pages/change-password.html';
            return null;
        }

        if (allowedRoles.length > 0 && !allowedRoles.includes(user.role)) {
            console.warn("Accès interdit pour le rôle :", user.role);
            Auth.redirectToDashboard(user.role);
            return null;
        }

        return user;
    },

    // Rediriger vers la page de connexion
    redirectToLogin: () => {
        const currentPath = window.location.pathname;
        let prefix = './';
        if (currentPath.includes('/pages/eleve/') || currentPath.includes('/pages/enseignant/') || currentPath.includes('/pages/censeur/') || currentPath.includes('/pages/admin/')) {
            prefix = '../../';
        } else if (currentPath.includes('/pages/')) {
            prefix = '../';
        }
        window.location.href = prefix + 'pages/login.html';
    },

    // Rediriger vers le tableau de bord approprié selon le rôle
    redirectToDashboard: (role) => {
        const currentPath = window.location.pathname;
        let prefix = './';
        if (currentPath.includes('/pages/eleve/') || currentPath.includes('/pages/enseignant/') || currentPath.includes('/pages/censeur/') || currentPath.includes('/pages/admin/')) {
            prefix = '../../';
        } else if (currentPath.includes('/pages/')) {
            prefix = '../';
        }

        switch (role) {
            case 'eleve':
                window.location.href = prefix + 'pages/eleve/dashboard.html';
                break;
            case 'enseignant':
                window.location.href = prefix + 'pages/enseignant/dashboard.html';
                break;
            case 'censeur':
                window.location.href = prefix + 'pages/censeur/dashboard.html';
                break;
            case 'admin':
                window.location.href = prefix + 'pages/admin/dashboard.html';
                break;
            default:
                Auth.redirectToLogin();
        }
    },

    // Déconnexion de l'utilisateur
    logout: async () => {
        try {
            const refreshToken = API.getRefreshToken();
            if (refreshToken) {
                await API.postData('/auth/logout', { refresh_token: refreshToken });
            }
        } catch (e) {
            console.error("Erreur déconnexion API:", e);
        }
        API.clearSession();
        Auth.redirectToLogin();
    }
};
