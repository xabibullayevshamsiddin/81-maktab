(() => {
  const storageKey = 'site-theme';
  const savedTheme = localStorage.getItem(storageKey);
  const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  const theme = savedTheme === 'dark' || savedTheme === 'light'
    ? savedTheme
    : (systemPrefersDark ? 'dark' : 'light');

  document.documentElement.setAttribute('data-theme', theme);
  document.addEventListener('DOMContentLoaded', () => {
    document.body.setAttribute('data-theme', theme);
  });

  window.loadDonorFont = function(fontName) {
    if (!fontName) return;
    var fontMap = {
      'orbitron': 'Orbitron:wght@700;800;900',
      'caveat': 'Caveat:wght@700',
      'press-start': 'Press+Start+2P',
      'pacifico': 'Pacifico',
      'righteous': 'Righteous',
      'bungee': 'Bungee',
      'permanent-marker': 'Permanent+Marker'
    };
    var param = fontMap[fontName];
    if (!param) return;
    var id = 'font-donor-' + fontName;
    if (!document.getElementById(id)) {
      var link = document.createElement('link');
      link.id = id;
      link.rel = 'stylesheet';
      link.href = 'https://fonts.googleapis.com/css2?family=' + param + '&display=swap';
      document.head.appendChild(link);
    }
  };
})();
