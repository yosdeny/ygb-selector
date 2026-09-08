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
      
      // Disable select while processing
      this.disabled = true;
      this.style.opacity = '0.7';
      this.style.cursor = 'not-allowed';
      
      if (msg) {
        msg.textContent = (ygbSelectorAjax && ygbSelectorAjax.labels && ygbSelectorAjax.labels.redirecting) || '';
        msg.style.display = 'block';
      }

      const formData = new FormData();
      formData.append('action', ygbSelectorAjax.setAction);
      formData.append('nonce', ygbSelectorAjax.nonce);
      formData.append('url', url);

      fetch(ygbSelectorAjax.url, { method: 'POST', credentials: 'same-origin', body: formData })
        .then(r => {
          if (!r.ok) {
            throw new Error('Server response error');
          }
          return r.json();
        })
        .then(res => {
          if (res && res.success) {
            window.location.href = url;
          } else {
            // Fallback redirect without exposing error details
            setTimeout(() => {
              window.location.href = url;
            }, 500);
          }
        })
        .catch(() => {
          // Silent fallback redirect on connection error
          setTimeout(() => {
            window.location.href = url;
          }, 1000);
        })
        .finally(() => {
          // Re-enable only if still on page
          setTimeout(() => {
            if (menu && menu.disabled) {
              menu.disabled = false;
              menu.style.opacity = '';
              menu.style.cursor = '';
              menu.value = '';
            }
          }, 2000);
        });
    });
  }

  document.addEventListener('DOMContentLoaded', init);
})();