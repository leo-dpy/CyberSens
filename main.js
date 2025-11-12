// Plain JS interactions for mobile menu and best practice modal
(function() {
  function qs(sel){return document.querySelector(sel);}  
  const mobileBtn = qs('#open-mobile');
  const mobileMenu = qs('#mobile-menu');
  const bpBackdrop = qs('#bp-backdrop');
  const bpDialog = qs('#bp-dialog');
  const bpClose = qs('#bp-close');

  // Mobile menu toggle
  if(mobileBtn && mobileMenu){
    mobileBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
    });
  }

  // Show best practice dialog after 1s
  function openDialog(){
    bpBackdrop.classList.remove('hidden');
    bpDialog.classList.remove('hidden');
  }
  function closeDialog(){
    bpBackdrop.classList.add('hidden');
    bpDialog.classList.add('hidden');
  }

  if(bpDialog){
    setTimeout(openDialog, 1000);
  }

  if(bpClose){
    bpClose.addEventListener('click', closeDialog);
  }
  if(bpBackdrop){
    bpBackdrop.addEventListener('click', closeDialog);
  }

  // Initialize lucide icons
  if(window.lucide){
    window.lucide.createIcons();
  } else {
    document.addEventListener('DOMContentLoaded', () => {
      if(window.lucide){ window.lucide.createIcons(); }
    });
  }
})();