document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la sélection des méthodes de paiement
    const paymentMethods = document.querySelectorAll('.payment-method');

    paymentMethods.forEach(method => {
        method.addEventListener('click', function() {
            // Désélectionner toutes les méthodes
            paymentMethods.forEach(m => m.classList.remove('selected'));

            // Sélectionner celle-ci
            this.classList.add('selected');

            // Cocher le radio button
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
        });
    });
});