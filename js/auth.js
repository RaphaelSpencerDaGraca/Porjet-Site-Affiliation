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
function initPasswordStrength() {
    const passwordInput = document.getElementById('password');
    const strengthLevel = document.getElementById('strength-level');
    const strengthText = document.getElementById('strength-text');

    if (!passwordInput || !strengthLevel || !strengthText) return;

    passwordInput.addEventListener('input', function() {
        const password = passwordInput.value;
        const score = calculatePasswordStrength(password);

        // Mise à jour de la barre de progression
        strengthLevel.style.width = score + '%';

        // Mise à jour de la couleur en fonction de la force
        if (score < 30) {
            strengthLevel.style.backgroundColor = '#ff4d4d'; // Rouge
            strengthText.textContent = 'Faible';
        } else if (score < 60) {
            strengthLevel.style.backgroundColor = '#ffa64d'; // Orange
            strengthText.textContent = 'Moyen';
        } else if (score < 80) {
            strengthLevel.style.backgroundColor = '#99cc00'; // Vert clair
            strengthText.textContent = 'Bon';
        } else {
            strengthLevel.style.backgroundColor = '#00cc44'; // Vert
            strengthText.textContent = 'Excellent';
        }
    });
}

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