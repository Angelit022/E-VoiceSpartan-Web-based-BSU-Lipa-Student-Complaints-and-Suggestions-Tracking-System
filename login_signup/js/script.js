const loginForm = document.getElementById('loginForm');
const loginSection = document.getElementById('login-section');
const signupSection = document.getElementById('signup-section');
const showSignup = document.getElementById('showSignup');
const backToLogin = document.getElementById('backToLogin');

loginForm.addEventListener('submit', (e) => {
  e.preventDefault();
  window.location.href = "homepage.html";
});

showSignup.addEventListener('click', (e) => {
  e.preventDefault();
  loginSection.style.display = 'none';
  signupSection.style.display = 'flex';
});

backToLogin.addEventListener('click', (e) => {
  e.preventDefault();
  signupSection.style.display = 'none';
  loginSection.style.display = 'flex';
});
