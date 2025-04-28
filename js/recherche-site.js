document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('recherche');
    const items = document.querySelectorAll('#liste-sites li');

    input.addEventListener('input', function() {
      const terme = this.value.trim().toLowerCase();

      items.forEach(li => {
        const nom = li.querySelector('strong').textContent.toLowerCase();
        // si le nom contient le terme, on affiche, sinon on masque
        li.style.display = nom.includes(terme) ? '' : 'none';
      });
    });
  });