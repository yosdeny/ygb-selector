(function () {
  function applyColors() {
    const content = document.getElementById('ygb-selector-popup-content');
    if (!content) return;
    const bg = content.getAttribute('data-bg') || '#fff';
    const text = content.getAttribute('data-text') || '#333';
    const title = content.getAttribute('data-title') || '#E26143';
    const accent = content.getAttribute('data-accent') || '#0073aa';

    content.style.setProperty('--ygb-bg', bg);
    content.style.setProperty('--ygb-text', text);
    content.style.setProperty('--ygb-title', title);
    content.style.setProperty('--ygb-accent', accent);
  }

  function init() {
    applyColors();

    const menu = document.getElementById('ygb-selector-menu');
    const msg = document.getElementById('ygb-selector-redirect-msg');
    if (!menu) return;

    menu.addEventListener('change', function () {
      const url = this.value;
      if (!url) return;
      
      // Deshabilitar select mientras procesa
      this.disabled = true;
      this.style.opacity = '0.7';
      this.style.cursor = 'not-allowed';
      
      if (msg) {
        msg.textContent = (ygbSelectorAjax && ygbSelectorAjax.labels && ygbSelectorAjax.labels.redirecting) || 'Redirigiendo...';
        msg.style.display = 'block';
      }

      const formData = new FormData();
      formData.append('action', ygbSelectorAjax.setAction);
      formData.append('nonce', ygbSelectorAjax.nonce);
      formData.append('url', url);

      fetch(ygbSelectorAjax.url, { method: 'POST', credentials: 'same-origin', body: formData })
        .then(r => {
          if (!r.ok) {
            throw new Error('Error en la respuesta del servidor');
          }
          return r.json();
        })
        .then(res => {
          if (res && res.success) {
            window.location.href = url;
          } else {
            if (msg) {
              msg.textContent = (ygbSelectorAjax.labels && ygbSelectorAjax.labels.errorSet) || 'Error al fijar la cookie. Redirigiendo…';
              msg.style.color = '#dc3232';
            }
            // Redirigir igual como fallback
            setTimeout(() => {
              window.location.href = url;
            }, 1000);
          }
        })
        .catch((error) => {
          console.error('YGB Selector Error:', error);
          if (msg) {
            msg.textContent = (ygbSelectorAjax.labels && ygbSelectorAjax.labels.errorConn) || 'Error de conexión. Redirigiendo…';
            msg.style.color = '#dc3232';
          }
          // Redirigir como fallback después de 2 segundos
          setTimeout(() => {
            window.location.href = url;
          }, 2000);
        })
        .finally(() => {
          // Solo re-habilitar si aún estamos en la página
          setTimeout(() => {
            if (menu && menu.disabled) {
              menu.disabled = false;
              menu.style.opacity = '';
              menu.style.cursor = '';
              menu.value = '';
            }
          }, 3000);
        });
    });
  }

  document.addEventListener('DOMContentLoaded', init);
})();