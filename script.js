// Typing Animation for Subtitle - SMOOTH CONTINUOUS
const subtitleTexts = [
  'STUDENT <span class="red">C</span>OMPLAINTS AND <span class="red">S</span>UGGESTIONS TRACKING SYSTEM <span class="red">.</span><span class="red">.</span>',
  'SECURE COMMUNICATION ENHANCING STUDENT MANAGEMENT<span class="red">.</span><span class="red">.</span>',
  'COMPLAINTS AND SUGGESTION EXPEDITED TRANSPARENCY INCLUDING <span class="red">R</span>ESPONSE AND <span class="red">F</span>EEDBACK MECHANISM<span class="red">.</span><span class="red">.</span>'
];

let currentTextIndex = 0;
let charIndex = 0;
let isDeleting = false;
const subtitleElement = document.getElementById('dynamic-subtitle');
const typingSpeed = 40;
const deletingSpeed = 20;

function typeEffect() {
  const currentText = subtitleTexts[currentTextIndex];
  
  if (!isDeleting) {
    // Typing - continuous
    if (charIndex < currentText.length) {
      const displayText = currentText.substring(0, charIndex + 1);
      subtitleElement.innerHTML = displayText + '<span class="cursor"></span>';
      charIndex++;
      setTimeout(typeEffect, typingSpeed);
    } else {
      // Start deleting immediately after typing completes
      isDeleting = true;
      setTimeout(typeEffect, deletingSpeed);
    }
  } else {
    // Deleting - continuous
    if (charIndex > 0) {
      charIndex--;
      const displayText = currentText.substring(0, charIndex);
      subtitleElement.innerHTML = displayText + '<span class="cursor"></span>';
      setTimeout(typeEffect, deletingSpeed);
    } else {
      // Move to next text immediately
      isDeleting = false;
      currentTextIndex = (currentTextIndex + 1) % subtitleTexts.length;
      charIndex = 0;
      setTimeout(typeEffect, typingSpeed);
    }
  }
}

// Initialize typing animation
typeEffect();

// Image Transition Animation for Hexagons
const hexagonWrappers = document.querySelectorAll('.hexagon-wrapper');
const transitionInterval = 4000; // Change image every 4 seconds

hexagonWrappers.forEach((hexagon, index) => {
  // Stagger the start time for each hexagon
  const startDelay = index * 800;
  
  setTimeout(() => {
    setInterval(() => {
      hexagon.classList.toggle('active-transition');
    }, transitionInterval);
  }, startDelay);
});

// Smooth scroll behavior
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({
        behavior: 'smooth'
      });
    }
  });
});

// Add animation to button on page load
window.addEventListener('load', () => {
  const navButton = document.querySelector('.nav-button');
  navButton.style.animation = 'fadeInUp 0.8s ease 0.5s forwards';
});

// CSS animation injection for fade-in effects
const style = document.createElement('style');
style.textContent = `
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
`;
document.head.appendChild(style);