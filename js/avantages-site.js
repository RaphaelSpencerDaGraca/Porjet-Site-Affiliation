document.addEventListener('DOMContentLoaded', () => {
    const affichageLiens = document.getElementById('affiche-liens');
    const affichageCodes = document.getElementById('affiche-codes');
    const listeLiens = document.getElementById('liste-liens');
    const listeCodes = document.getElementById('liste-codes');
  
    listeCodes.style.display = 'none';

    affichageCodes.addEventListener('click', () => {
      affichageLiens.style.fontWeight = 'normal';
      affichageCodes.style.fontWeight = 'bolder';
      listeLiens.style.display = 'none';
      listeCodes.style.display = 'block';
    });
  
    affichageLiens.addEventListener('click', () => {
      affichageLiens.style.fontWeight = 'bolder';
      affichageCodes.style.fontWeight = 'normal';
      listeCodes.style.display = 'none';
      listeLiens.style.display = 'block';
    });
  
    // ← Voici la boucle sur tous les éléments .espace-code
    const codeCopies = document.querySelectorAll('.espace-code');
    codeCopies.forEach(el => {
      el.addEventListener('click', () => {
        const texte = el.textContent.trim();
        navigator.clipboard.writeText(texte)
          .then(() => alert("Code copié : " + texte))
          .catch(err => console.error("Échec de la copie :", err));
      });
    });
  });
  