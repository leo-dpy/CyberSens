// Interactions JS simples pour le menu mobile et la modale de bonnes pratiques
(function () {
  // Fonction utilitaire pour sélectionner un élément du DOM
  function selectionner(selecteur) { return document.querySelector(selecteur); }

  // Sélection des éléments du DOM avec les nouveaux IDs en français
  const boutonMobile = selectionner('#ouvrir-mobile');
  const menuMobile = selectionner('#menu-mobile');
  const fondBonnesPratiques = selectionner('#fond-bonnes-pratiques');
  const dialogueBonnesPratiques = selectionner('#dialogue-bonnes-pratiques');
  const boutonFermerBP = selectionner('#fermer-bonnes-pratiques');
  const boutonOuvrirBP = selectionner('#ouvrir-bonnes-pratiques');

  // Gestion du menu mobile
  if (boutonMobile && menuMobile) {
    boutonMobile.addEventListener('click', () => {
      menuMobile.classList.toggle('hidden');
    });
  }

  // Fonction pour ouvrir la boîte de dialogue des bonnes pratiques
  function ouvrirDialogue() {
    fondBonnesPratiques.classList.remove('hidden');
    dialogueBonnesPratiques.classList.remove('hidden');
    if (boutonOuvrirBP) boutonOuvrirBP.classList.add('hidden');
  }

  // Fonction pour fermer la boîte de dialogue des bonnes pratiques
  function fermerDialogue() {
    fondBonnesPratiques.classList.add('hidden');
    dialogueBonnesPratiques.classList.add('hidden');
    if (boutonOuvrirBP) boutonOuvrirBP.classList.remove('hidden');
  }

  // Ouverture automatique une seule fois par session
  if (dialogueBonnesPratiques) {
    try {
      const aEteAffiche = sessionStorage.getItem('bpAffiche');
      if (!aEteAffiche) {
        sessionStorage.setItem('bpAffiche', '1');
        setTimeout(ouvrirDialogue, 1000);
      } else {
        // Ne pas rouvrir automatiquement lors de la navigation ; s'assurer que le bouton d'ouverture est visible
        if (boutonOuvrirBP) boutonOuvrirBP.classList.remove('hidden');
      }
    } catch (e) {
      // Si sessionStorage n'est pas disponible, préférer ne pas ouvrir automatiquement à répétition
      if (boutonOuvrirBP) boutonOuvrirBP.classList.remove('hidden');
    }
  }

  // Ajout des écouteurs d'événements pour les boutons
  if (boutonFermerBP) {
    boutonFermerBP.addEventListener('click', fermerDialogue);
  }
  if (fondBonnesPratiques) {
    fondBonnesPratiques.addEventListener('click', fermerDialogue);
  }
  if (boutonOuvrirBP) {
    boutonOuvrirBP.addEventListener('click', ouvrirDialogue);
  }

  // Fermer avec la touche Échap pour l'accessibilité
  document.addEventListener('keydown', (e) => {
    if (dialogueBonnesPratiques && e.key === 'Escape' && !dialogueBonnesPratiques.classList.contains('hidden')) {
      fermerDialogue();
    }
  });

  // Initialiser les icônes Lucide
  if (window.lucide) {
    window.lucide.createIcons();
  } else {
    document.addEventListener('DOMContentLoaded', () => {
      if (window.lucide) { window.lucide.createIcons(); }
    });
  }
})();