/* Elite Event — main.js */
'use strict';

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.role-toggle-option input[type="radio"]').forEach(function (input) {
    input.addEventListener('change', function () {
      document.querySelectorAll('.role-toggle-option').forEach(function (opt) {
        opt.classList.toggle('checked', opt.contains(input) && input.checked);
      });
    });
    if (input.checked) input.dispatchEvent(new Event('change'));
  });

  var current = window.location.pathname.split('/').pop() || 'index.php';
  document.querySelectorAll('.nav-link').forEach(function (link) {
    var href = link.getAttribute('href') || '';
    if (href === current || (current === '' && href === 'index.php')) link.classList.add('active');
  });
});
