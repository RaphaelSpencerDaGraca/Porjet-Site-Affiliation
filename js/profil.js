/**
 * JavaScript pour la page de profil utilisateur
 */

// Fonction pour copier le texte dans le presse-papier
function copyToClipboard(text) {
    // Créer un élément temporaire textarea
    const textarea = document.createElement('textarea');
    textarea.value = text;

    // Le rendre invisible
    textarea.style.position = 'fixed';
    textarea.style.opacity = 0;

    // L'ajouter au DOM
    document.body.appendChild(textarea);

    // Sélectionner tout le texte
    textarea.select();
    textarea.setSelectionRange(0, 99999); // Pour les appareils mobiles

    // Exécuter la commande de copie
    document.execCommand('copy');

    // Supprimer l'élément temporaire
    document.body.removeChild(textarea);

    // Afficher une notification
    showNotification('Lien copié dans le presse-papier !');
}

// Fonction pour afficher une notification
function showNotification(message, type = 'success') {
    // Créer l'élément de notification
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;

    // Styles de base
    notification.style.position = 'fixed';
    notification.style.bottom = '20px';
    notification.style.right = '20px';
    notification.style.padding = '12px 24px';
    notification.style.borderRadius = '8px';
    notification.style.fontFamily = "'Poppins', sans-serif";
    notification.style.zIndex = '1000';
    notification.style.display = 'flex';
    notification.style.alignItems = 'center';
    notification.style.gap = '10px';
    notification.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
    notification.style.transition = 'all 0.3s ease';
    notification.style.opacity = '0';
    notification.style.transform = 'translateY(20px)';

    // Type-specific styles
    if (type === 'success') {
        notification.style.backgroundColor = '#e6f4ea';
        notification.style.color = '#1e8e3e';
        notification.style.borderLeft = '4px solid #34a853';
        notification.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> ${message}`;
    } else {
        notification.style.backgroundColor = '#fdecea';
        notification.style.color = '#d93025';
        notification.style.borderLeft = '4px solid #ea4335';
        notification.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg> ${message}`;
    }

    // Ajouter au DOM
    document.body.appendChild(notification);

    // Animation d'entrée
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateY(0)';
    }, 10);

    // Disparaît après 3 secondes
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(20px)';

        // Supprimer du DOM après la transition
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Initialisation quand le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des onglets du formulaire
    const showFormBtn = document.getElementById('show-affiliate-form-btn');
    const formContainer = document.getElementById('affiliate-form-container');
    const cancelLinkBtn = document.getElementById('cancel-link-form');
    const cancelCodeBtn = document.getElementById('cancel-code-form');
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');

    // Afficher/masquer le formulaire
    if (showFormBtn && formContainer) {
        showFormBtn.addEventListener('click', function() {
            formContainer.style.display = 'block';
            showFormBtn.style.display = 'none';
        });

        function hideForm() {
            formContainer.style.display = 'none';
            showFormBtn.style.display = 'inline-block';
        }

        if (cancelLinkBtn) cancelLinkBtn.addEventListener('click', hideForm);
        if (cancelCodeBtn) cancelCodeBtn.addEventListener('click', hideForm);
    }

    // Gestion des onglets
    if (tabBtns.length > 0 && tabPanes.length > 0) {
        tabBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                // Retirer la classe active de tous les boutons
                tabBtns.forEach(function(b) {
                    b.classList.remove('active');
                });

                // Ajouter la classe active au bouton cliqué
                this.classList.add('active');

                // Afficher le contenu de l'onglet correspondant
                const tabId = this.getAttribute('data-tab');

                tabPanes.forEach(function(pane) {
                    pane.classList.remove('active');
                });

                document.getElementById(tabId).classList.add('active');
            });
        });
    }

    // Vérification de la correspondance des mots de passe
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    if (newPasswordInput && confirmPasswordInput) {
        function checkPasswordMatch() {
            if (confirmPasswordInput.value === '') return;

            if (newPasswordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity('Les mots de passe ne correspondent pas');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        }

        newPasswordInput.addEventListener('input', checkPasswordMatch);
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    }

    // Animation d'entrée pour les cartes de liens
    const affiliateCards = document.querySelectorAll('.affiliate-card');
    if (affiliateCards.length > 0) {
        affiliateCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.3s ease';
            card.style.transitionDelay = `${index * 0.1}s`;

            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
    }
});

