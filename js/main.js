// Main entry point for site behavior.
(function () {
  'use strict';

  var moveTop = document.getElementById('movetop');
  if (!moveTop) return;

  window.addEventListener('scroll', function () {
    moveTop.style.display = window.scrollY > 20 ? 'block' : 'none';
  });

  moveTop.addEventListener('click', function () {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
})();
