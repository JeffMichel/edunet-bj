// Fonctions utilitaires globales pour EduNet BJ

const Utils = {
    // Échapper le HTML pour prévenir les failles XSS
    escapeHTML: (str) => {
        if (!str) return '';
        return String(str).replace(/[&<>'"]/g, 
            tag => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
            }[tag] || tag)
        );
    },

    // Formater une date (ex: 2026-06-11 14:30:00 -> 11 juin 2026 à 14h30)
    formatDate: (dateString, includeTime = true) => {
        if (!dateString) return '';
        // Convertir la date en format ISO valide ou remplacer les tirets par des slashs pour Safari
        const normalized = dateString.replace(/-/g, "/");
        const date = new Date(normalized);
        if (isNaN(date.getTime())) return dateString;
        
        const months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
        const day = date.getDate();
        const month = months[date.getMonth()];
        const year = date.getFullYear();
        
        let result = `${day} ${month} ${year}`;
        if (includeTime) {
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            result += ` à ${hours}h${minutes}`;
        }
        return result;
    },

    // Formater l'heure simple (ex: 08:30:00 -> 08h30)
    formatTime: (timeString) => {
        if (!timeString) return '';
        const parts = timeString.split(':');
        if (parts.length >= 2) {
            return `${parts[0]}h${parts[1]}`;
        }
        return timeString;
    },

    // Afficher une notification (toast) à l'écran
    showToast: (message, type = 'success') => {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm w-full';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        
        let bgClass = 'bg-primary text-white';
        if (type === 'success') bgClass = 'bg-success text-white';
        if (type === 'error') bgClass = 'bg-error text-white';
        if (type === 'warning') bgClass = 'bg-warning text-white';

        toast.className = `${bgClass} px-5 py-4 rounded-xl shadow-lg flex items-center justify-between gap-4 transition-all duration-300 transform translate-y-0 opacity-100`;
        toast.innerHTML = `
            <div class="flex items-center gap-2">
                <span class="font-medium text-sm">${Utils.escapeHTML(message)}</span>
            </div>
            <button class="text-white hover:opacity-75 focus:outline-none font-bold" onclick="this.parentElement.remove()">&times;</button>
        `;
        
        container.appendChild(toast);

        // Animation d'entrée
        toast.style.transform = 'translateY(20px)';
        toast.style.opacity = '0';
        setTimeout(() => {
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
        }, 10);

        // Disparaître après 4 secondes
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    },

    // Traduction du rôle en français
    getRoleLabel: (role) => {
        const labels = {
            'eleve': 'Élève',
            'enseignant': 'Enseignant',
            'censeur': 'Censeur',
            'admin': 'Administrateur'
        };
        return labels[role] || role;
    }
};
// Alias pour la compatibilité (minuscule h)
Utils.escapeHtml = Utils.escapeHTML;

// Formater une date en format relatif (il y a X heures)
Utils.formatRelativeDate = (dateString) => {
    if (!dateString) return '';
    const now = new Date();
    const date = new Date(dateString.replace(/-/g, '/'));
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    if (diffMins < 1) return 'À l\'instant';
    if (diffMins < 60) return `Il y a ${diffMins} min`;
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `Il y a ${diffHours}h`;
    const diffDays = Math.floor(diffHours / 24);
    if (diffDays < 7) return `Il y a ${diffDays}j`;
    return Utils.formatDate(dateString, false);
};
