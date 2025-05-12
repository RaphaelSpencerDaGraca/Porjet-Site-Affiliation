/**
 * JavaScript pour les pages d'authentification
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialisation du formulaire d'inscription
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        initPasswordStrength();
        initPasswordMatch();
    }
});

/**
 * Initialise l'indicateur de force du mot de passe
 */
// Script pour l'indicateur de force du mot de passe
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const strengthMeter = document.getElementById('password-strength-meter');
    const strengthText = document.getElementById('strength-text');
    const confirmPasswordInput = document.getElementById('confirm_password');

    if(passwordInput && strengthMeter && strengthText) {
        passwordInput.addEventListener('input', function() {
            const password = passwordInput.value;
            let strength = 0;

            // Calculer la force
            if(password.length >= 8) strength += 25;
            if(password.match(/[a-z]+/)) strength += 15;
            if(password.match(/[A-Z]+/)) strength += 20;
            if(password.match(/[0-9]+/)) strength += 20;
            if(password.match(/[^a-zA-Z0-9]+/)) strength += 20;

            // Mettre à jour la barre de progression
            strengthMeter.style.width = strength + '%';

            // Mise à jour du texte et de la couleur
            if(strength < 30) {
                strengthMeter.style.backgroundColor = '#f44336'; // Rouge
                strengthText.textContent = 'Faible';
            } else if(strength < 60) {
                strengthMeter.style.backgroundColor = '#ff9800'; // Orange
                strengthText.textContent = 'Moyen';
            } else if(strength < 80) {
                strengthMeter.style.backgroundColor = '#4CAF50'; // Vert
                strengthText.textContent = 'Bon';
            } else {
                strengthMeter.style.backgroundColor = '#2E7D32'; // Vert foncé
                strengthText.textContent = 'Excellent';
            }
        });
    }

    // Vérification de correspondance des mots de passe
    if(passwordInput && confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            if(passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity('Les mots de passe ne correspondent pas');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        });

        passwordInput.addEventListener('input', function() {
            if(confirmPasswordInput.value && passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity('Les mots de passe ne correspondent pas');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        });
    }
});

/**
 * Calcule la force d'un mot de passe
 * @param {string} password - Le mot de passe à évaluer
 * @return {number} - Score entre 0 et 100
 */
function calculatePasswordStrength(password) {
    if (!password) return 0;

    let score = 0;

    // Longueur (40 points max)
    score += Math.min(password.length * 4, 40);

    // Complexité (60 points max)
    const hasLowercase = /[a-z]/.test(password);
    const hasUppercase = /[A-Z]/.test(password);
    const hasDigits = /\d/.test(password);
    const hasSpecialChars = /[^a-zA-Z0-9]/.test(password);

    if (hasLowercase) score += 10;
    if (hasUppercase) score += 15;
    if (hasDigits) score += 15;
    if (hasSpecialChars) score += 20;

    return score;
}

/**
 * Initialise la vérification de correspondance des mots de passe
 */
function initPasswordMatch() {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    if (!passwordInput || !confirmPasswordInput) return;

    function checkPasswordMatch() {
        if (confirmPasswordInput.value === '') return;

        if (passwordInput.value !== confirmPasswordInput.value) {
            confirmPasswordInput.setCustomValidity('Les mots de passe ne correspondent pas');
        } else {
            confirmPasswordInput.setCustomValidity('');
        }
    }

    passwordInput.addEventListener('input', checkPasswordMatch);
    confirmPasswordInput.addEventListener('input', checkPasswordMatch);
}

// Script pour gérer le formulaire d'ajout de lien/code
document.addEventListener('DOMContentLoaded', function() {
    // Éléments du DOM
    const showFormBtn = document.getElementById('show-affiliate-form-btn');
    const formContainer = document.getElementById('affiliate-form-container');
    const cancelLinkBtn = document.getElementById('cancel-link-form');
    const cancelCodeBtn = document.getElementById('cancel-code-form');
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');

    // Afficher le formulaire
    showFormBtn.addEventListener('click', function() {
        formContainer.style.display = 'block';
        showFormBtn.style.display = 'none';
    });

    // Masquer le formulaire
    function hideForm() {
        formContainer.style.display = 'none';
        showFormBtn.style.display = 'inline-block';
    }

    cancelLinkBtn.addEventListener('click', hideForm);
    cancelCodeBtn.addEventListener('click', hideForm);

    // Gestion des onglets
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
});