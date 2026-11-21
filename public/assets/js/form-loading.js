(function () {
  var forms = document.querySelectorAll('form[data-loading-overlay]');
  if (!forms.length) {
    return;
  }

  var overlay = document.createElement('div');
  overlay.className = 'app-loading-overlay';
  overlay.hidden = true;
  overlay.setAttribute('role', 'status');
  overlay.setAttribute('aria-live', 'polite');

  var image = document.createElement('img');
  image.src = 'public/assets/images/cargando-camion.svg';
  image.alt = '';
  image.setAttribute('aria-hidden', 'true');
  overlay.appendChild(image);

  var accessibleMessage = document.createElement('span');
  accessibleMessage.className = 'sr-only';
  accessibleMessage.textContent = 'Procesando tu solicitud, espera un momento.';
  overlay.appendChild(accessibleMessage);
  document.body.appendChild(overlay);

  forms.forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (form.dataset.loading === 'true') {
        event.preventDefault();
        return;
      }

      form.dataset.loading = 'true';
      form.setAttribute('aria-busy', 'true');

      var submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
      if (submitButton) {
        submitButton.disabled = true;
      }

      overlay.hidden = false;
    });
  });
}());
