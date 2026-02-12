/**
 * Selector de tema de color — Admin ISP
 * Independiente del modo claro/oscuro. Persistencia en localStorage.
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'colorTheme';
  const THEMES = ['indigo', 'blue', 'green', 'teal'];

  function getStored() {
    return localStorage.getItem(STORAGE_KEY);
  }

  function setStored(value) {
    if (THEMES.includes(value)) {
      localStorage.setItem(STORAGE_KEY, value);
    }
  }

  function apply(colorTheme) {
    const root = document.documentElement;
    if (THEMES.includes(colorTheme)) {
      root.setAttribute('data-color-theme', colorTheme);
      setStored(colorTheme);
      updateActiveSwatch(colorTheme);
    }
  }

  function updateActiveSwatch(theme) {
    document.querySelectorAll('[data-color-theme-switch]').forEach(function (el) {
      if (el.getAttribute('data-color-theme-switch') === theme) {
        el.classList.add('active');
        el.setAttribute('aria-pressed', 'true');
      } else {
        el.classList.remove('active');
        el.setAttribute('aria-pressed', 'false');
      }
    });
  }

  function getCurrent() {
    const stored = getStored();
    return THEMES.includes(stored) ? stored : 'indigo';
  }

  function set(theme) {
    if (THEMES.includes(theme)) {
      apply(theme);
      return theme;
    }
    return getCurrent();
  }

  function init() {
    const theme = getCurrent();
    apply(theme);
  }

  window.ColorTheme = {
    set: set,
    getCurrent: getCurrent,
    init: init,
    THEMES: THEMES,
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
