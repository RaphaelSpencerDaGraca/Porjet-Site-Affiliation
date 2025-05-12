

document.addEventListener('DOMContentLoaded', () => {
    const showBtn = document.getElementById('show-affiliate-form-btn');
    const formContainer = document.getElementById('affiliate-form-container');
    const cancelLink = document.getElementById('cancel-link-form');
    const cancelCode = document.getElementById('cancel-code-form');
    const tabBtns = document.querySelectorAll('.tab-btn');
    const linkForm = document.getElementById('link-form');
    const codeForm = document.getElementById('code-form');
  
    // Afficher / cacher le conteneur global
    showBtn.addEventListener('click', () => {
      formContainer.style.display = 'block';
    });
    cancelLink.addEventListener('click', () => {
      formContainer.style.display = 'none';
      // réinitialiser l’onglet actif si besoin
      linkForm.classList.add('active');
      codeForm.classList.remove('active');
      tabBtns[0].classList.add('active');
      tabBtns[1].classList.remove('active');
    });
    cancelCode.addEventListener('click', () => {
      formContainer.style.display = 'none';
      linkForm.classList.add('active');
      codeForm.classList.remove('active');
      tabBtns[0].classList.add('active');
      tabBtns[1].classList.remove('active');
    });
  
    // Changement d’onglets (Lien ↔ Code)
    tabBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        tabBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
  
        if (btn.dataset.tab === 'link-form') {
          linkForm.classList.add('active');
          codeForm.classList.remove('active');
        } else {
          codeForm.classList.add('active');
          linkForm.classList.remove('active');
        }
      });
    });

    function copyToClipboard(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    
        alert('Copié dans le presse-papier !');
    }
  });