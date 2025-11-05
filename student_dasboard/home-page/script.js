const sidebar = document.getElementById('sidebar');
const menuBtn = document.getElementById('menu-btn');

menuBtn.addEventListener('click', () => {
  sidebar.classList.toggle('open');
});

document.addEventListener('click', (e) => {
  if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
    sidebar.classList.remove('open');
  }
});

const links = document.querySelectorAll('.nav-link');
links.forEach(link => {
  link.addEventListener('click', function() {
    links.forEach(l => l.classList.remove('active'));
    this.classList.add('active');
  });
});

const profileToggle = document.getElementById('profile-toggle');
const profileSub = document.getElementById('profile-sub');
const settingsToggle = document.getElementById('settings-toggle');
const settingsSub = document.getElementById('settings-sub');

profileToggle.addEventListener('click', () => {
  profileSub.style.display = profileSub.style.display === 'block' ? 'none' : 'block';
  const arrow = profileToggle.querySelector('.arrow');
  arrow.textContent = profileSub.style.display === 'block' ? '▼' : '▶';
});

settingsToggle.addEventListener('click', () => {
  settingsSub.style.display = settingsSub.style.display === 'block' ? 'none' : 'block';
  const arrow = settingsToggle.querySelector('.arrow');
  arrow.textContent = settingsSub.style.display === 'block' ? '▼' : '▶';
});
