// SIDEBAR
const menuBtn = document.getElementById('menuBtn');
const sidebar = document.getElementById('sidebar');
menuBtn.addEventListener('click', () => sidebar.classList.toggle('open'));
const profileToggle = document.getElementById('profileToggle');
const profileSub = document.getElementById('profileSub');
const settingsToggle = document.getElementById('settingsToggle');
const settingsSub = document.getElementById('settingsSub');
profileToggle.addEventListener('click', () => {
  profileSub.style.display = profileSub.style.display === 'block' ? 'none' : 'block';
  profileToggle.querySelector('.arrow').textContent = profileSub.style.display === 'block' ? '▼' : '▶';
});
settingsToggle.addEventListener('click', () => {
  settingsSub.style.display = settingsSub.style.display === 'block' ? 'none' : 'block';
  settingsToggle.querySelector('.arrow').textContent = settingsSub.style.display === 'block' ? '▼' : '▶';
});
// (and continue the rest of your JavaScript code exactly as-is)
