document.addEventListener('DOMContentLoaded', () => {
    const radios = document.querySelectorAll('input[name="type-parrainage"]');
    const input  = document.getElementById('parrainage-input');
  
    // fonction de mise à jour du placeholder
    function majPlaceholder() {
      const choix = document.querySelector('input[name="type-parrainage"]:checked').value;
      input.placeholder = choix === 'lien'
        ? 'Lien de parrainage'
        : 'Code de parrainage';
    }
  
    // initialisation au chargement
    majPlaceholder();
  
    // à chaque changement, on met à jour
    radios.forEach(radio => {
      radio.addEventListener('change', majPlaceholder);
    });
  });
  