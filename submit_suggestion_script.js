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
  profileToggle.querySelector('.arrow').textContent =
    profileSub.style.display === 'block' ? '▼' : '▶';
});

settingsToggle.addEventListener('click', () => {
  settingsSub.style.display = settingsSub.style.display === 'block' ? 'none' : 'block';
  settingsToggle.querySelector('.arrow').textContent =
    settingsSub.style.display === 'block' ? '▼' : '▶';
});

// STEPS
const step1 = document.getElementById('step1');
const step2 = document.getElementById('step2');
const step3 = document.getElementById('step3');
const step4 = document.getElementById('step4');
const step5 = document.getElementById('step5');
const nextBtn = document.getElementById('nextBtn');
const descBox = document.getElementById('descBox');
const uploadBox = document.getElementById('uploadBox');
const reviewWrap = document.getElementById('reviewWrap');
const stepsCircles = document.querySelectorAll('.step');
const catButtons = document.querySelectorAll('.category-btn');
let selectedCategories = [];

function setActiveStep(n) {
  stepsCircles.forEach((el, i) => {
    if (i === n) el.classList.add('active');
    else el.classList.remove('active');
  });
}

// CATEGORY SELECTION
catButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    const cat = btn.textContent.trim();
    btn.classList.toggle('selected');
    if (btn.classList.contains('selected')) {
      if (!selectedCategories.includes(cat)) selectedCategories.push(cat);
    } else {
      selectedCategories = selectedCategories.filter(c => c !== cat);
    }
  });
});

// STEP 1 -> STEP 2
nextBtn.addEventListener('click', () => {
  if (selectedCategories.length === 0) {
    alert('Please select at least one category.');
    return;
  }
  step1.style.display = 'none';
  step2.style.display = 'flex';
  setActiveStep(1);
  descBox.innerHTML = '';
  selectedCategories.forEach((cat, i) => {
    descBox.innerHTML += `
      <div style="margin-bottom:16px;">
        <h3 style="color:var(--accent);margin-bottom:6px;">${cat}</h3>
        <textarea id="desc-${i}" placeholder="Describe your suggestion about ${cat}..."></textarea>
      </div>`;
  });
  descBox.innerHTML += `
    <div class="controls">
      <button id="backTo1" class="btn">Back</button>
      <button id="toStep3" class="btn">Next</button>
    </div>`;
  document.getElementById('backTo1').onclick = () => {
    step2.style.display = 'none';
    step1.style.display = 'block';
    setActiveStep(0);
  };
  document.getElementById('toStep3').onclick = () => {
    goToStep3();
  };
});

function goToStep3() {
  const descriptions = [];
  selectedCategories.forEach((_, i) => {
    const val = document.getElementById(`desc-${i}`).value.trim();
    descriptions.push(val);
  });
  step2.style.display = 'none';
  step3.style.display = 'flex';
  setActiveStep(2);
  uploadBox.innerHTML = '';
  selectedCategories.forEach((cat, i) => {
    uploadBox.innerHTML += `
      <div style="margin-bottom:30px;text-align:left;">
        <h3 style="color:var(--accent);margin-bottom:4px;">${cat}</h3>
        <p><strong>Description:</strong> ${descriptions[i] || '(none)'}</p>
        <input type="file" id="file-${i}" multiple>
        <label for="file-${i}">Attach File</label>
        <div id="fileList-${i}" class="file-list"></div>
      </div>`;
  });
  uploadBox.innerHTML += `
    <div class="controls" style="margin-top:20px;">
      <button id="backTo2" class="btn">Back</button>
      <button id="toStep4" class="btn">Next</button>
    </div>`;
  document.getElementById('backTo2').onclick = () => {
    step3.style.display = 'none';
    step2.style.display = 'flex';
    setActiveStep(1);
  };
  selectedCategories.forEach((_, i) => {
    document.getElementById(`file-${i}`).addEventListener('change', e => {
      const list = document.getElementById(`fileList-${i}`);
      const files = Array.from(e.target.files);
      list.innerHTML = files.map(f => `• ${f.name}`).join('<br>');
    });
  });
  document.getElementById('toStep4').onclick = () => {
    goToStep4(descriptions);
  };
}

function goToStep4(descriptions) {
  step3.style.display = 'none';
  step4.style.display = 'flex';
  setActiveStep(3);
  reviewWrap.innerHTML = '';
  selectedCategories.forEach((cat, i) => {
    reviewWrap.innerHTML += `
      <div class="review-item">
        <h3>${cat}</h3>
        <p><strong>Description:</strong> ${descriptions[i] || '(none)'}</p>
      </div>`;
  });
  reviewWrap.innerHTML += `
    <div class="controls">
      <button id="backTo3" class="btn">Back</button>
      <button id="toStep5" class="btn">Next</button>
    </div>`;
  document.getElementById('backTo3').onclick = () => {
    step4.style.display = 'none';
    step3.style.display = 'flex';
    setActiveStep(2);
  };
  document.getElementById('toStep5').onclick = () => {
    step4.style.display = 'none';
    step5.style.display = 'flex';
    setActiveStep(4);
  };
}

document.getElementById('backTo4').onclick = () => {
  step5.style.display = 'none';
  step4.style.display = 'flex';
  setActiveStep(3);
};

document.getElementById('submitBtn').onclick = () => {
  const check = document.getElementById('confirmCheck');
  if (!check.checked) {
    alert('Please confirm before submitting.');
    return;
  }
  alert('Suggestion submitted successfully!');
};


