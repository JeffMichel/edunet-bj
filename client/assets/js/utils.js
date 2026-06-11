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

// Initialisation automatique de la responsivité mobile sur tous les écrans
const initMobileResponsiveness = () => {
    console.log("✨ EduNet BJ : Initialisation de la responsivité mobile (v1.3)...");

    const aside = document.querySelector('aside');
    const header = document.querySelector('main > header');

    if (!aside) return;

    // 1. Vérification et injection dynamique du bouton hamburger si manquant dans le header
    if (header) {
        let mobileMenuBtn = header.querySelector('#mobileMenuBtn');
        if (!mobileMenuBtn) {
            const container = header.querySelector('.flex.items-center.gap-4') || header.firstElementChild;
            if (container) {
                mobileMenuBtn = document.createElement('button');
                mobileMenuBtn.id = 'mobileMenuBtn';
                mobileMenuBtn.className = 'md:hidden text-neutralMuted hover:text-neutralText mr-2 shrink-0 p-1.5 rounded-lg hover:bg-neutralBg transition-colors';
                mobileMenuBtn.innerHTML = '<i data-lucide="menu" class="w-6 h-6"></i>';
                container.insertBefore(mobileMenuBtn, container.firstChild);
                if (window.lucide) {
                    try { window.lucide.createIcons(); } catch(e) {}
                }
            }
        }
    }

    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    if (!mobileMenuBtn) return;

    // 2. Création et configuration de l'overlay (backdrop) en JS inline
    let backdrop = document.querySelector('.mobile-backdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.className = 'mobile-backdrop';
        backdrop.style.position = 'fixed';
        backdrop.style.inset = '0';
        backdrop.style.backgroundColor = 'rgba(15, 28, 63, 0.4)';
        backdrop.style.backdropFilter = 'blur(4px)';
        backdrop.style.webkitBackdropFilter = 'blur(4px)';
        backdrop.style.zIndex = '9998';
        backdrop.style.opacity = '0';
        backdrop.style.pointerEvents = 'none';
        backdrop.style.transition = 'opacity 0.3s ease-in-out';
        document.body.appendChild(backdrop);
    }

    // 3. Application des styles JS inline sur l'aside pour le mode mobile
    const applyMobileStyles = () => {
        if (window.innerWidth < 768) {
            aside.style.setProperty('position', 'fixed', 'important');
            aside.style.setProperty('top', '0', 'important');
            aside.style.setProperty('bottom', '0', 'important');
            aside.style.setProperty('left', '0', 'important');
            aside.style.setProperty('z-index', '9999', 'important');
            aside.style.setProperty('width', '260px', 'important');
            aside.style.setProperty('display', 'flex', 'important');
            aside.style.setProperty('flex-direction', 'column', 'important');
            aside.style.setProperty('transition', 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)', 'important');
            
            if (!aside.classList.contains('open')) {
                aside.style.setProperty('transform', 'translateX(-100%)', 'important');
            } else {
                aside.style.setProperty('transform', 'translateX(0)', 'important');
            }
        } else {
            // Nettoyage complet des styles mobiles sur écran large
            aside.style.removeProperty('position');
            aside.style.removeProperty('top');
            aside.style.removeProperty('bottom');
            aside.style.removeProperty('left');
            aside.style.removeProperty('z-index');
            aside.style.removeProperty('width');
            aside.style.removeProperty('display');
            aside.style.removeProperty('flex-direction');
            aside.style.removeProperty('transition');
            aside.style.removeProperty('transform');
            aside.classList.remove('open');
            backdrop.style.opacity = '0';
            backdrop.style.pointerEvents = 'none';
            document.body.style.overflow = '';
        }
    };

    // Appliquer au démarrage et lors du redimensionnement
    applyMobileStyles();
    window.addEventListener('resize', applyMobileStyles);

    const openMenu = () => {
        aside.classList.add('open');
        aside.style.setProperty('transform', 'translateX(0)', 'important');
        aside.style.setProperty('box-shadow', '10px 0 30px rgba(15, 28, 63, 0.25)', 'important');
        backdrop.style.opacity = '1';
        backdrop.style.pointerEvents = 'auto';
        document.body.style.overflow = 'hidden';
    };

    const closeMenu = () => {
        aside.classList.remove('open');
        aside.style.setProperty('transform', 'translateX(-100%)', 'important');
        aside.style.removeProperty('box-shadow');
        backdrop.style.opacity = '0';
        backdrop.style.pointerEvents = 'none';
        document.body.style.overflow = '';
    };

    // Remplacer le bouton par un clone propre pour éliminer les anciens event listeners
    const cleanBtn = mobileMenuBtn.cloneNode(true);
    mobileMenuBtn.parentNode.replaceChild(cleanBtn, mobileMenuBtn);

    cleanBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        e.preventDefault();
        if (aside.classList.contains('open')) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    backdrop.addEventListener('click', closeMenu);

    // Fermer le menu lors du clic sur un lien interne de la barre
    aside.querySelectorAll('nav a').forEach(link => {
        link.addEventListener('click', closeMenu);
    });

    // 4. Correction de la responsivité des tableaux (éviter les débordements horizontaux)
    document.querySelectorAll('table').forEach(table => {
        if (!table.parentElement.classList.contains('overflow-x-auto')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'overflow-x-auto w-full';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMobileResponsiveness);
} else {
    initMobileResponsiveness();
}
