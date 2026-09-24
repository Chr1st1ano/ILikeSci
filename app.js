// Default App State — all content (topics, lessons, questions) loaded from database
const defaultState = {
  isLoggedIn: false,
  currentUser: null,
  currentRole: 'teacher',
  currentView: 'dashboard',
  soundEnabled: true,
  musicEnabled: true,
  streak: 0,
  gems: 0,
  xp: 0,
  performanceEnabled: false,
  students: [],
  topics: {},
  lessons: {},
  questions: [],
  pointsMap: {
    'Easy': 1,
    'Medium': 3,
    'Hard': 5
  },
  assessment: {
    grade: '4',
    topic: '',
    difficulty: 'Easy',
    studentId: null,
    activeQuestion: '',
    useCurriculum: false
  },
  lesson: {
    grade: '4',
    topic: '',
    currentSlide: 0,
    slides: [],
    useCurriculum: false,
    hasQuestions: false,
    curriculumId: null
  },
  profile: {
    name: "Science Teacher",
    bio: "Intermediate Grade Teacher at Central Elementary School",
    picture: null,
    border: "none"
  },
  customLessons: [],
  isLoggedIn: false
};


// Admin users — loaded from database only (no hardcoded credentials)
let adminUsers = [];

let state = { ...defaultState };

const SHARED_PAGE_BUTTONS = [
  { href: 'students.html', label: 'Students', icon: 'fa-solid fa-users' },
  { href: 'lessons.html', label: 'Lessons', icon: 'fa-solid fa-chalkboard-user' },
  { href: 'multimedia.html', label: 'Multimedia', icon: 'fa-solid fa-photo-film' },
  { href: 'games.html', label: 'Games', icon: 'fa-solid fa-gamepad' },
  { href: 'materials.html', label: 'Materials', icon: 'fa-solid fa-book' },
  { href: 'records.html', label: 'Records', icon: 'fa-solid fa-folder-open' },
  { href: 'profile.html', label: 'Profile', icon: 'fa-solid fa-user-gear' },
  { href: 'admin.html', label: 'Admin', icon: 'fa-solid fa-shield-halved' }
];

// Load and Save state
function loadState() {
  const saved = localStorage.getItem('ilikesci_hybrid_state');
  if (saved) {
    try {
      const parsed = JSON.parse(saved);
      state = { ...state, ...parsed };
    } catch(e) { console.error('Error loading state'); }
  }

  // Load admin users
  const savedAdmins = localStorage.getItem('ilikesci_admin_users');
  if (savedAdmins) {
    try {
      adminUsers = JSON.parse(savedAdmins);
    } catch(e) { console.error('Error loading admin users'); }
  }
}

function saveState() {
  localStorage.setItem('ilikesci_hybrid_state', JSON.stringify(state));
  localStorage.setItem('ilikesci_admin_users', JSON.stringify(adminUsers));
}

const CURRICULUM_LESSONS_CACHE_KEY = 'ilikesci_curriculum_lessons';
const CURRICULUM_SLIDES_CACHE_KEY = 'ilikesci_curriculum_slides';

function loadCachedJson(key) {
  try {
    return JSON.parse(localStorage.getItem(key)) || {};
  } catch (e) {
    return {};
  }
}

function saveCachedJson(key, value) {
  localStorage.setItem(key, JSON.stringify(value));
}

function cacheCurriculumLessons(grade, lessons) {
  const cache = loadCachedJson(CURRICULUM_LESSONS_CACHE_KEY);
  cache[grade] = lessons;
  saveCachedJson(CURRICULUM_LESSONS_CACHE_KEY, cache);
}

function getCachedCurriculumLessons(grade) {
  const cache = loadCachedJson(CURRICULUM_LESSONS_CACHE_KEY);
  return Array.isArray(cache[grade]) ? cache[grade] : [];
}

function cacheCurriculumSlides(curriculumId, slides) {
  const cache = loadCachedJson(CURRICULUM_SLIDES_CACHE_KEY);
  cache[curriculumId] = slides;
  saveCachedJson(CURRICULUM_SLIDES_CACHE_KEY, cache);
}

function getCachedCurriculumSlides(curriculumId) {
  const cache = loadCachedJson(CURRICULUM_SLIDES_CACHE_KEY);
  return Array.isArray(cache[curriculumId]) ? cache[curriculumId] : [];
}

async function fetchCurriculumLessons(grade) {
  try {
    const response = await fetch(`lessons_api.php?grade=${grade}`);
    const data = await response.json();
    if (data.status === 'success' && Array.isArray(data.lessons) && data.lessons.length > 0) {
      cacheCurriculumLessons(grade, data.lessons);
      return data.lessons;
    }
  } catch (e) {
    console.error('Failed to load curriculum lessons:', e);
  }

  return getCachedCurriculumLessons(grade);
}

async function fetchCurriculumSlides(curriculumId) {
  try {
    const response = await fetch(`lessons_api.php?slides=${curriculumId}`);
    const data = await response.json();
    if (data.status === 'success' && Array.isArray(data.slides) && data.slides.length > 0) {
      cacheCurriculumSlides(curriculumId, data.slides);
      return data.slides;
    }
  } catch (e) {
    console.error('Failed to load lesson slides:', e);
  }

  return getCachedCurriculumSlides(curriculumId);
}

// Initialize App
document.addEventListener('DOMContentLoaded', () => {
  loadState();
  initNavigation();
  registerServiceWorker();
  checkAIStatus(); // Check AI availability on load
  loadTheme();
  initKeyboardShortcuts();

  // Auth guard: redirect to login.html if not authenticated
  const currentPage = (window.location.pathname.split('/').pop() || 'index.html').toLowerCase();
  const publicPages = ['login.html', 'landing.html', 'signup.html'];
  if (!state.isLoggedIn && !publicPages.includes(currentPage)) {
    // Anti-flicker: prevent redirect loop
    const loopGuard = sessionStorage.getItem('ilikesci_index_guard');
    const now = Date.now();
    if (loopGuard && (now - parseInt(loopGuard)) < 2000) {
      console.warn('ILikeSci: Redirect loop detected in app.js, halting.');
      return;
    }
    sessionStorage.setItem('ilikesci_index_guard', now.toString());
    window.location.href = 'login.html';
    return;
  }

  // Fetch user role + profile from DB on every page load (fixes old sessions missing currentRole)
  if (state.currentUser && state.isLoggedIn) {
    // Fetch role from users table via auth.php GET
    fetch('auth.php')
      .then(r => r.json())
      .then(data => {
        if (data.status === 'success' && Array.isArray(data.users)) {
          const me = data.users.find(u => u.username === state.currentUser);
          if (me) {
            state.currentRole = me.role || 'teacher';
            saveState();
            // Re-render hamburger menu now that role is known
            const oldPanel = document.getElementById('hamburger-panel');
            const oldBtn = document.getElementById('hamburger-toggle');
            if (oldPanel) oldPanel.remove();
            if (oldBtn) oldBtn.remove();
            renderSharedPageButtons();
          }
        }
      })
      .catch(() => { /* offline — keep existing role */ });

    // Load per-user profile (avatar, bio, border)
    fetch('profile_api.php?username=' + encodeURIComponent(state.currentUser))
      .then(r => r.json())
      .then(data => {
        if (data.status === 'success' && data.profile) {
          const p = data.profile;
          if (p.display_name) state.profile.name = p.display_name;
          if (p.bio) state.profile.bio = p.bio;
          if (p.avatar_data) state.profile.picture = p.avatar_data;
          if (p.border_style) state.profile.border = p.border_style;
          applyProfileUI();
        }
      })
      .catch(() => { /* offline fallback */ });
  }

  // Admin guard: redirect teachers away from admin.html
  // If currentRole is stale (missing from old sessions), check DB first
  if (currentPage === 'admin.html' && state.currentRole !== 'admin') {
    if (state.currentUser) {
      // Prevent infinite reload loop
      const adminGuard = sessionStorage.getItem('ilikesci_admin_guard');
      const now2 = Date.now();
      if (adminGuard && (now2 - parseInt(adminGuard)) < 3000) {
        // Already tried once recently, don't loop
        console.warn('Admin guard: already checked recently, allowing page.');
      } else {
        sessionStorage.setItem('ilikesci_admin_guard', now2.toString());
        fetch('auth.php')
          .then(r => r.json())
          .then(data => {
            if (data.status === 'success' && Array.isArray(data.users)) {
              const me = data.users.find(u => u.username === state.currentUser);
              if (me && me.role === 'admin') {
                state.currentRole = 'admin';
                saveState();
                location.reload();
              } else {
                window.location.href = 'index.html';
              }
            } else {
              window.location.href = 'index.html';
            }
          })
          .catch(() => { window.location.href = 'index.html'; });
      }
    } else {
      window.location.href = 'index.html';
      return;
    }
  }

  checkLoginStatus();

  // Update sound/music toggle buttons based on state
  updateSoundMusicButtons();

  // Removed stats counters
  syncTopNavTabs();
  renderSharedPageButtons();
  applyProfileUI();
  applyPerformanceUI();

  // Auto-sync students from MySQL database on every page load
  syncStudentsFromDB().then(() => {
    // Initial renders based on which page we're on
    if (document.getElementById('dash-grade-select') && document.getElementById('dash-topic-select')) {
      updateDashboardTopics();
    }
    if (document.getElementById('students-container') && document.getElementById('grade-filter')) renderStudents();
    if (document.getElementById('assess-grade') && document.getElementById('assess-topic')) updateAssessmentTopics();
    if (document.getElementById('mat-grade') && document.getElementById('mat-topic')) {
      updateMaterialTopics();
      renderQuestionBank();
    }
    if (document.getElementById('assess-student')) renderAssessmentStudents();
    if (document.getElementById('records-body')) renderRecords();
    if (document.getElementById('scoreboard-body')) renderScoreboard();
    if (document.getElementById('lesson-grade') && document.getElementById('lesson-topic')) updateLessonTopics();
    if (document.getElementById('admin-stat-grid')) {
      loadAdminStats();
      loadAdminUsers();
      loadSystemSettings();
      if (document.getElementById('eclass-grade') && document.getElementById('eclass-section')) {
        updateSectionDropdown('eclass-grade', 'eclass-section', true);
      }
      if (document.getElementById('admin-student-grade') && document.getElementById('admin-student-section')) {
        updateSectionDropdown('admin-student-grade', 'admin-student-section', false);
      }
    } else if (document.getElementById('admin-user-list')) {
      renderAdminUsers();
      loadSystemSettings();
    }
    if (document.getElementById('custom-lessons-list')) renderCustomLessonsList();
    // Auto-load E-Class records on records page
    if (document.getElementById('eclass-table-container') && typeof loadEClassRecords === 'function') {
      loadEClassRecords();
    }
  });
});

function updateSoundMusicButtons() {
  const soundBtn = document.getElementById('toggle-sound');
  const musicBtn = document.getElementById('toggle-music');

  if (soundBtn) {
    if (state.soundEnabled) {
      soundBtn.innerHTML = '<i class="fa-solid fa-volume-high"></i> ON';
      soundBtn.classList.remove('off');
    } else {
      soundBtn.innerHTML = '<i class="fa-solid fa-volume-xmark"></i> OFF';
      soundBtn.classList.add('off');
    }
  }

  if (musicBtn) {
    if (state.musicEnabled) {
      musicBtn.innerHTML = '<i class="fa-solid fa-music"></i> ON';
      musicBtn.classList.remove('off');
    } else {
      musicBtn.innerHTML = '<i class="fa-solid fa-volume-xmark"></i> OFF';
      musicBtn.classList.add('off');
    }
  }
}

function togglePerformance() {
  state.performanceEnabled = !state.performanceEnabled;
  applyPerformanceUI();
  saveState();
}

function applyPerformanceUI() {
  const btn = document.getElementById('toggle-performance');
  if (state.performanceEnabled) {
    document.body.classList.add('performance-mode');
    if (btn) btn.innerHTML = '<i class="fa-solid fa-bolt"></i> ON';
  } else {
    document.body.classList.remove('performance-mode');
    if (btn) btn.innerHTML = '<i class="fa-solid fa-bolt"></i> OFF';
  }
}

let logoClickCount = 0;
function oyoEasterEgg() {
  logoClickCount++;
  if (logoClickCount >= 5) {
    alert("🚀 OYO SPEED MODE ACTIVATED! Greetings to the Creator!");
    document.body.style.filter = "invert(100%)";
    setTimeout(() => { document.body.style.filter = "none"; }, 500);
    logoClickCount = 0;
  }
}

// Removed updateStatsCounters function

function getCurrentPageName() {
  const currentPath = window.location.pathname.split('/').pop();
  return (currentPath || 'index.html').toLowerCase();
}

function syncTopNavTabs() {
  const pageName = getCurrentPageName();
  const activeTabs = {
    learn: ['index.html', 'students.html', 'lessons.html', 'multimedia.html', 'games.html', 'materials.html', 'profile.html', 'admin.html', 'dashboard.html', 'index-tablet.html', 'layout.html'],
    practice: ['assessment.html'],
    leaderboard: ['scoreboard.html', 'records.html']
  };

  document.querySelectorAll('.top-nav .nav-tab').forEach(tab => {
    const viewName = tab.getAttribute('data-view');
    const shouldBeActive = (activeTabs[viewName] || []).includes(pageName);
    tab.classList.toggle('active', shouldBeActive);
  });
}

function renderSharedPageButtons() {
  // Render a hamburger menu button in the top nav instead of inline buttons
  const topNav = document.querySelector('.top-nav');
  if (!topNav || document.getElementById('hamburger-toggle')) return;

  const pageName = getCurrentPageName();

  // Create hamburger button in top-nav-left
  const navLeft = topNav.querySelector('.top-nav-left');
  if (navLeft) {
    const hamburgerBtn = document.createElement('button');
    hamburgerBtn.id = 'hamburger-toggle';
    hamburgerBtn.className = 'btn-icon hamburger-btn';
    hamburgerBtn.title = 'Teacher Tools';
    hamburgerBtn.innerHTML = '<i class="fa-solid fa-bars"></i>';
    hamburgerBtn.onclick = toggleHamburgerMenu;
    navLeft.insertBefore(hamburgerBtn, navLeft.firstChild);
  }

  // Create dropdown panel
  const panel = document.createElement('div');
  panel.id = 'hamburger-panel';
  panel.className = 'hamburger-panel hidden';
  // Filter out Admin link for non-admin users
  const isAdmin = state.currentRole === 'admin';
  const visibleButtons = SHARED_PAGE_BUTTONS.filter(item => {
    if (item.href === 'admin.html' && !isAdmin) return false;
    return true;
  });

  panel.innerHTML = `
    <div class="hamburger-panel-header">
      <h3><i class="fa-solid fa-grid-2"></i> Teacher Tools</h3>
      <button class="btn-icon" onclick="toggleHamburgerMenu()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="hamburger-panel-nav">
      ${visibleButtons.map(item => `
        <a class="hamburger-item${pageName === item.href ? ' active' : ''}" href="${item.href}">
          <i class="${item.icon}"></i>
          <span>${item.label}</span>
        </a>
      `).join('')}
    </div>
  `;
  document.body.appendChild(panel);

  // Close on click outside
  document.addEventListener('click', (e) => {
    const panel = document.getElementById('hamburger-panel');
    const btn = document.getElementById('hamburger-toggle');
    if (panel && !panel.classList.contains('hidden') && !panel.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
      panel.classList.add('hidden');
    }
  });
}

function toggleHamburgerMenu() {
  const panel = document.getElementById('hamburger-panel');
  if (panel) panel.classList.toggle('hidden');
}

// Login Logic — login is now on login.html, so this ensures main app is visible and login overlay is dismissed
function checkLoginStatus() {
  const mainApp = document.getElementById('main-app');
  const loginScreen = document.getElementById('login-screen');
  if (state.isLoggedIn) {
    if (mainApp) mainApp.classList.remove('hidden');
    if (loginScreen) loginScreen.classList.add('hidden');
  }
}

// handleLogin is now on login.html — this is a fallback redirect
async function handleLogin() {
  window.location.href = 'login.html';
}

// No offline login — database authentication only
async function handleOfflineLogin(user, pass, err) {
  if (err) {
    err.classList.remove('hidden');
    err.textContent = 'Database connection required. Please ensure XAMPP MySQL is running.';
  }
}

function handleLogout() {
  showLogoutModal();
}

function showLogoutModal() {
  // Remove existing modal if any
  const existing = document.getElementById('logout-modal');
  if (existing) existing.remove();

  const modal = document.createElement('div');
  modal.id = 'logout-modal';
  modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);display:flex;align-items:center;justify-content:center;z-index:99999;animation:fadeIn 0.2s ease;';
  modal.innerHTML = `
    <div style="background:var(--bg-card, #1e293b);border:1px solid var(--glass-border, rgba(255,255,255,0.1));border-radius:20px;padding:40px;max-width:400px;width:90%;text-align:center;box-shadow:0 25px 60px rgba(0,0,0,0.5);">
      <i class="fa-solid fa-right-from-bracket" style="font-size:48px;color:var(--warning, #f59e0b);margin-bottom:20px;display:block;"></i>
      <h2 style="margin-bottom:12px;color:var(--text-main, #e2e8f0);font-size:22px;">Are you sure you want to log out?</h2>
      <p style="color:var(--text-muted, #94a3b8);margin-bottom:28px;font-size:15px;">Your session data will be saved locally.</p>
      <div style="display:flex;gap:12px;justify-content:center;">
        <button id="logout-no-btn" style="padding:14px 36px;border:1px solid var(--glass-border, rgba(255,255,255,0.1));background:rgba(255,255,255,0.05);color:var(--text-main, #e2e8f0);border-radius:12px;font-size:16px;font-weight:600;cursor:pointer;transition:all 0.2s;font-family:inherit;">No</button>
        <button id="logout-yes-btn" style="padding:14px 36px;border:none;background:var(--danger, #ef4444);color:white;border-radius:12px;font-size:16px;font-weight:600;cursor:pointer;transition:all 0.2s;font-family:inherit;">Yes, Log Out</button>
      </div>
    </div>
  `;
  document.body.appendChild(modal);

  // Click outside to dismiss
  modal.addEventListener('click', (e) => {
    if (e.target === modal) modal.remove();
  });

  document.getElementById('logout-no-btn').addEventListener('click', () => modal.remove());
  document.getElementById('logout-yes-btn').addEventListener('click', () => {
    state.isLoggedIn = false;
    state.currentUser = null;
    state.currentRole = 'teacher';
    saveState();
    try {
      fetch('auth.php?action=logout', { method: 'POST', credentials: 'same-origin' }).catch(() => {});
    } catch(e) {}
    const loginUser = document.getElementById('login-user');
    const loginPass = document.getElementById('login-pass');
    if (loginUser) loginUser.value = '';
    if (loginPass) loginPass.value = '';
    modal.remove();
    window.location.href = 'login.html';
  });

  // Escape key to dismiss
  const escHandler = (e) => {
    if (e.key === 'Escape') { modal.remove(); document.removeEventListener('keydown', escHandler); }
  };
  document.addEventListener('keydown', escHandler);
}

// --- Cloud Database Integration ---
async function syncToCloud() {
  try {
    // 1. Prepare basic state sync
    const payload = { state };

    // 2. Fetch multimedia images from IndexedDB to sync to MySQL
    // We only sync 'pictures' category to keep it lightweight for low-end hardware
    try {
      if (typeof getUploadedMediaByCategory === 'function') {
        const pics = await getUploadedMediaByCategory('pictures');
        if (pics && pics.length > 0) {
          const mediaToSync = [];
          for (const pic of pics) {
            const fileData = await getUploadedFile(pic.id);
            if (fileData && fileData.blob) {
              const base64 = await blobToBase64(fileData.blob);
              mediaToSync.push({
                id: pic.id,
                category: 'pictures',
                name: pic.name,
                type: pic.type,
                size: pic.size,
                data: base64
              });
            }
          }
          payload.multimedia = mediaToSync;
        }
      }
    } catch (err) {
      console.warn("Multimedia sync skipped or failed:", err);
    }

    const response = await fetch('sync.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await response.json();
    if(result.status === 'success') {
      alert("✅ Sync Successful! All data and images are now saved in the database.");
    } else {
      alert('❌ Sync Error: ' + result.message);
    }
  } catch(e) {
    alert('⚠️ Connection Failed: Make sure XAMPP (Apache/MySQL) is running.');
  }
}

function blobToBase64(blob) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onloadend = () => resolve(reader.result);
    reader.onerror = reject;
    reader.readAsDataURL(blob);
  });
}

async function restoreFromCloud() {
  if(!confirm('This will overwrite your local offline data with the cloud database. Continue?')) return;
  try {
    const response = await fetch('sync.php', { method: 'GET' });
    const result = await response.json();
    if(result.status === 'success' && result.state) {
      state = { ...defaultState, ...result.state };
      saveState();
      alert('Data restored successfully!');
      location.reload(); // Refresh to apply restored state across all views
    } else {
      alert('Error: ' + result.message);
    }
  } catch(e) {
    alert('Failed to connect to the cloud database. Is XAMPP running?');
  }
}

// Auto-sync students from MySQL on every page load
// DB is source of truth — merges DB students into state.students
async function syncStudentsFromDB() {
  try {
    const response = await fetch('student_api.php', { method: 'GET' });
    const data = await response.json();
    if (data.status === 'success' && Array.isArray(data.students)) {
      const dbStudents = data.students;
      const stateById = {};
      state.students.forEach(s => { stateById[s.id] = s; });

      // Merge: DB students take priority for core fields
      const merged = [];
      const seenIds = new Set();

      dbStudents.forEach(dbS => {
        const id = parseInt(dbS.id) || dbS.id;
        seenIds.add(id);
        const localS = stateById[id];
        merged.push({
          id: id,
          name: dbS.name || (localS && localS.name) || 'Unknown',
          grade: String(dbS.grade || (localS && localS.grade) || '4'),
          section: dbS.section || (localS && localS.section) || 'A',
          recitations: parseInt(dbS.recitations) || (localS && localS.recitations) || 0,
          totalScore: parseInt(dbS.totalScore) || (localS && localS.totalScore) || 0,
          photo: dbS.photo || (localS && localS.photo) || null
        });
      });

      // Keep any localStorage-only students (genuine offline additions not yet pushed)
      // Never re-add deleted dummy students or outdated Grade 4/6 students not in DB
      state.students.forEach(s => {
        if (!seenIds.has(s.id) && s.id > 1000000000000 && s.grade !== '4' && s.grade !== '6') {
          merged.push(s);
        }
      });

      state.students = merged;
      saveState();
      console.log(`✅ Synced ${dbStudents.length} students from DB, ${merged.length} total in state`);
    }
  } catch (e) {
    console.warn('⚠️ Could not sync students from DB (offline?):', e.message);
  }
}

// Navigation
function initNavigation() {
  document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', (e) => {
      const targetView = e.currentTarget.getAttribute('data-view');
      switchView(targetView);
    });
  });
}

function switchView(viewId) {
  document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
  const navItem = document.querySelector(`.nav-item[data-view="${viewId}"]`);
  if (navItem) navItem.classList.add('active');

  document.querySelectorAll('.view-section').forEach(s => s.classList.remove('active'));
  const viewSection = document.getElementById(`view-${viewId}`);
  if (viewSection) viewSection.classList.add('active');

  state.currentView = viewId;
  saveState();

  if(viewId === 'records') renderRecords();
  if(viewId === 'students') renderStudents();
  if(viewId === 'assessment') {
    updateAssessmentTopics();
    renderAssessmentStudents();
  }
  if(viewId === 'materials') {
    updateMaterialTopics();
    renderQuestionBank();
  }
  if(viewId === 'scoreboard') {
    renderScoreboard();
  }
  if(viewId === 'lessons') {
    updateLessonTopics();
  }
  if(viewId === 'admin') {
    renderAdminUsers();
    loadSystemSettings();
    renderQuestionBank();
  }
}

// Service Worker (Offline Support)
function registerServiceWorker() {
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('pwabuilder-adv-sw.js').catch(console.error);
  }
}

// --- Question Bank Manager (Database Driven) ---
let dbQuestions = []; // Cache from database

async function fetchQuestionsFromDB() {
  try {
    const res = await fetch('questions_api.php');
    const data = await res.json();
    if (data.status === 'success') {
      dbQuestions = data.questions;
      return dbQuestions;
    }
  } catch(e) { console.error("Error fetching questions:", e); }
  return [];
}

async function renderQuestionBank() {
  const adminList = document.getElementById('admin-question-list');
  const matList = document.getElementById('q-bank-list');
  
  if (!adminList && !matList) return;
  
  if (adminList) adminList.innerHTML = '<li>Loading questions from database...</li>';
  if (matList) matList.innerHTML = '<div>Loading questions from database...</div>';
  
  await fetchQuestionsFromDB();
  
  if (adminList) adminList.innerHTML = '';
  if (matList) matList.innerHTML = '';
  
  const filterGrade = document.getElementById('qb-filter-grade') ? document.getElementById('qb-filter-grade').value : 'all';
  const searchInput = document.getElementById('qb-search');
  const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
  
  const matGrade = document.getElementById('mat-grade') ? document.getElementById('mat-grade').value : null;
  const matTopic = document.getElementById('mat-topic') ? document.getElementById('mat-topic').value : null;
  
  dbQuestions.forEach((q) => {
    // Determine type display
    let typeDisplay = "Multiple Choice";
    if (q.type === 'multiple-choice') typeDisplay = "Multiple Choice";
    else if (q.type === 'identification') typeDisplay = "Identification";
    else if (q.type === 'open-ended') typeDisplay = "Open Ended";
    else if (q.text.includes("____") || q.text.toLowerCase().includes("identify")) typeDisplay = "Identification";
    else if (q.text.toLowerCase().includes("enumerate") || q.text.toLowerCase().includes("list")) typeDisplay = "Enumeration";

    // For Admin View
    if (adminList) {
      if (filterGrade !== 'all' && q.grade !== filterGrade) return;
      if (searchTerm && !q.topic.toLowerCase().includes(searchTerm) && !q.text.toLowerCase().includes(searchTerm)) return;
      
      const li = document.createElement('li');
      li.className = 'glass-card mb-10';
      li.style.padding = '15px';
      li.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:start;">
          <div style="flex:1">
            <span class="badge badge-primary" style="background:var(--primary); padding:2px 8px; border-radius:12px; font-size:12px;">Grade ${q.grade}</span> 
            <span class="badge badge-secondary" style="background:var(--secondary); padding:2px 8px; border-radius:12px; font-size:12px;">${q.difficulty}</span>
            <span class="badge" style="background:rgba(255,255,255,0.1); padding:2px 8px; border-radius:12px; font-size:12px;">${typeDisplay}</span>
            <div style="margin-top:8px; font-weight:600;">[${q.topic}]</div>
            <div style="margin-top:4px;">${q.text}</div>
          </div>
          <div style="display:flex; gap:8px;">
            <button class="btn btn-primary btn-sm" onclick="app.openEditQuestionModal(${q.id})" title="Edit Question"><i class="fa-solid fa-pen-to-square"></i></button>
            <button class="btn btn-danger btn-sm" onclick="app.deleteQuestion(${q.id})" title="Delete Question"><i class="fa-solid fa-trash"></i></button>
          </div>
        </div>
      `;
      adminList.appendChild(li);
    }
    
    // For Materials View
    if (matList) {
      if (matGrade && q.grade !== matGrade) return;
      if (matTopic && q.topic !== matTopic) return;
      
      const item = document.createElement('div');
      item.className = 'student-card glass-card mb-10';
      item.innerHTML = `
        <div style="flex:1">
          <span class="badge ${q.difficulty === 'Easy' ? 'bg-success' : q.difficulty === 'Medium' ? 'bg-warning' : 'bg-danger'}">${q.difficulty}</span>
          <span class="badge" style="background:rgba(255,255,255,0.1); padding:2px 8px; border-radius:12px; font-size:12px;">${typeDisplay}</span>
          <strong style="margin-left:8px;">Gr ${q.grade} - ${q.topic}</strong>
          <p style="margin-top:6px; font-size:14px; line-height:1.5;">${q.text}</p>
        </div>
        <div style="display:flex; gap:8px; align-items:flex-start; margin-left:12px;">
          <button class="btn btn-secondary btn-sm" onclick="app.openEditQuestionModal(${q.id})" title="Edit Question" style="padding:6px 12px; display:inline-flex; align-items:center; gap:6px;"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
          <button class="btn-remove" onclick="app.deleteQuestion(${q.id})" title="Delete Question"><i class="fa-solid fa-trash"></i></button>
        </div>
      `;
      matList.appendChild(item);
    }
  });
}

function escapeHtmlAttr(str) {
  if (!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function openEditQuestionModal(id) {
  const q = dbQuestions.find(x => Number(x.id) === Number(id));
  if (!q) return alert("Question not found in memory.");

  let modal = document.getElementById('edit-question-modal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'edit-question-modal';
    modal.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.75); display:flex; align-items:center; justify-content:center; z-index:99999; padding:16px; box-sizing:border-box;';
    document.body.appendChild(modal);
  }

  modal.innerHTML = `
    <div class="glass-card" style="width:100%; max-width:620px; max-height:90vh; overflow-y:auto; padding:28px; background:var(--bg-card, #1e293b); border:2px solid var(--primary); border-radius:16px; box-shadow:0 16px 40px rgba(0,0,0,0.6); position:relative;">
      <button onclick="app.closeEditQuestionModal()" class="btn-icon" style="position:absolute; right:16px; top:16px;" title="Close"><i class="fa-solid fa-xmark"></i></button>
      <h3 style="margin-top:0; margin-bottom:6px; display:flex; align-items:center; gap:8px;">
        <i class="fa-solid fa-pen-to-square text-primary"></i> Edit Question #` + q.id + `
      </h3>
      <p class="text-muted" style="font-size:13px; margin-bottom:18px;">Update question text, multiple-choice choices, difficulty, grade, or topic.</p>

      <input type="hidden" id="eq-id" value="${q.id}">

      <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:14px;">
        <div style="flex:1; min-width:120px;">
          <label style="font-size:13px; font-weight:600; display:block; margin-bottom:4px;">Grade Level</label>
          <select id="eq-grade" class="form-control">
            <option value="3" ${String(q.grade) === '3' ? 'selected' : ''}>Grade 3</option>
            <option value="4" ${String(q.grade) === '4' ? 'selected' : ''}>Grade 4</option>
            <option value="5" ${String(q.grade) === '5' ? 'selected' : ''}>Grade 5</option>
            <option value="6" ${String(q.grade) === '6' ? 'selected' : ''}>Grade 6</option>
          </select>
        </div>
        <div style="flex:2; min-width:180px;">
          <label style="font-size:13px; font-weight:600; display:block; margin-bottom:4px;">Topic</label>
          <input type="text" id="eq-topic" class="form-control" value="${escapeHtmlAttr(q.topic)}">
        </div>
      </div>

      <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:14px;">
        <div style="flex:1; min-width:140px;">
          <label style="font-size:13px; font-weight:600; display:block; margin-bottom:4px;">Difficulty</label>
          <select id="eq-difficulty" class="form-control">
            <option value="Easy" ${q.difficulty === 'Easy' ? 'selected' : ''}>Easy</option>
            <option value="Medium" ${q.difficulty === 'Medium' ? 'selected' : ''}>Medium</option>
            <option value="Hard" ${q.difficulty === 'Hard' ? 'selected' : ''}>Hard</option>
          </select>
        </div>
        <div style="flex:1; min-width:140px;">
          <label style="font-size:13px; font-weight:600; display:block; margin-bottom:4px;">Question Type</label>
          <select id="eq-type" class="form-control">
            <option value="multiple-choice" ${(q.type||'multiple-choice') === 'multiple-choice' ? 'selected' : ''}>Multiple Choice</option>
            <option value="identification" ${q.type === 'identification' ? 'selected' : ''}>Identification</option>
            <option value="open-ended" ${q.type === 'open-ended' ? 'selected' : ''}>Open Ended</option>
          </select>
        </div>
      </div>

      <div style="margin-bottom:18px;">
        <label style="font-size:13px; font-weight:600; display:block; margin-bottom:4px;">Question Text & Choices</label>
        <textarea id="eq-text" rows="5" class="form-control" style="font-family:inherit; line-height:1.5;">${escapeHtmlAttr(q.text)}</textarea>
        <small class="text-muted" style="display:block; margin-top:4px; font-size:12px;">Tip: For Multiple Choice, format choices with pipes e.g. <code>Question text | A: Choice 1 B: Choice 2 C: Choice 3 D: Choice 4</code></small>
      </div>

      <div style="display:flex; gap:10px; justify-content:flex-end;">
        <button class="btn btn-secondary" onclick="app.closeEditQuestionModal()"><i class="fa-solid fa-xmark"></i> Cancel</button>
        <button class="btn btn-success" onclick="app.saveEditedQuestion()"><i class="fa-solid fa-check"></i> Save Changes</button>
      </div>
    </div>
  `;

  modal.classList.remove('hidden');
  modal.style.display = 'flex';
}

function closeEditQuestionModal() {
  const modal = document.getElementById('edit-question-modal');
  if (modal) {
    modal.classList.add('hidden');
    modal.style.display = 'none';
  }
}

async function saveEditedQuestion() {
  const idEl = document.getElementById('eq-id');
  const gradeEl = document.getElementById('eq-grade');
  const topicEl = document.getElementById('eq-topic');
  const diffEl = document.getElementById('eq-difficulty');
  const typeEl = document.getElementById('eq-type');
  const textEl = document.getElementById('eq-text');

  if (!idEl || !textEl) return;

  const id = Number(idEl.value);
  const grade = gradeEl ? gradeEl.value : '4';
  const topic = topicEl ? topicEl.value.trim() : '';
  const difficulty = diffEl ? diffEl.value : 'Medium';
  const type = typeEl ? typeEl.value : 'multiple-choice';
  const text = textEl.value.trim();

  if (!text) return alert("Question text cannot be empty.");
  if (!topic) return alert("Topic cannot be empty.");

  try {
    const res = await fetch('questions_api.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, grade, topic, difficulty, type, text })
    });
    const data = await res.json();
    if (data.status === 'success') {
      const q = dbQuestions.find(x => Number(x.id) === id);
      if (q) {
        q.grade = grade;
        q.topic = topic;
        q.difficulty = difficulty;
        q.type = type;
        q.text = text;
      }
      closeEditQuestionModal();
      renderQuestionBank();
      alert("✅ Question updated successfully!");
    } else {
      alert("❌ Error: " + (data.message || 'Failed to update question.'));
    }
  } catch(e) {
    alert("Network or server error updating question: " + e.message);
  }
}

function editQuestion(id) {
  openEditQuestionModal(id);
}

async function deleteQuestion(id) {
  if (confirm("Are you sure you want to delete this question from the database?")) {
    await fetch('questions_api.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    });
    renderQuestionBank();
  }
}

async function exportQuestionsCSV() {
  await fetchQuestionsFromDB();
  if (dbQuestions.length === 0) return alert("No questions to export.");
  
  let csvContent = "data:text/csv;charset=utf-8,";
  csvContent += "ID,Grade,Topic,Difficulty,Type,Question\n";
  
  dbQuestions.forEach(q => {
    let typeDisplay = "Multiple Choice";
    if (q.text.includes("____") || q.text.toLowerCase().includes("identify")) typeDisplay = "Identification";
    if (q.text.toLowerCase().includes("enumerate") || q.text.toLowerCase().includes("list")) typeDisplay = "Enumeration";
    
    const safeText = q.text.replace(/"/g, '""');
    const safeTopic = q.topic.replace(/"/g, '""');
    
    csvContent += `"${q.id}","${q.grade}","${safeTopic}","${q.difficulty}","${typeDisplay}","${safeText}"\n`;
  });
  
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", "ILikeSci_Database_Questions.csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}


// --- Data Management ---
function exportData() {
  const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(state));
  const downloadAnchorNode = document.createElement('a');
  downloadAnchorNode.setAttribute("href",     dataStr);
  downloadAnchorNode.setAttribute("download", "ilikesci_backup_" + new Date().toISOString().split('T')[0] + ".json");
  document.body.appendChild(downloadAnchorNode);
  downloadAnchorNode.click();
  downloadAnchorNode.remove();
}

// --- Section Options & Filter Helpers ---
function getAvailableSections(grade) {
  const gStr = String(grade);
  const sections = new Set();
  (state.students || []).forEach(s => {
    if (gStr === 'all' || String(s.grade) === gStr) {
      if (s.section) sections.add(s.section);
    }
  });
  if (sections.size === 0) {
    if (gStr === '4') return ['Maagap', 'Magalang', 'Masigasig', 'Masikap', 'Matatag', 'Matiyaga'];
    if (gStr === '6') return ['Aristotle', 'Einstein', 'Faraday', 'Galilei', 'Newton', 'Pasteur', 'Tesla'];
    return ['A', 'B'];
  }
  return Array.from(sections).sort();
}

function updateSectionDropdown(gradeSelectIdOrVal, sectionSelectId, includeAllOption = true) {
  const sectionSelect = document.getElementById(sectionSelectId);
  if (!sectionSelect) return;

  let gradeVal = 'all';
  if (typeof gradeSelectIdOrVal === 'string') {
    const gradeEl = document.getElementById(gradeSelectIdOrVal);
    gradeVal = gradeEl ? gradeEl.value : gradeSelectIdOrVal;
  }

  const currentVal = sectionSelect.value;
  const sections = getAvailableSections(gradeVal);

  const currentOptions = Array.from(sectionSelect.options).map(o => o.value);
  const targetOptions = includeAllOption ? ['all', ...sections] : sections;
  const matches = currentOptions.length === targetOptions.length && currentOptions.every((v, i) => v === targetOptions[i]);

  if (!matches) {
    sectionSelect.innerHTML = '';
    if (includeAllOption) {
      const allOpt = document.createElement('option');
      allOpt.value = 'all';
      allOpt.textContent = 'All Sections';
      sectionSelect.appendChild(allOpt);
    }

    sections.forEach(sec => {
      const opt = document.createElement('option');
      opt.value = sec;
      opt.textContent = sec.length === 1 ? `Section ${sec}` : sec;
      sectionSelect.appendChild(opt);
    });

    if (currentVal && (currentVal === 'all' || sections.includes(currentVal))) {
      sectionSelect.value = currentVal;
    } else {
      sectionSelect.value = includeAllOption ? 'all' : (sections[0] || 'A');
    }
  }
}

// --- Students Management ---
function renderStudents() {
  const container = document.getElementById('students-container');
  const gradeFilter = document.getElementById('grade-filter');
  if(!container || !gradeFilter) return;
  const filter = gradeFilter.value;
  updateSectionDropdown('grade-filter', 'section-filter', true);
  const sectionFilter = document.getElementById('section-filter');
  const secFilter = sectionFilter ? sectionFilter.value : 'all';
  const sortEl = document.getElementById('student-sort');
  const sortField = sortEl ? sortEl.value : 'name';
  const ascending = typeof window.getStudentSortDir === 'function' ? window.getStudentSortDir() : true;
  const searchEl = document.getElementById('student-search');
  const searchQ = searchEl ? searchEl.value.toLowerCase().trim() : '';
  
  container.innerHTML = '';
  
  let filtered = filter === 'all' 
    ? [...state.students]
    : state.students.filter(s => s.grade === filter);

  if (secFilter !== 'all') {
    filtered = filtered.filter(s => (s.section || 'A') === secFilter);
  }

  if (searchQ) {
    filtered = filtered.filter(s => s.name.toLowerCase().includes(searchQ));
  }

  // Sort
  filtered.sort((a, b) => {
    let cmp = 0;
    switch (sortField) {
      case 'name': cmp = a.name.localeCompare(b.name); break;
      case 'grade': cmp = (parseInt(a.grade)||0) - (parseInt(b.grade)||0); break;
      case 'points': cmp = (a.totalScore||0) - (b.totalScore||0); break;
      case 'recitations': cmp = (a.recitations||0) - (b.recitations||0); break;
      default: cmp = a.name.localeCompare(b.name); break;
    }
    return ascending ? cmp : -cmp;
  });

  filtered.forEach(student => {
    const initials = student.name.split(' ').map(n => n[0]).join('').toUpperCase();
    const photoHTML = student.photo 
      ? `<img src="${student.photo}" class="avatar" style="object-fit: cover; cursor:pointer;" onclick="app.openStudentPhotoModal(${student.id})" title="Click to view or change photo">`
      : `<div class="avatar" style="cursor:pointer;" onclick="app.openStudentPhotoModal(${student.id})" title="Click to view or add photo">${initials}</div>`;
    const section = student.section || 'A';
    const div = document.createElement('div');
    div.className = 'glass-card student-card';
    div.innerHTML = `
      <div class="student-card-info">
        ${photoHTML}
        <div>
          <strong>${student.name}</strong><br>
          <small class="text-muted">Grade ${student.grade} — Section ${section} | ${student.totalScore||0} pts | ${student.recitations||0} recitations</small>
        </div>
      </div>
      <div style="display:flex; gap:8px;">
        <button class="btn-icon" onclick="app.openStudentPhotoModal(${student.id})" title="Student Photo (View / Upload / Camera)"><i class="fa-solid fa-camera"></i></button>
        <button class="btn-remove" onclick="app.removeStudent(${student.id})"><i class="fa-solid fa-trash"></i></button>
      </div>
    `;
    container.appendChild(div);
  });
}

function toggleAddStudent() {
  const form = document.getElementById('add-student-form');
  if (!form) return;
  form.classList.toggle('hidden');
  if (!form.classList.contains('hidden')) {
    updateSectionDropdown('student-grade', 'student-section', false);
  }
}

function addStudent() {
  const name = document.getElementById('student-name').value.trim();
  const grade = document.getElementById('student-grade').value;
  const sectionEl = document.getElementById('student-section');
  const section = sectionEl ? sectionEl.value : 'A';

  if (!name) return alert("Please enter the student's full name.");
  if (name.length < 2) return alert("Student name is too short.");

  const newId = Date.now();
  const newStudent = { id: newId, name, grade, section, recitations: 0, totalScore: 0, photo: null };
  state.students.push(newStudent);

  saveState();

  // Persist to database immediately
  fetch('student_api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(newStudent)
  }).catch(e => console.warn('Student DB save failed (offline?):', e));

  document.getElementById('student-name').value = '';
  renderStudents();
  toggleAddStudent();
  alert(`${name} added to Grade ${grade} Section ${section}!`);
}

function removeStudent(id) {
  if(confirm('Remove this student?')) {
    state.students = state.students.filter(s => s.id !== id);
    saveState();

    // Delete from database immediately
    fetch('student_api.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    }).catch(e => console.warn('Student DB delete failed (offline?):', e));

    renderStudents();
  }
}

async function bulkAddStudents(namesText, grade, section) {
  if (!namesText || !namesText.trim()) return alert('Please enter student names, one per line.');
  const lines = namesText.split('\n').map(l => l.trim()).filter(l => l.length > 0);
  if (lines.length === 0) return alert('No valid names found.');

  let addedCount = 0;
  for (const name of lines) {
    const newId = Date.now() + Math.floor(Math.random() * 1000);
    const newStudent = { id: newId, name, grade: String(grade), section: String(section), recitations: 0, totalScore: 0, photo: null };
    state.students.push(newStudent);
    addedCount++;

    // Persist to database
    fetch('student_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(newStudent)
    }).catch(e => console.warn('Student DB save failed (offline?):', e));
  }

  saveState();
  renderStudents();
  playChime('coin');
  alert(`✅ Successfully imported ${addedCount} students into Grade ${grade} Section ${section}!`);
}

function resetQuarterRecitations(grade) {
  const gStr = String(grade);
  const targetLabel = gStr === 'all' ? 'ALL Grades' : `Grade ${gStr}`;
  if (!confirm(`Are you sure you want to reset recitation scores for ${targetLabel}? This is typically done at the start of a new term. (Student profiles will NOT be deleted)`)) {
    return;
  }

  state.students.forEach(s => {
    if (gStr === 'all' || s.grade === gStr) {
      s.recitations = 0;
      s.totalScore = 0;
      fetch('student_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(s)
      }).catch(e => {});
    }
  });

  saveState();
  if (typeof renderStudents === 'function') renderStudents();
  if (typeof renderScoreboard === 'function') renderScoreboard();
  if (typeof renderRecords === 'function') renderRecords();
  playChime('correct');
  alert(`✅ Recitation scores reset successfully for ${targetLabel}! Ready for new term.`);
}

// --- Materials Management (DB-driven topics) ---
async function fetchAllTopicsForGrade(grade) {
  const topicsSet = new Set();
  const targetGradeStr = String(grade || '4').trim();
  const targetNormGrade = targetGradeStr.replace(/\D/g, '');

  // Helper matcher for grades
  const isGradeMatch = (g) => {
    if (!g || g === '') return true;
    const strG = String(g).trim();
    if (strG === targetGradeStr) return true;
    const normG = strG.replace(/\D/g, '');
    return normG !== '' && normG === targetNormGrade;
  };

  // 1. From dbQuestions
  try {
    await fetchQuestionsFromDB();
    if (Array.isArray(dbQuestions)) {
      dbQuestions
        .filter(q => isGradeMatch(q.grade) && q.topic && q.topic.trim())
        .forEach(q => topicsSet.add(q.topic.trim()));
    }
  } catch(e) {}

  // 2. From curriculum_lessons (includes PPTX imports & curriculum guides)
  try {
    const lessons = await fetchCurriculumLessons(grade);
    if (Array.isArray(lessons)) {
      lessons
        .filter(l => isGradeMatch(l.grade) && l.topic && l.topic.trim())
        .forEach(l => topicsSet.add(l.topic.trim()));
    }
  } catch(e) {}

  // 3. From pptx_uploads list
  try {
    const res = await fetch('pptx_api.php?action=list');
    const data = await res.json();
    if (data.status === 'success' && Array.isArray(data.uploads)) {
      data.uploads
        .filter(u => isGradeMatch(u.grade))
        .forEach(u => {
          const t = (u.topic && u.topic.trim()) ? u.topic.trim() : (u.original_name ? u.original_name.replace(/\.(pptx?|ppt)$/i, '').replace(/[_-]/g, ' ').trim() : '');
          if (t) topicsSet.add(t);
        });
    }
  } catch(e) {}

  // 4. From local state topics registry
  try {
    if (state && state.topics) {
      Object.keys(state.topics).forEach(gKey => {
        if (isGradeMatch(gKey) && Array.isArray(state.topics[gKey])) {
          state.topics[gKey].forEach(t => { if (t && t.trim()) topicsSet.add(t.trim()); });
        }
      });
    }
  } catch(e) {}

  return Array.from(topicsSet).sort();
}

async function refreshAllTopicSelectors() {
  if (typeof updateMaterialTopics === 'function') await updateMaterialTopics();
  if (typeof updateAssessmentTopics === 'function') await updateAssessmentTopics();
  if (typeof updateLessonTopics === 'function') await updateLessonTopics();
  if (typeof updateDashboardTopics === 'function') await updateDashboardTopics();
}

async function updateMaterialTopics() {
  const topicSelect = document.getElementById('mat-topic');
  const gradeSelect = document.getElementById('mat-grade');
  if(!topicSelect || !gradeSelect) return;
  const grade = gradeSelect.value;
  topicSelect.innerHTML = '';

  const topics = await fetchAllTopicsForGrade(grade);

  if (topics.length === 0) {
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = '-- No topics available --';
    topicSelect.appendChild(opt);
  } else {
    topics.forEach(topic => {
      const opt = document.createElement('option');
      opt.value = topic;
      opt.textContent = topic;
      topicSelect.appendChild(opt);
    });
  }
}

async function promptNewTopic() {
  const grade = document.getElementById('mat-grade').value;
  const topic = prompt(`Enter new Science topic for Grade ${grade}:`);

  if (!topic) return;
  const trimmedTopic = topic.trim();
  if (trimmedTopic === "") return alert("Topic name cannot be empty.");

  // Check if topic already exists in DB questions
  await fetchQuestionsFromDB();
  const existingTopics = [...new Set(dbQuestions.filter(q => q.grade === grade).map(q => q.topic))];
  if (existingTopics.includes(trimmedTopic)) {
    return alert("This topic already exists for this grade.");
  }

  // Add a placeholder question to create the topic in DB
  await fetch('questions_api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ grade, topic: trimmedTopic, difficulty: 'Easy', text: `[Placeholder] New topic: ${trimmedTopic}` })
  });

  await updateMaterialTopics();
  alert(`Topic "${trimmedTopic}" added successfully!`);
}

async function addMaterialQuestion() {
  const grade = document.getElementById('mat-grade').value;
  const topic = document.getElementById('mat-topic').value;
  const text = document.getElementById('mat-q-text').value.trim();
  const difficulty = document.getElementById('mat-diff').value;
  const typeSelect = document.getElementById('mat-type');
  const type = typeSelect ? typeSelect.value : 'multiple-choice';

  if (!topic) return alert("Please select or add a topic first.");
  if (!text) return alert("Please enter the question text.");

  try {
    await fetch('questions_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ grade, topic, difficulty, text, type })
    });
    document.getElementById('mat-q-text').value = '';
    alert("Question added to database bank!");
    // If Admin view is active, update the list
    if (document.getElementById('admin-question-list') || document.getElementById('q-bank-list')) renderQuestionBank();
  } catch(e) { console.error("Error adding question:", e); }
}

// --- File Import: Parse Questions from User Files ---
let parsedImportQuestions = [];

function handleImportFile(file) {
  if (!file) return;
  const ext = file.name.split('.').pop().toLowerCase();
  const reader = new FileReader();

  reader.onload = function(e) {
    const content = e.target.result;
    let questions = [];

    if (ext === 'json') {
      questions = parseJSONQuestions(content, file.name);
    } else if (ext === 'csv') {
      questions = parseCSVQuestions(content, file.name);
    } else if (ext === 'txt') {
      questions = parseTXTQuestions(content, file.name);
    } else {
      return alert('Unsupported file type. Use .txt, .csv, or .json');
    }

    const mcOnly = document.getElementById('import-mc-only')?.checked;
    if (mcOnly) {
      questions = questions.filter(q => q.type === 'multiple-choice');
    }

    if (questions.length === 0) {
      return alert('No questions found in this file. Make sure the file contains numbered questions (1. 2. 3. etc.) or structured data.');
    }

    parsedImportQuestions = questions;
    renderImportPreview(questions, file.name);
  };

  reader.readAsText(file);
}

function parseTXTQuestions(text, filename) {
  const questions = [];
  const lines = text.split('\n').map(l => l.replace(/\r/g, '').trim());
  const grade = extractGradeFromText(lines, filename);
  const topic = extractTopicFromText(lines, filename);
  const defaultDiff = document.getElementById('import-diff')?.value || 'Medium';

  let i = 0;
  while (i < lines.length) {
    const line = lines[i];

    // --- Pattern 1: Numbered multiple-choice question ---
    // e.g., "1. Which of the following inventions has greatly changed..."
    //        "a. laptop   b. television   c. cell phone     d. projector"
    const mcMatch = line.match(/^(\d+)\.\s+(.{15,})/);
    if (mcMatch && !isBoilerplate(line)) {
      const qNum = mcMatch[1];
      let qText = mcMatch[2].trim();
      let options = [];
      let j = i + 1;

      // Collect continuation lines (question text spanning multiple lines)
      while (j < lines.length && lines[j] && !lines[j].match(/^[a-dA-D][\.\)]\s/) && !lines[j].match(/^\d+\.\s/) && !isBoilerplate(lines[j])) {
        qText += ' ' + lines[j].trim();
        j++;
      }

      // Collect options (a. b. c. d.)
      // Pattern A: Options on separate lines
      while (j < lines.length && lines[j].match(/^[a-dA-D][\.\)]\s/)) {
        const optMatch = lines[j].match(/^[a-dA-D][\.\)]\s+(.*)/);
        if (optMatch) options.push(optMatch[1].trim());
        j++;
      }

      // Pattern B: Options on same line as question (a. xxx  b. xxx  c. xxx  d. xxx)
      if (options.length === 0) {
        const inlineOpts = qText.match(/\b[aA][\.\)]\s+.+?\s+[bB][\.\)]\s+.+?\s+[cC][\.\)]\s+.+?\s+[dD][\.\)]\s+.+/);
        if (inlineOpts) {
          const optStr = inlineOpts[0];
          qText = qText.replace(optStr, '').trim();
          const optParts = optStr.split(/\s+[a-dA-D][\.\)]\s+/).filter(Boolean);
          // Re-split properly
          const optMatches = optStr.match(/[a-dA-D][\.\)]\s+[^a-dA-D\.\)]+/g);
          if (optMatches) {
            optMatches.forEach(o => {
              options.push(o.replace(/^[a-dA-D][\.\)]\s+/, '').trim());
            });
          }
        }
      }

      // Clean up question text
      qText = qText.replace(/\s+/g, ' ').trim();
      if (qText.length < 10) { i = j || i + 1; continue; }

      const optionsStr = options.length > 0 ? '\nOptions: ' + options.join(' | ') : '';
      const type = options.length >= 3 ? 'multiple-choice' : 'identification';

      questions.push({
        text: qText + optionsStr,
        grade: grade,
        topic: topic,
        difficulty: guessDifficulty(qText, defaultDiff),
        type: type,
        selected: true,
        source: filename
      });

      i = j || i + 1;
      continue;
    }

    // --- Pattern 2: Guide/Discussion Questions ---
    // e.g., "What feelings came about when you were reading about his accomplishments?"
    const guideMatch = line.match(/^(?:GUIDE\s+)?(?:Discussion\s+)?Question[s]?[:\s]/i);
    if (guideMatch) {
      // Scan next lines for numbered sub-questions
      let k = i + 1;
      while (k < lines.length && k < i + 30) {
        const subQ = lines[k].match(/^(\d+)\.\s+(.{15,})/);
        if (subQ && !isBoilerplate(lines[k])) {
          let subText = subQ[2].trim();
          let m = k + 1;
          while (m < lines.length && lines[m] && !lines[m].match(/^\d+\.\s/) && !lines[m].match(/^_/) && !isBoilerplate(lines[m])) {
            subText += ' ' + lines[m].trim();
            m++;
          }
          subText = subText.replace(/\s+/g, ' ').trim();
          if (subText.length >= 15 && subText.includes('?')) {
            questions.push({
              text: subText,
              grade: grade,
              topic: topic,
              difficulty: 'Easy',
              type: 'open-ended',
              selected: true,
              source: filename
            });
          }
          k = m;
        } else {
          k++;
        }
      }
      i = k;
      continue;
    }

    // --- Pattern 3: Standalone question with "?" or inquiry question followed by options ---
    if ((line.endsWith('?') || line.match(/^(?:Which|What|Why|How|Where|Who)\b/i)) && line.length > 15 && !isBoilerplate(line) && !line.match(/^\d+\./)) {
      const cleanQ = line.replace(/^[\s•\-\*]+/, '').trim();
      let options = [];
      let j = i + 1;

      // Collect following options (A. B. C. D.)
      while (j < lines.length && lines[j] && lines[j].match(/^[a-dA-D][\.\)]\s/)) {
        const optMatch = lines[j].match(/^[a-dA-D][\.\)]\s+(.*)/);
        if (optMatch) options.push(optMatch[1].trim());
        j++;
      }

      if (options.length >= 2) {
        const optionsStr = '\nOptions: ' + options.join(' | ');
        questions.push({
          text: cleanQ + optionsStr,
          grade: grade,
          topic: topic,
          difficulty: guessDifficulty(cleanQ, defaultDiff),
          type: 'multiple-choice',
          selected: true,
          source: filename
        });
        i = j;
        continue;
      } else if (cleanQ.endsWith('?') && cleanQ.length > 20) {
        questions.push({
          text: cleanQ,
          grade: grade,
          topic: topic,
          difficulty: defaultDiff,
          type: 'open-ended',
          selected: true,
          source: filename
        });
      }
    }

    i++;
  }

  return questions;
}

function parseJSONQuestions(content, filename) {
  try {
    const data = JSON.parse(content);
    const arr = Array.isArray(data) ? data : (data.questions || []);
    const defaultDiff = document.getElementById('import-diff')?.value || 'Medium';
    const grade = document.getElementById('mat-grade')?.value || '4';
    const topic = document.getElementById('mat-topic')?.value || 'Imported';

    return arr.map(q => ({
      text: q.text || q.question || q.question_text || '',
      grade: q.grade || grade,
      topic: q.topic || topic,
      difficulty: q.difficulty || defaultDiff,
      type: q.type || 'imported',
      selected: true,
      source: filename
    })).filter(q => q.text.length > 5);
  } catch(e) {
    alert('Invalid JSON format.');
    return [];
  }
}

function parseCSVQuestions(content, filename) {
  const lines = content.split('\n').filter(l => l.trim());
  if (lines.length < 2) return [];
  const defaultDiff = document.getElementById('import-diff')?.value || 'Medium';
  const grade = document.getElementById('mat-grade')?.value || '4';
  const topic = document.getElementById('mat-topic')?.value || 'Imported';

  const headers = lines[0].split(',').map(h => h.trim().toLowerCase());
  const textIdx = headers.findIndex(h => ['text','question','question_text','q'].includes(h));
  const gradeIdx = headers.findIndex(h => h === 'grade');
  const topicIdx = headers.findIndex(h => h === 'topic');
  const diffIdx = headers.findIndex(h => ['difficulty','diff'].includes(h));

  if (textIdx === -1) {
    alert('CSV must have a column named "text", "question", or "question_text".');
    return [];
  }

  return lines.slice(1).map(line => {
    const cols = parseCSVLine(line);
    return {
      text: (cols[textIdx] || '').trim(),
      grade: gradeIdx >= 0 ? (cols[gradeIdx] || grade) : grade,
      topic: topicIdx >= 0 ? (cols[topicIdx] || topic) : topic,
      difficulty: diffIdx >= 0 ? (cols[diffIdx] || defaultDiff) : defaultDiff,
      type: 'csv-import',
      selected: true,
      source: filename
    };
  }).filter(q => q.text.length > 5);
}

function parseCSVLine(line) {
  const result = [];
  let current = '';
  let inQuotes = false;
  for (let i = 0; i < line.length; i++) {
    const ch = line[i];
    if (ch === '"') { inQuotes = !inQuotes; }
    else if (ch === ',' && !inQuotes) { result.push(current); current = ''; }
    else { current += ch; }
  }
  result.push(current);
  return result;
}

// Helper: Detect grade from file content
function extractGradeFromText(lines, filename) {
  // Check filename first (e.g., Science4, Science_4, Grade4)
  const fnMatch = filename.match(/(?:Science|Grade)\s*(\d)/i);
  if (fnMatch) return fnMatch[1];
  // Check first 30 lines for "Grade X" or "Science X"
  for (let i = 0; i < Math.min(30, lines.length); i++) {
    const m = lines[i].match(/(?:Science|Grade)\s+(\d)/i);
    if (m) return m[1];
  }
  return document.getElementById('mat-grade')?.value || '4';
}

// Helper: Detect topic from file content
function extractTopicFromText(lines, filename) {
  // Check for "Lesson Title/Topic:" pattern in DepEd files
  for (let i = 0; i < Math.min(60, lines.length); i++) {
    const m = lines[i].match(/Lesson\s+Title\s*\/?\s*Topic\s*:\s*(.+)/i);
    if (m) {
      let topic = m[1].trim();
      // Sometimes the topic continues on the next line
      if (topic.length < 10 && i + 1 < lines.length) {
        topic += ' ' + lines[i + 1].trim();
      }
      return topic.replace(/\s+/g, ' ').trim();
    }
  }
  return document.getElementById('mat-topic')?.value || 'Imported';
}

// Helper: Skip boilerplate lines
function isBoilerplate(line) {
  const bps = [
    'IMPLEMENTATION OF THE MATATAG',
    'LEARNING ACTIVITY SHEET',
    'Science 4 Quarter',
    'Science 3 Quarter',
    'Science 5 Quarter',
    'Science 6 Quarter',
    'Science 7 Quarter',
    'Science 4 Term',
    'Science 3 Term',
    'Science 5 Term',
    'Science 6 Term',
    'Science 7 Term',
    'Philippine Normal University',
    'Research Institute',
    'SiMERR',
    'This material is intended',
    'Borrowed content',
    'Development Team',
    'Management Team',
    'blr.od@deped.gov.ph',
    'NOTES TO TEACHERS',
    'ANSWER KEY',
    'ANNEX',
    '____'
  ];
  return bps.some(bp => line.includes(bp));
}

// Helper: Guess difficulty from question length/keywords
function guessDifficulty(text, fallback) {
  const hard = ['explain','analyze','compare','evaluate','describe the relationship','differentiate'];
  const easy = ['what is','name the','identify','which of the following','true or false','how many'];
  const lower = text.toLowerCase();
  if (hard.some(k => lower.includes(k))) return 'Hard';
  if (easy.some(k => lower.includes(k))) return 'Easy';
  return fallback;
}

function renderImportPreview(questions, filename) {
  const preview = document.getElementById('import-preview');
  const container = document.getElementById('import-table-container');
  const title = document.getElementById('import-preview-title');
  const status = document.getElementById('import-status');
  if (!preview || !container) return;

  title.innerHTML = `<i class="fa-solid fa-magnifying-glass"></i> ${questions.length} questions found in <strong>${filename}</strong>`;
  status.textContent = `All ${questions.length} selected for import. Review and uncheck any you don't want.`;

  let html = `<table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
    <thead><tr style="background:rgba(67,97,238,0.1); position:sticky; top:0;">
      <th style="padding:8px; text-align:center; width:40px;">✓</th>
      <th style="padding:8px; text-align:left;">Question</th>
      <th style="padding:8px; width:60px;">Grade</th>
      <th style="padding:8px; width:140px;">Topic</th>
      <th style="padding:8px; width:80px;">Diff</th>
      <th style="padding:8px; width:90px;">Type</th>
    </tr></thead><tbody>`;

  questions.forEach((q, idx) => {
    const typeColor = q.type === 'multiple-choice' ? '#4361ee' : q.type === 'open-ended' ? '#06d6a0' : '#ffd166';
    html += `<tr style="border-bottom:1px solid rgba(0,0,0,0.06);">
      <td style="padding:8px; text-align:center;">
        <input type="checkbox" ${q.selected ? 'checked' : ''} onchange="app.toggleImportQ(${idx})">
      </td>
      <td style="padding:8px; max-width:400px; word-break:break-word;">${q.text.substring(0, 200)}${q.text.length > 200 ? '...' : ''}</td>
      <td style="padding:8px; text-align:center;">${q.grade}</td>
      <td style="padding:8px; font-size:0.8rem;">${q.topic.substring(0, 30)}</td>
      <td style="padding:8px;">
        <select onchange="app.setImportDiff(${idx}, this.value)" style="font-size:0.8rem; padding:2px 4px; border-radius:6px;">
          <option ${q.difficulty==='Easy'?'selected':''}>Easy</option>
          <option ${q.difficulty==='Medium'?'selected':''}>Medium</option>
          <option ${q.difficulty==='Hard'?'selected':''}>Hard</option>
        </select>
      </td>
      <td style="padding:8px;"><span style="color:${typeColor}; font-weight:600; font-size:0.75rem; text-transform:uppercase;">${q.type}</span></td>
    </tr>`;
  });

  html += '</tbody></table>';
  container.innerHTML = html;
  preview.classList.remove('hidden');
}

function toggleImportQ(idx) {
  if (parsedImportQuestions[idx]) parsedImportQuestions[idx].selected = !parsedImportQuestions[idx].selected;
  updateImportStatus();
}

function setImportDiff(idx, diff) {
  if (parsedImportQuestions[idx]) parsedImportQuestions[idx].difficulty = diff;
}

function toggleAllImport() {
  const allSelected = parsedImportQuestions.every(q => q.selected);
  parsedImportQuestions.forEach(q => q.selected = !allSelected);
  // Re-render checkboxes
  document.querySelectorAll('#import-table-container input[type="checkbox"]').forEach((cb, i) => {
    cb.checked = parsedImportQuestions[i]?.selected || false;
  });
  updateImportStatus();
}

function updateImportStatus() {
  const count = parsedImportQuestions.filter(q => q.selected).length;
  const status = document.getElementById('import-status');
  if (status) status.textContent = `${count} of ${parsedImportQuestions.length} selected for import.`;
}

async function importSelectedQuestions() {
  const selected = parsedImportQuestions.filter(q => q.selected);
  if (selected.length === 0) return alert('No questions selected for import.');

  const grade = document.getElementById('mat-grade')?.value || '4';
  const topic = document.getElementById('mat-topic')?.value;

  if (!confirm(`Import ${selected.length} questions to the database?`)) return;

  let success = 0;
  let failed = 0;

  for (const q of selected) {
    try {
      const res = await fetch('questions_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          grade: q.grade || grade,
          topic: q.topic || topic || 'Imported',
          difficulty: q.difficulty,
          text: q.text,
          type: q.type || 'multiple-choice'
        })
      });
      const data = await res.json();
      if (data.status === 'success') success++;
      else failed++;
    } catch(e) { failed++; }
  }

  alert(`✅ Imported ${success} questions to database.\n${failed > 0 ? `❌ ${failed} failed.` : ''}`);

  // Refresh question bank & topics
  await fetchQuestionsFromDB();
  if (typeof renderQuestionBank === 'function') renderQuestionBank();
  if (typeof refreshAllTopicSelectors === 'function') await refreshAllTopicSelectors();

  clearImportPreview();
}

function clearImportPreview() {
  parsedImportQuestions = [];
  const preview = document.getElementById('import-preview');
  if (preview) preview.classList.add('hidden');
  const fileInput = document.getElementById('import-q-file');
  if (fileInput) fileInput.value = '';
}

// --- Assessment & Flash Screen ---
function updateAssessmentGrade() {
  updateSectionDropdown('assess-grade', 'assess-section', true);
  updateAssessmentTopics();
  renderAssessmentStudents();
}

async function updateAssessmentTopics() {
  const gradeSelect = document.getElementById('assess-grade');
  const grade = gradeSelect ? gradeSelect.value : (state.assessment.grade || '4');
  state.assessment.grade = grade;

  updateSectionDropdown('assess-grade', 'assess-section', true);

  const topicSelect = document.getElementById('assess-topic');
  if(!topicSelect) return;
  topicSelect.innerHTML = '';

  const lessons = await fetchCurriculumLessons(grade);

  // Filter out any obsolete test or non-science lessons
  const cleanLessons = (lessons || []).filter(l => {
    if (!l.topic) return false;
    const t = l.topic.toLowerCase();
    if (t.includes('automated test') || t.includes('production verification') || t.includes('ap4') || t.startsWith('ap ')) return false;
    return true;
  });

  if(cleanLessons.length > 0) {
    // Sort lessons by quarter and lesson number
    cleanLessons.sort((a, b) => {
      const qA = parseInt(a.quarter) || 0;
      const qB = parseInt(b.quarter) || 0;
      if (qA !== qB) return qA - qB;
      const lA = parseInt(a.lesson_number) || 0;
      const lB = parseInt(b.lesson_number) || 0;
      return lA - lB;
    });

    // Deduplicate by topic name
    const seenTopics = new Set();
    cleanLessons.forEach(lesson => {
      const topicKey = lesson.topic.trim().toLowerCase();
      if (seenTopics.has(topicKey)) return;
      seenTopics.add(topicKey);

      const opt = document.createElement('option');
      opt.value = lesson.id;
      const qNum = lesson.quarter ? `Term ${lesson.quarter}` : '';
      const lNum = lesson.lesson_number ? ` L${lesson.lesson_number}` : '';
      opt.textContent = `${qNum}${lNum ? lNum + ': ' : ': '}${lesson.topic}`;
      opt.dataset.topic = lesson.topic;
      opt.dataset.lessonId = lesson.id;
      topicSelect.appendChild(opt);
    });
    state.assessment.useCurriculum = true;
  } else {
    // Fallback: load topics from questions DB
    await fetchQuestionsFromDB();
    const qTopics = [...new Set((dbQuestions || [])
      .filter(q => q.grade === grade && !q.topic.toLowerCase().includes('test') && !q.topic.toLowerCase().includes('ap4'))
      .map(q => q.topic))];
    qTopics.forEach(topic => {
      const opt = document.createElement('option');
      opt.value = topic;
      opt.textContent = topic;
      topicSelect.appendChild(opt);
    });
    state.assessment.useCurriculum = false;
  }

  renderAssessmentStudents();
}

function getAssessmentStudentPool() {
  const gradeEl = document.getElementById('assess-grade');
  const currentGrade = String(gradeEl ? gradeEl.value : (state.assessment.grade || '4'));
  state.assessment.grade = currentGrade;

  const sectionEl = document.getElementById('assess-section');
  const currentSection = sectionEl ? sectionEl.value : 'all';

  let pool = (state.students || []).filter(s => String(s.grade) === currentGrade);
  if (currentSection && currentSection !== 'all') {
    pool = pool.filter(s => (s.section || '').trim().toLowerCase() === currentSection.trim().toLowerCase());
  }
  return pool;
}

function renderAssessmentStudents() {
  const select = document.getElementById('assess-student');
  if(!select) return;
  select.innerHTML = '';
  
  const currentGrade = String(state.assessment.grade || document.getElementById('assess-grade')?.value || '4');
  const sectionEl = document.getElementById('assess-section');
  const currentSection = sectionEl ? sectionEl.value : 'all';
  
  const gradeStudents = getAssessmentStudentPool();
  
  const countBadge = document.getElementById('assess-student-count-badge');
  if (countBadge) {
    const secLabel = currentSection === 'all' ? 'All Sections' : `Sec. ${currentSection}`;
    countBadge.textContent = `${gradeStudents.length} Learners (${secLabel})`;
  }

  if(gradeStudents.length === 0) {
    select.innerHTML = '<option value="">No students in this selection</option>';
    state.assessment.studentId = null;
    return;
  }
  
  // Sort alphabetically by name
  gradeStudents.sort((a, b) => a.name.localeCompare(b.name));

  gradeStudents.forEach(s => {
    const opt = document.createElement('option');
    opt.value = String(s.id);
    const secTxt = s.section ? ` [${s.section}]` : '';
    opt.textContent = `${s.name}${secTxt} — ${s.recitations || 0} recs (${s.totalScore || 0} pts)`;
    select.appendChild(opt);
  });

  if (gradeStudents.length > 0) {
    const prevId = state.assessment.studentId;
    const stillPresent = gradeStudents.some(s => Number(s.id) === Number(prevId));
    if (stillPresent) {
      select.value = String(prevId);
    } else {
      select.value = String(gradeStudents[0].id);
      state.assessment.studentId = Number(gradeStudents[0].id);
    }
  }
}

// suggestStudent defined later in the file (single definition)

function setDifficulty(diff) {
  state.assessment.difficulty = diff || state.assessment.difficulty;
  document.querySelectorAll('.btn-diff').forEach(b => b.classList.remove('active'));
  const activeButton = document.querySelector(`.btn-diff[data-diff="${state.assessment.difficulty}"]`);
  if(activeButton) activeButton.classList.add('active');
}

function renderFlashChoices(rawText, correctAnswer) {
  const container = document.getElementById('flash-choices-container');
  if (!container) return;
  container.innerHTML = '';

  if (!rawText) return;

  // Extract choices portion
  let choicesStr = '';
  if (rawText.includes('|')) {
    const parts = rawText.split('|');
    choicesStr = parts.slice(1).join(' ').trim();
  } else {
    const firstChoiceIdx = rawText.search(/(?:^|[|\s]+)[A-D][:\.)\-]\s+/i);
    if (firstChoiceIdx !== -1) {
      choicesStr = rawText.substring(firstChoiceIdx).trim();
    }
  }

  if (!choicesStr) return;

  // Match each choice starting with [A-D] followed by : . ) or -
  const choiceRegex = /(?:^|[|\s]+)([A-D])[:.)\-]\s*(.*?)(?=(?:[|\s]+[A-D][:.)\-]\s*)|$)/gi;
  let match;
  const choices = [];
  while ((match = choiceRegex.exec(choicesStr)) !== null) {
    const letter = match[1].toUpperCase();
    const cleanText = match[2].replace(/[|\s]+$/, '').trim();
    if (cleanText) {
      choices.push({ letter, text: cleanText });
    }
  }

  if (choices.length === 0) return;

  let targetCorrect = 'A';
  if (correctAnswer && typeof correctAnswer === 'string') {
    targetCorrect = correctAnswer.trim().toUpperCase();
  }

  choices.forEach(c => {
    const card = document.createElement('div');
    card.className = 'choice-card';
    card.innerHTML = `<span class="choice-letter">${c.letter}</span> <span>${c.text}</span>`;
    
    card.onclick = () => {
      container.querySelectorAll('.choice-card').forEach(cc => cc.style.pointerEvents = 'none');
      const isRight = (c.letter === targetCorrect);
      if (isRight) {
        card.classList.add('correct');
        if (typeof playChime === 'function') playChime('correct');
        const pText = document.getElementById('participation-text');
        if (pText) pText.innerHTML = `<span style="color:#10b981;font-weight:bold;">✓ Correct Answer (${c.letter})! Award recitation points below.</span>`;
      } else {
        card.classList.add('wrong');
        container.querySelectorAll('.choice-card').forEach(cc => {
          if (cc.querySelector('.choice-letter')?.textContent.trim() === targetCorrect) {
            cc.classList.add('correct');
          }
        });
        if (typeof playChime === 'function') playChime('wrong');
        const pText = document.getElementById('participation-text');
        if (pText) pText.innerHTML = `<span style="color:#ef4444;font-weight:bold;">✗ Selected ${c.letter}. Correct was ${targetCorrect}.</span>`;
      }
    };
    container.appendChild(card);
  });
}

async function startQuizFlash() {
  const studentSelect = document.getElementById('assess-student');
  let studentId = studentSelect ? studentSelect.value : null;

  // Auto-select first student if none selected yet
  if ((!studentId || studentId === '') && studentSelect && studentSelect.options.length > 0) {
    for (let i = 0; i < studentSelect.options.length; i++) {
      if (studentSelect.options[i].value) {
        studentSelect.selectedIndex = i;
        studentId = studentSelect.options[i].value;
        break;
      }
    }
  }

  if (!studentId && typeof suggestStudent === 'function') {
    suggestStudent();
    studentId = studentSelect ? studentSelect.value : null;
  }

  if (!studentId) {
    return alert('Please add or select a student from the dropdown first!');
  }

  state.assessment.studentId = parseInt(studentId);
  const student = state.students.find(s => Number(s.id) === Number(state.assessment.studentId)) || { name: 'Student', id: studentId };

  const gradeSelect = document.getElementById('assess-grade');
  const grade = gradeSelect ? gradeSelect.value : (state.assessment.grade || '4');
  state.assessment.grade = grade;

  const topicSelect = document.getElementById('assess-topic');
  const selectedOption = (topicSelect && topicSelect.selectedIndex >= 0) ? topicSelect.options[topicSelect.selectedIndex] : null;
  const lessonId = selectedOption ? (selectedOption.dataset.lessonId || selectedOption.value) : null;
  const topicTitle = selectedOption ? (selectedOption.dataset.topic || selectedOption.textContent.replace(/^Q\d+\s+L\d+:\s*/i, '').trim()) : '';
  state.assessment.topic = topicTitle;

  // Question resolution pool
  let questionPool = [];

  // 1. Try to find questions from curriculum lesson
  if (lessonId) {
    try {
      const lessons = await fetchCurriculumLessons(grade);
      const lesson = lessons.find(l => l.id == lessonId || l.topic == topicTitle);
      if (lesson && Array.isArray(lesson.questions) && lesson.questions.length > 0) {
        questionPool = lesson.questions.map(q => {
          if (typeof q === 'object') return q;
          return { text: q, correct: 'A', difficulty: 'Medium' };
        });
      }
    } catch(e) {
      console.warn('Curriculum lesson questions fetch error:', e);
    }
  }

  // 2. Fallback to Question Bank (dbQuestions)
  if (questionPool.length === 0) {
    if (dbQuestions.length === 0) await fetchQuestionsFromDB();

    // Match by grade and topic
    const topicNorm = topicTitle.toLowerCase().trim();
    let topicMatches = dbQuestions.filter(q => 
      String(q.grade) === String(grade) && 
      (q.topic.toLowerCase().trim() === topicNorm || 
       topicNorm.includes(q.topic.toLowerCase().trim()) || 
       q.topic.toLowerCase().trim().includes(topicNorm))
    );

    // If no topic matches, fallback to all questions for this grade
    if (topicMatches.length === 0) {
      topicMatches = dbQuestions.filter(q => String(q.grade) === String(grade));
    }

    // If still empty, fallback to all questions across all grades
    if (topicMatches.length === 0) {
      topicMatches = [...dbQuestions];
    }

    questionPool = topicMatches;
  }

  if (questionPool.length === 0) {
    return alert('No questions found. Please add questions in the Materials tab or seed science questions!');
  }

  // 3. Filter by chosen difficulty, or fallback gracefully to any available difficulty in pool
  const chosenDiff = state.assessment.difficulty || 'Easy';
  let diffMatching = questionPool.filter(q => (q.difficulty || 'Medium').toLowerCase() === chosenDiff.toLowerCase());
  let chosenQ = null;

  if (diffMatching.length > 0) {
    chosenQ = diffMatching[Math.floor(Math.random() * diffMatching.length)];
  } else {
    // Graceful fallback to any question in the pool
    chosenQ = questionPool[Math.floor(Math.random() * questionPool.length)];
  }

  const rawText = (typeof chosenQ === 'object') ? (chosenQ.text || chosenQ.question || JSON.stringify(chosenQ)) : String(chosenQ);
  const correctAns = (typeof chosenQ === 'object') ? (chosenQ.correct || 'A') : 'A';
  const effectiveDiff = (typeof chosenQ === 'object' && chosenQ.difficulty) ? chosenQ.difficulty : chosenDiff;
  state.assessment.difficulty = effectiveDiff;
  state.assessment.activeQuestion = rawText;

  // 4. Update UI
  const pts = state.pointsMap[effectiveDiff] || 3;
  const diffBadge = document.getElementById('flash-diff');
  if (diffBadge) {
    diffBadge.textContent = `${effectiveDiff} (${pts} pts)`;
    diffBadge.className = `badge ${effectiveDiff === 'Easy' ? 'bg-success' : effectiveDiff === 'Medium' ? 'bg-warning' : 'bg-danger'}`;
  }

  const studentNameEl = document.getElementById('flash-student-name');
  if (studentNameEl) studentNameEl.textContent = student.name;

  let qStem = rawText;
  if (rawText.includes('|')) {
    qStem = rawText.split('|')[0].trim();
  } else {
    const firstChoiceIdx = rawText.search(/(?:^|[|\s]+)[A-D][:\.)\-]\s+/i);
    if (firstChoiceIdx !== -1) {
      qStem = rawText.substring(0, firstChoiceIdx).trim();
    }
  }
  const qTextEl = document.getElementById('flash-q-text');
  if (qTextEl) qTextEl.textContent = qStem;

  renderFlashChoices(rawText, correctAns);

  const ptsLabel = document.getElementById('pts-label');
  if (ptsLabel) ptsLabel.textContent = `(+${pts} pts)`;

  const pText = document.getElementById('participation-text');
  if (pText) pText.textContent = `Ready: Recitation for ${student.name}`;

  const flashScreen = document.getElementById('assessment-flash');
  if (flashScreen) {
    flashScreen.classList.remove('hidden');
    flashScreen.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  // Timer animation reset
  const timerBar = document.getElementById('flash-timer-bar');
  if (timerBar) {
    timerBar.style.animation = 'none';
    timerBar.offsetHeight; // Trigger reflow
    timerBar.style.animation = 'flashCountdown 20s linear forwards';
  }

  // Sync to TV screen if open
  showQuestionOnTV(qStem);
}

function cancelQuiz() {
  const flash = document.getElementById('assessment-flash');
  if(flash) flash.classList.add('hidden');
  const choicesContainer = document.getElementById('flash-choices-container');
  if(choicesContainer) choicesContainer.innerHTML = '';
  const pText = document.getElementById('participation-text');
  if(pText) pText.textContent = 'Ready';
}

function recordQuizResult(isCorrect) {
  const student = state.students.find(s => s.id === state.assessment.studentId);
  if(!student) return;
  student.recitations += 1;

  const points = isCorrect ? state.pointsMap[state.assessment.difficulty] : 0;
  student.totalScore += points;

  if (isCorrect) {
    playChime('correct');
  } else {
    playChime('wrong');
  }

  saveState();

  // Persist recitation record to database
  fetch('recitation_api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      student_id: student.id,
      topic: state.assessment.topic,
      difficulty: state.assessment.difficulty,
      points: points,
      is_correct: isCorrect ? 1 : 0
    })
  }).catch(e => console.warn('Recitation DB write failed (offline?):', e));

  alert(`Recorded! ${student.name} received ${points} points.`);
  cancelQuiz();
}

// --- Records & Proficiency (DepEd K-12 Grading System) ---

// DepEd Transmutation Table: Converts Initial Grade to Transmuted Grade
function transmute(initialGrade) {
  if (initialGrade >= 100) return 100;
  if (initialGrade >= 98.40) return 99;
  if (initialGrade >= 96.80) return 98;
  if (initialGrade >= 95.20) return 97;
  if (initialGrade >= 93.60) return 96;
  if (initialGrade >= 92.00) return 95;
  if (initialGrade >= 90.40) return 94;
  if (initialGrade >= 88.80) return 93;
  if (initialGrade >= 87.20) return 92;
  if (initialGrade >= 85.60) return 91;
  if (initialGrade >= 84.00) return 90;
  if (initialGrade >= 82.40) return 89;
  if (initialGrade >= 80.80) return 88;
  if (initialGrade >= 79.20) return 87;
  if (initialGrade >= 77.60) return 86;
  if (initialGrade >= 76.00) return 85;
  if (initialGrade >= 74.40) return 84;
  if (initialGrade >= 72.80) return 83;
  if (initialGrade >= 71.20) return 82;
  if (initialGrade >= 69.60) return 81;
  if (initialGrade >= 68.00) return 80;
  if (initialGrade >= 66.40) return 79;
  if (initialGrade >= 64.80) return 78;
  if (initialGrade >= 63.20) return 77;
  if (initialGrade >= 61.60) return 76;
  if (initialGrade >= 60.00) return 75;
  if (initialGrade >= 56.00) return 74;
  if (initialGrade >= 52.00) return 73;
  if (initialGrade >= 48.00) return 72;
  if (initialGrade >= 44.00) return 71;
  if (initialGrade >= 40.00) return 70;
  if (initialGrade >= 36.00) return 69;
  if (initialGrade >= 32.00) return 68;
  if (initialGrade >= 28.00) return 67;
  if (initialGrade >= 24.00) return 66;
  if (initialGrade >= 20.00) return 65;
  if (initialGrade >= 16.00) return 64;
  if (initialGrade >= 12.00) return 63;
  if (initialGrade >= 8.00) return 62;
  if (initialGrade >= 4.00) return 61;
  return 60;
}

// DepEd Proficiency Levels
function getDepEdLevel(transmutedGrade) {
  if (transmutedGrade >= 90) return { level: 'Outstanding', abbr: 'O', cssClass: 'deped-outstanding' };
  if (transmutedGrade >= 85) return { level: 'Very Satisfactory', abbr: 'VS', cssClass: 'deped-very-satisfactory' };
  if (transmutedGrade >= 80) return { level: 'Satisfactory', abbr: 'S', cssClass: 'deped-satisfactory' };
  if (transmutedGrade >= 75) return { level: 'Fairly Satisfactory', abbr: 'FS', cssClass: 'deped-fairly-satisfactory' };
  return { level: 'Did Not Meet Expectations', abbr: 'DNME', cssClass: 'deped-dnme' };
}

// DepEd Science Component Weights (Grades 3-10)
// Written Work: 40%, Performance Task: 40%, Quarterly Assessment: 20%
const DEPED_WEIGHTS = { WW: 0.40, PT: 0.40, QA: 0.20 };

function renderRecords() {
  const tbody = document.getElementById('records-body');
  if(!tbody) return;

  const gradeFilter = document.getElementById('records-grade-filter');
  const sortFilter = document.getElementById('records-sort-filter');
  const totalStudentsEl = document.getElementById('records-total-students');
  const totalRecitationsEl = document.getElementById('records-total-recitations');
  const targetGrade = gradeFilter ? gradeFilter.value : 'all';
  updateSectionDropdown('records-grade-filter', 'records-section-filter', true);
  const sortBy = sortFilter ? sortFilter.value : 'grade';
  const searchEl = document.getElementById('records-search');
  const searchQ = searchEl ? searchEl.value.toLowerCase().trim() : '';
  const sectionFilterEl = document.getElementById('records-section-filter');
  const secFilter = sectionFilterEl ? sectionFilterEl.value : 'all';

  tbody.innerHTML = '';
  
  let filtered = targetGrade === 'all'
    ? [...state.students]
    : state.students.filter(s => s.grade === targetGrade);

  if (secFilter !== 'all') {
    filtered = filtered.filter(s => (s.section || 'A') === secFilter);
  }

  if (searchQ) {
    filtered = filtered.filter(s => s.name.toLowerCase().includes(searchQ));
  }

  // DepEd Grading Computation
  // For this prototype, recitation scores map to Performance Tasks
  // totalScore / (recitations * maxPointsPossible) gives initial grade percentage
  const maxPts = state.pointsMap['Hard'] || 5;
  const records = filtered.map(student => {
    // Calculate initial grade from recitation performance
    let initialGrade = 0;
    if (student.recitations > 0) {
      const avgScorePercent = (student.totalScore / (student.recitations * maxPts)) * 100;
      // Map: WW (recitation correctness) = 40%, PT (participation count) = 40%, QA (avg score) = 20%
      const wwScore = Math.min(100, avgScorePercent);  // Written Work proxy
      const ptScore = Math.min(100, (student.recitations / 10) * 100); // Performance Task (participation)
      const qaScore = Math.min(100, avgScorePercent);  // Quarterly Assessment proxy
      
      initialGrade = (wwScore * DEPED_WEIGHTS.WW) + (ptScore * DEPED_WEIGHTS.PT) + (qaScore * DEPED_WEIGHTS.QA);
    }

    const transmutedGrade = transmute(initialGrade);
    const depedLevel = getDepEdLevel(transmutedGrade);

    return {
      ...student,
      initialGrade: Math.round(initialGrade * 100) / 100,
      transmutedGrade,
      depedLevel: depedLevel.level,
      depedAbbr: depedLevel.abbr,
      depedClass: depedLevel.cssClass
    };
  });

  // Sort direction: desc by default, toggle from records page
  const sortDir = typeof window.getRecordsSortDir === 'function' ? window.getRecordsSortDir() : 'desc';
  const dir = sortDir === 'asc' ? 1 : -1;

  // Sort
  if (sortBy === 'grade') {
    records.sort((a, b) => dir * (a.transmutedGrade - b.transmutedGrade) || a.name.localeCompare(b.name));
  } else if (sortBy === 'recitations') {
    records.sort((a, b) => dir * (a.recitations - b.recitations) || a.name.localeCompare(b.name));
  } else if (sortBy === 'name') {
    records.sort((a, b) => dir * a.name.localeCompare(b.name));
  } else {
    records.sort((a, b) => dir * (a.totalScore - b.totalScore) || a.name.localeCompare(b.name));
  }

  const totalRecitations = records.reduce((sum, s) => sum + s.recitations, 0);
  const activeLearners = records.filter(s => s.recitations >= 3).length;
  const passiveLearners = records.filter(s => s.recitations < 3).length;

  if (totalStudentsEl) totalStudentsEl.textContent = records.length;
  if (totalRecitationsEl) totalRecitationsEl.textContent = totalRecitations;
  if (document.getElementById('records-active-learners')) document.getElementById('records-active-learners').textContent = activeLearners;
  if (document.getElementById('records-passive-learners')) document.getElementById('records-passive-learners').textContent = passiveLearners;

  if(records.length === 0) {
    const tr = document.createElement('tr');
    tr.innerHTML = '<td colspan="7" style="text-align:center;">No student records available for this selection.</td>';
    tbody.appendChild(tr);
    return;
  }

  records.forEach(student => {
    const tr = document.createElement('tr');
    const isPassing = student.transmutedGrade >= 75;
    tr.innerHTML = `
      <td><strong>${student.name}</strong></td>
      <td>Gr ${student.grade}</td>
      <td>${student.recitations}</td>
      <td><strong class="text-primary">${student.totalScore} pts</strong></td>
      <td>${student.initialGrade}%</td>
      <td><strong style="color:${isPassing ? 'var(--success)' : 'var(--danger)'}">${student.transmutedGrade}</strong></td>
      <td><span class="badge ${student.depedClass}">${student.depedAbbr}</span></td>
    `;
    tbody.appendChild(tr);
  });
}

// --- Profile & Camera Logic ---
let cameraTarget = 'profile'; // 'profile' or studentId
let stream = null;

function openCamera() {
  const container = document.getElementById('camera-container');
  const video = document.getElementById('camera-video');
  if (!container || !video) return;
  container.classList.remove('hidden');
  
  navigator.mediaDevices.getUserMedia({ video: true })
    .then(s => {
      stream = s;
      video.srcObject = stream;
    })
    .catch(err => {
      alert("Camera access denied or unavailable.");
      container.classList.add('hidden');
    });
}

// --- Student Photo Management & Modal Logic ---
let activeStudentPhotoId = null;

function openStudentPhotoModal(studentId) {
  activeStudentPhotoId = Number(studentId);
  const modal = document.getElementById('student-photo-modal');
  if (!modal) {
    // Fallback if modal not present (e.g. other pages)
    startStudentPhoto(studentId);
    return;
  }

  const student = state.students.find(s => Number(s.id) === activeStudentPhotoId);
  if (!student) return;

  const nameEl = document.getElementById('sp-modal-student-name');
  const detailsEl = document.getElementById('sp-modal-student-details');
  const avatarPreview = document.getElementById('sp-modal-avatar-preview');
  const removeBtn = document.getElementById('sp-btn-remove');
  const camContainer = document.getElementById('camera-container');

  if (camContainer) camContainer.classList.add('hidden');
  stopStudentCamera();

  if (nameEl) nameEl.textContent = student.name;
  if (detailsEl) detailsEl.textContent = `Grade ${student.grade} • Section ${student.section || 'A'} • ${student.totalScore || 0} pts • ${student.recitations || 0} recitations`;

  const initials = student.name.split(' ').filter(Boolean).map(n => n[0]).join('').toUpperCase();
  if (avatarPreview) {
    if (student.photo) {
      avatarPreview.innerHTML = `<img src="${student.photo}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
      if (removeBtn) removeBtn.classList.remove('hidden');
    } else {
      avatarPreview.innerHTML = initials;
      if (removeBtn) removeBtn.classList.add('hidden');
    }
  }

  modal.classList.remove('hidden');
}

function closeStudentPhotoModal() {
  stopStudentCamera();
  const modal = document.getElementById('student-photo-modal');
  if (modal) modal.classList.add('hidden');
  activeStudentPhotoId = null;
}

function openStudentCamera() {
  const container = document.getElementById('camera-container');
  const video = document.getElementById('camera-video');
  if (!container || !video) return;

  container.classList.remove('hidden');
  navigator.mediaDevices.getUserMedia({ video: true })
    .then(s => {
      stream = s;
      video.srcObject = stream;
    })
    .catch(err => {
      alert("Camera access denied or unavailable.");
      container.classList.add('hidden');
    });
}

function stopStudentCamera() {
  if (stream) {
    stream.getTracks().forEach(track => track.stop());
    stream = null;
  }
  const container = document.getElementById('camera-container');
  if (container) container.classList.add('hidden');
}

function captureStudentPhoto() {
  const video = document.getElementById('camera-video');
  const canvas = document.getElementById('camera-canvas') || document.createElement('canvas');
  if (!video || !activeStudentPhotoId) return;

  // Scale down to max 320x320 for speed, crisp quality, and low RAM
  const size = Math.min(video.videoWidth, video.videoHeight) || 320;
  canvas.width = 320;
  canvas.height = 320;
  const ctx = canvas.getContext('2d');

  // Center crop square
  const sx = (video.videoWidth - size) / 2;
  const sy = (video.videoHeight - size) / 2;
  ctx.drawImage(video, sx, sy, size, size, 0, 0, 320, 320);

  const dataUrl = canvas.toDataURL('image/jpeg', 0.75);
  updateStudentPhotoData(activeStudentPhotoId, dataUrl);
  stopStudentCamera();
}

function handleStudentPhotoUpload(event) {
  const file = event.target.files && event.target.files[0];
  if (!file || !activeStudentPhotoId) return;

  const reader = new FileReader();
  reader.onload = (e) => {
    const img = new Image();
    img.onload = () => {
      const canvas = document.createElement('canvas');
      canvas.width = 320;
      canvas.height = 320;
      const ctx = canvas.getContext('2d');
      const size = Math.min(img.width, img.height);
      const sx = (img.width - size) / 2;
      const sy = (img.height - size) / 2;
      ctx.drawImage(img, sx, sy, size, size, 0, 0, 320, 320);
      const compressedData = canvas.toDataURL('image/jpeg', 0.75);
      updateStudentPhotoData(activeStudentPhotoId, compressedData);
    };
    img.src = e.target.result;
  };
  reader.readAsDataURL(file);
  event.target.value = '';
}

function removeStudentPhoto() {
  if (!activeStudentPhotoId) return;
  if (confirm("Remove this student's photo?")) {
    updateStudentPhotoData(activeStudentPhotoId, null);
  }
}

function updateStudentPhotoData(studentId, photoData) {
  const student = state.students.find(s => Number(s.id) === Number(studentId));
  if (!student) return;

  student.photo = photoData;
  saveState();
  if (typeof renderStudents === 'function') renderStudents();
  if (typeof renderScoreboard === 'function') renderScoreboard();

  // Update modal preview
  const avatarPreview = document.getElementById('sp-modal-avatar-preview');
  const removeBtn = document.getElementById('sp-btn-remove');
  const initials = student.name.split(' ').filter(Boolean).map(n => n[0]).join('').toUpperCase();
  if (avatarPreview) {
    if (photoData) {
      avatarPreview.innerHTML = `<img src="${photoData}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
      if (removeBtn) removeBtn.classList.remove('hidden');
    } else {
      avatarPreview.innerHTML = initials;
      if (removeBtn) removeBtn.classList.add('hidden');
    }
  }

  // Persist to database immediately
  fetch('student_api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(student)
  }).catch(e => console.warn('Student photo DB update error:', e));
}

function startStudentPhoto(studentId) {
  const modal = document.getElementById('student-photo-modal');
  if (modal) {
    openStudentPhotoModal(studentId);
    return;
  }
  cameraTarget = studentId;
  openCamera();
}

function closeCamera() {
  if(stream) {
    stream.getTracks().forEach(track => track.stop());
    stream = null;
  }
  const container = document.getElementById('camera-container');
  if (container) container.classList.add('hidden');
  cameraTarget = 'profile';
}

function capturePhoto() {
  const video = document.getElementById('camera-video');
  const canvas = document.getElementById('camera-canvas');
  if (!video || !canvas) return;
  
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  
  const ctx = canvas.getContext('2d');
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
  
  const dataUrl = canvas.toDataURL('image/jpeg', 0.7);
  
  if (cameraTarget === 'profile') {
    state.profile.picture = dataUrl;
    applyProfileUI();
  } else {
    updateStudentPhotoData(cameraTarget, dataUrl);
  }
  
  closeCamera();
  saveState();
}

function handleImageUpload(event) {
  const file = event.target.files[0];
  if(file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      state.profile.picture = e.target.result;
      applyProfileUI();
    };
    reader.readAsDataURL(file);
  }
}

function setBorder(borderType) {
  state.profile.border = borderType;
  document.querySelectorAll('.border-option').forEach(el => el.classList.remove('active'));
  document.querySelector(`.border-option[data-border="${borderType}"]`).classList.add('active');
  applyProfileUI();
}

function updateProfile() {
  state.profile.name = document.getElementById('prof-name').value;
  state.profile.bio = document.getElementById('prof-bio').value;
}

function saveProfile() {
  updateProfile();
  saveState();
  applyProfileUI();

  // Save to database
  const payload = {
    username: state.currentUser || 'admin',
    display_name: state.profile.name,
    bio: state.profile.bio,
    avatar_data: state.profile.picture || null,
    border_style: state.profile.border || 'none'
  };

  fetch('profile_api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
  .then(r => r.json())
  .then(data => {
    if (data.status === 'success') {
      alert("Profile saved to database successfully!");
    } else {
      alert("Profile saved locally. Database: " + (data.message || 'unavailable'));
    }
  })
  .catch(() => {
    alert("Profile saved locally (offline mode).");
  });
}

function applyProfileUI() {
  const profileName = document.getElementById('prof-name');
  const profileBio = document.getElementById('prof-bio');

  if(profileName) profileName.value = state.profile.name;
  if(profileBio) profileBio.value = state.profile.bio;

  document.querySelectorAll('.border-option').forEach(el => el.classList.remove('active'));
  const activeOpt = document.querySelector(`.border-option[data-border="${state.profile.border}"]`);
  if(activeOpt) activeOpt.classList.add('active');

  const initials = state.profile.name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map(part => part[0])
    .join('')
    .toUpperCase() || 'SC';

  const avatars = document.querySelectorAll('.user-profile .avatar, .mobile-profile .avatar, #profile-avatar-preview, .user-avatar-small');

  avatars.forEach(av => {
    if(state.profile.picture) {
      av.innerHTML = `<img src="${state.profile.picture}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
    } else {
      av.textContent = initials;
    }

    av.className = av.className.replace(/\s*border-\w+/g, '').trim();
    if(state.profile.border !== 'none') {
      av.classList.add(`border-${state.profile.border}`);
    }

    if(av.classList.contains('user-avatar-small')) {
      av.setAttribute('title', state.profile.name);
    }
  });

  document.querySelectorAll('.user-info strong, .mobile-profile strong').forEach(el => {
    el.textContent = state.profile.name;
  });
}

// --- Interactive Games ---
let gameTimer = null;

function launchGame(gameType) {
  const gameDisplay = document.getElementById('game-display');
  const gameTitle = document.getElementById('game-title');
  const gameContent = document.getElementById('game-content-area');
  const studentSelect = document.getElementById('game-student-select');

  // Populate student select
  studentSelect.innerHTML = '';
  state.students.forEach(s => {
    const opt = document.createElement('option');
    opt.value = s.id;
    opt.textContent = `${s.name} (Gr ${s.grade})`;
    studentSelect.appendChild(opt);
  });

  let content = '';
  let title = '';

  switch(gameType) {
    case 'dance':
      title = 'DANCE GAME';
      content = `
        <div class="dance-animation">
          <i class="fa-solid fa-person-running"></i>
          <h3 style="margin-top:20px;">Photosynthesis Boogie!</h3>
          <p style="font-size:20px; margin-top:10px;">
            Move your arms like leaves catching sunlight!<br>
            Spin around like the water cycle!<br>
            Jump up like oxygen rising!
          </p>
        </div>
      `;
      break;
    case 'sing':
      title = 'SING GAME';
      content = `
        <div class="sing-lyrics">
          <h3>Science Song Karaoke</h3>
          <p style="margin-top:20px;">
            The <span class="highlight-term">SUN</span> gives energy to plants so bright,<br>
            <span class="highlight-term">PHOTOSYNTHESIS</span> happens from morning to night.<br>
            <span class="highlight-term">CARBON DIOXIDE</span> goes in, oxygen comes out,<br>
            That's what the science song is all about!
          </p>
        </div>
      `;
      break;
    case 'groupings':
      title = 'GROUPINGS GAME';
      content = `
        <div class="groupings-setup" style="padding:10px; text-align:left; max-width:500px; margin:0 auto;">
          <h3 style="font-size:18px; margin-bottom:8px;"><i class="fa-solid fa-users text-primary"></i> Classroom Group Generator</h3>
          <p class="text-muted" style="font-size:13px; margin-bottom:15px; line-height:1.4;">Configure and partition your class list into science-themed groups.</p>
          
          <div class="form-group" style="margin-bottom:10px;">
            <label style="font-size:12px; font-weight:600; margin-bottom:4px; display:block;">Grade Level</label>
            <select id="group-grade" class="form-control">
              <option value="3">Grade 3</option>
              <option value="4" selected>Grade 4</option>
              <option value="5">Grade 5</option>
              <option value="6">Grade 6</option>
            </select>
          </div>
          
          <div class="form-group" style="margin-bottom:10px;">
            <label style="font-size:12px; font-weight:600; margin-bottom:4px; display:block;">Group Size (Students per group)</label>
            <select id="group-size" class="form-control">
              <option value="2">2 Students</option>
              <option value="3" selected>3 Students</option>
              <option value="4">4 Students</option>
              <option value="5">5 Students</option>
              <option value="6">6 Students</option>
            </select>
          </div>
          
          <div class="form-group" style="margin-bottom:10px;">
            <label style="font-size:12px; font-weight:600; margin-bottom:4px; display:block;">Theme Naming Style</label>
            <select id="group-theme" class="form-control">
              <option value="ecosystem">Ecosystem Roles (Herbivores, Carnivores...)</option>
              <option value="matter">States of Matter (Solids, Liquids, Gases)</option>
              <option value="space">Solar System Planets (Mercury, Venus...)</option>
              <option value="lab">Science Lab Teams (Physicists, Chemists...)</option>
            </select>
          </div>

          <div class="form-group" style="margin-bottom:15px;">
            <label style="font-size:12px; font-weight:600; margin-bottom:4px; display:block;">Time Limit</label>
            <select id="group-timer-limit" class="form-control">
              <option value="60">1 Minute</option>
              <option value="120" selected>2 Minutes</option>
              <option value="180">3 Minutes</option>
              <option value="300">5 Minutes</option>
            </select>
          </div>
          
          <button class="btn btn-primary w-100" onclick="app.generateDynamicGroupings()"><i class="fa-solid fa-wand-magic-sparkles"></i> Generate & Cast Groups</button>
          
          <div id="group-generator-result" class="mt-20 hidden" style="text-align:center;">
            <h4 style="font-size:15px; margin-bottom:8px;">Generated Groups:</h4>
            <div id="group-timer-display" class="groupings-timer" style="font-size:32px; margin: 10px 0; color:#ffd166; font-weight:800;">2:00</div>
            <div id="groups-list-display" style="max-height:220px; overflow-y:auto; font-size:13px; text-align:left; background:rgba(0,0,0,0.2); padding:12px; border-radius:10px; border:1px solid rgba(255,255,255,0.08);"></div>
          </div>
        </div>
      `;
      break;
  }

  gameTitle.textContent = title;
  gameContent.innerHTML = content;
  gameDisplay.classList.remove('hidden');
}

let groupingTimerInterval = null;

function generateDynamicGroupings() {
  const grade = document.getElementById('group-grade').value;
  const size = parseInt(document.getElementById('group-size').value) || 3;
  const theme = document.getElementById('group-theme').value;
  const timeLimit = parseInt(document.getElementById('group-timer-limit').value) || 120;
  
  const resultDiv = document.getElementById('group-generator-result');
  const timerDisplay = document.getElementById('group-timer-display');
  const listDisplay = document.getElementById('groups-list-display');
  
  if (!resultDiv || !timerDisplay || !listDisplay) return;

  const students = state.students.filter(s => s.grade === grade);
  if (students.length === 0) {
    alert("No students found in the selected grade to form groups!");
    return;
  }

  const shuffled = [...students].sort(() => 0.5 - Math.random());
  const groups = [];
  for (let i = 0; i < shuffled.length; i += size) {
    groups.push(shuffled.slice(i, i + size));
  }

  const themes = {
    ecosystem: {
      title: "ECOSYSTEM ROLES",
      names: ["Carnivores", "Herbivores", "Decomposers", "Producers", "Omnivores", "Scavengers", "Detritivores"],
      instruction: "Discuss your organism's role in the food chain! Mammals, birds, and reptiles working together."
    },
    matter: {
      title: "STATES OF MATTER",
      names: ["Solids Group", "Liquids Group", "Gases Group", "Plasma Group", "Bose-Einstein Group"],
      instruction: "Identify 3 items that match your state of matter. Show how molecules move in your state!"
    },
    space: {
      title: "SOLAR SYSTEM VOYAGERS",
      names: ["Mercury Team", "Venus Team", "Earth Team", "Mars Team", "Jupiter Team", "Saturn Team", "Uranus Team", "Neptune Team"],
      instruction: "Find out your planet's size rank and distance order from the Sun! Present 1 interesting fact."
    },
    lab: {
      title: "SCIENCE LAB TEAMS",
      names: ["Physicists", "Chemists", "Biologists", "Astronomers", "Geologists", "Meteorologists", "Ecologists"],
      instruction: "Assign roles: 1 Recorder, 1 Speaker, and researchers. Design a quick science experiment."
    }
  };

  const activeTheme = themes[theme] || themes['ecosystem'];
  const teamsArray = [];
  let html = '';

  groups.forEach((group, index) => {
    const teamName = activeTheme.names[index % activeTheme.names.length] + ` (Group ${index + 1})`;
    const memberNames = group.map(g => g.name).join(', ');
    teamsArray.push(`${teamName}: ${memberNames}`);
    
    html += `
      <div style="margin-bottom:8px; padding-bottom:6px; border-bottom:1px solid rgba(255,255,255,0.05);">
        <strong style="color:var(--primary);">${teamName}</strong>: <span class="text-muted">${memberNames}</span>
      </div>
    `;
  });

  listDisplay.innerHTML = html;
  resultDiv.classList.remove('hidden');

  if (tvWindow && !tvWindow.closed) {
    showGroupingOnTV(activeTheme.title, activeTheme.instruction, teamsArray);
  } else {
    alert("Groups generated locally! Open the TV Display next time to show them on the second screen.");
  }

  let timeLeft = timeLimit;
  if (groupingTimerInterval) clearInterval(groupingTimerInterval);

  const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  };

  timerDisplay.textContent = formatTime(timeLeft);
  timerDisplay.style.color = '#ffd166';
  
  if (tvWindow && !tvWindow.closed) {
    sendToTV('updateGroupingTimer', { time: formatTime(timeLeft) });
  }

  groupingTimerInterval = setInterval(() => {
    timeLeft--;
    if (timeLeft < 0) {
      clearInterval(groupingTimerInterval);
      timerDisplay.textContent = "TIME'S UP!";
      timerDisplay.style.color = 'var(--danger)';
      if (tvWindow && !tvWindow.closed) {
        sendToTV('updateGroupingTimer', { time: "TIME'S UP!" });
      }
    } else {
      timerDisplay.textContent = formatTime(timeLeft);
      if (tvWindow && !tvWindow.closed) {
        sendToTV('updateGroupingTimer', { time: formatTime(timeLeft) });
      }
    }
  }, 1000);
}

function closeGame() {
  if(gameTimer) {
    clearInterval(gameTimer);
    gameTimer = null;
  }
  if(groupingTimerInterval) {
    clearInterval(groupingTimerInterval);
    groupingTimerInterval = null;
  }
  document.getElementById('game-display').classList.add('hidden');
}

function awardGamePoints(points) {
  const studentId = parseInt(document.getElementById('game-student-select').value);
  if(!studentId) return alert('Please select a student first.');

  const student = state.students.find(s => s.id === studentId);
  if(student) {
    student.totalScore += points;
    student.recitations += 1;
    saveState();
    alert(`${student.name} awarded ${points} points!`);
  }
}

// --- Digital Scoreboard ---
function renderScoreboard() {
  const tbody = document.getElementById('scoreboard-body');
  if (!tbody) return;
  const gradeFilter = document.getElementById('scoreboard-grade');
  updateSectionDropdown('scoreboard-grade', 'scoreboard-section', true);
  const sectionFilter = document.getElementById('scoreboard-section');
  const sortBy = document.getElementById('scoreboard-sort');
  const searchEl = document.getElementById('scoreboard-search');
  const searchQ = searchEl ? searchEl.value.toLowerCase().trim() : '';

  const gf = gradeFilter ? gradeFilter.value : 'all';
  const sf = sectionFilter ? sectionFilter.value : 'all';
  const sortField = sortBy ? sortBy.value : 'points';
  const ascending = typeof window.getScoreboardSortDir === 'function' ? window.getScoreboardSortDir() : false;

  tbody.innerHTML = '';

  let filtered = gf === 'all'
    ? [...state.students]
    : state.students.filter(s => s.grade === gf);

  if (sf !== 'all') {
    filtered = filtered.filter(s => (s.section || 'A') === sf);
  }

  if (searchQ) {
    filtered = filtered.filter(s => s.name.toLowerCase().includes(searchQ));
  }

  // Calculate proficiency for sorting
  filtered.forEach(s => {
    s._prof = s.recitations > 0 ? Math.min(Math.round((s.totalScore / (s.recitations * 3)) * 100), 100) : 0;
  });

  // Sort
  filtered.sort((a, b) => {
    let cmp = 0;
    switch (sortField) {
      case 'name': cmp = a.name.localeCompare(b.name); break;
      case 'grade': cmp = (parseInt(a.grade)||0) - (parseInt(b.grade)||0); break;
      case 'section': cmp = (a.section||'A').localeCompare(b.section||'A'); break;
      case 'recitations': cmp = (a.recitations||0) - (b.recitations||0); break;
      case 'proficiency': cmp = a._prof - b._prof; break;
      default: cmp = (a.totalScore||0) - (b.totalScore||0); break;
    }
    return ascending ? cmp : -cmp;
  });

  // Performance insight counts
  const totalRecitations = filtered.reduce((s, st) => s + (st.recitations||0), 0);
  const active = filtered.filter(s => (s.recitations||0) >= 3).length;
  const passive = filtered.filter(s => (s.recitations||0) > 0 && (s.recitations||0) < 3).length;
  const zeroCount = filtered.filter(s => (s.recitations||0) === 0).length;

  // Update insight cards
  const el = (id, val) => { const e = document.getElementById(id); if (e) e.textContent = val; };
  el('sb-total-students', filtered.length);
  el('sb-total-recitations', totalRecitations);
  el('sb-active-count', active);
  el('sb-passive-count', passive);
  el('sb-zero-count', zeroCount);

  // Fairness alert
  const zeroParticipation = filtered.filter(s => (s.recitations||0) === 0);
  const fairnessAlert = document.getElementById('fairness-alert');
  const fairnessMessage = document.getElementById('fairness-message');
  if (fairnessAlert && fairnessMessage) {
    if (zeroParticipation.length > 0) {
      fairnessAlert.classList.remove('hidden');
      fairnessMessage.textContent = `⚠️ ${zeroParticipation.length} student(s) haven't participated yet: ${zeroParticipation.slice(0,5).map(s => s.name).join(', ')}${zeroParticipation.length > 5 ? '...' : ''}`;
    } else {
      fairnessAlert.classList.add('hidden');
    }
  }

  // Render table rows
  const maxRecitations = Math.max(1, ...filtered.map(s => s.recitations||0));

  filtered.forEach((student, index) => {
    const prof = student._prof;
    const rank = index + 1;
    const isLeader = rank === 1;
    const hasZero = (student.recitations||0) === 0;
    const isActive = (student.recitations||0) >= 3;
    const section = student.section || 'A';
    const barWidth = Math.round(((student.recitations||0) / maxRecitations) * 100);
    const barColor = isActive ? '#06d6a0' : hasZero ? '#ef4444' : '#ffd166';

    const tr = document.createElement('tr');
    if (isLeader) tr.className = 'rank-1';
    if (hasZero) tr.classList.add('highlight-zero');

    const initials = student.name.split(' ').filter(Boolean).map(n => n[0]).join('').toUpperCase();
    const miniAvatar = student.photo
      ? `<img src="${student.photo}" style="width:24px; height:24px; border-radius:50%; object-fit:cover; vertical-align:middle; margin-right:8px; border:1px solid var(--primary);" title="${student.name}">`
      : `<span style="display:inline-flex; align-items:center; justify-content:center; width:24px; height:24px; border-radius:50%; background:rgba(255,255,255,0.1); color:var(--text-main); font-size:10px; font-weight:700; margin-right:8px; vertical-align:middle;">${initials}</span>`;

    tr.innerHTML = `
      <td>${isLeader ? '<i class="fa-solid fa-crown" style="color:#ffd700"></i>' : rank}</td>
      <td>${miniAvatar}<strong>${student.name}</strong></td>
      <td>Gr ${student.grade}</td>
      <td>${section}</td>
      <td><strong class="text-primary">${student.totalScore||0} pts</strong></td>
      <td>
        <div style="display:flex;align-items:center;gap:8px;">
          <span>${student.recitations||0}</span>
          <div class="participation-bar"><div class="participation-fill" style="width:${barWidth}%;background:${barColor};"></div></div>
        </div>
      </td>
      <td><span class="badge" style="background:rgba(255,255,255,0.1)">${prof}%</span></td>
      <td>${isActive
        ? '<span class="status-active" title="Active"><i class="fa-solid fa-circle-check"></i> Active</span>'
        : hasZero
          ? '<span class="status-passive" title="Never participated"><i class="fa-solid fa-circle-xmark"></i> None</span>'
          : '<span style="color:#ffd166" title="Passive"><i class="fa-solid fa-circle-minus"></i> Low</span>'
      }</td>
      <td style="text-align:center;">
        <div class="score-btn-group">
          <button class="btn-point-quick btn-point-plus1" onclick="app.quickModifyPoints(${student.id}, 1)" title="Add 1 point">+1</button>
          <button class="btn-point-quick btn-point-plus3" onclick="app.quickModifyPoints(${student.id}, 3)" title="Add 3 points">+3</button>
          <button class="btn-point-quick btn-point-plus5" onclick="app.quickModifyPoints(${student.id}, 5)" title="Add 5 points">+5</button>
          <button class="btn-point-quick btn-point-minus1" onclick="app.quickModifyPoints(${student.id}, -1)" title="Subtract 1 point">-1</button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

async function quickModifyPoints(studentId, pts) {
  const student = state.students.find(s => s.id == studentId);
  if (!student) return;

  student.totalScore = Math.max(0, (student.totalScore || 0) + pts);
  if (pts > 0) {
    student.recitations = (student.recitations || 0) + 1;
    playChime('coin');
  } else {
    playChime('click');
  }

  saveState();
  renderScoreboard();

  try {
    await fetch('recitation_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        student_id: student.id,
        topic: 'Scoreboard Quick Modifier',
        difficulty: pts >= 3 ? 'Medium' : 'Easy',
        points: pts,
        is_correct: pts > 0 ? 1 : 0
      })
    });
  } catch(e) {}
}

function refreshScoreboard() {
  renderScoreboard();
}

function toggleFullscreen() {
  const scoreboardSection = document.getElementById('view-scoreboard');

  if(!document.fullscreenElement) {
    scoreboardSection.classList.add('fullscreen-tv');
    scoreboardSection.requestFullscreen().catch(err => {
      alert(`Error attempting to enable fullscreen: ${err.message}`);
      scoreboardSection.classList.remove('fullscreen-tv');
    });
  } else {
    document.exitFullscreen();
    scoreboardSection.classList.remove('fullscreen-tv');
  }
}

function suggestNextStudent() {
  const gradeFilter = document.getElementById('scoreboard-grade').value;
  let filtered = gradeFilter === 'all'
    ? [...state.students]
    : state.students.filter(s => s.grade === gradeFilter);

  // Find students with 0 recitations first
  const zeroParticipation = filtered.filter(s => s.recitations === 0);

  if(zeroParticipation.length > 0) {
    // Randomly select from those with 0 participation
    const suggested = zeroParticipation[Math.floor(Math.random() * zeroParticipation.length)];
    document.getElementById('suggested-student-text').innerHTML = `
      <strong class="text-warning">${suggested.name}</strong> has never participated!<br>
      Call them next for fairness.
    `;
  } else {
    // If all have participated, suggest the one with lowest recitations
    filtered.sort((a, b) => a.recitations - b.recitations);
    const suggested = filtered[0];
    document.getElementById('suggested-student-text').innerHTML = `
      <strong>${suggested.name}</strong> has the fewest participations (${suggested.recitations}).<br>
      Consider calling them next.
    `;
  }
}

function suggestStudent() {
  const dropdown = document.getElementById('assess-student');
  if(!dropdown) return;
  
  const gradeEl = document.getElementById('assess-grade');
  const currentGrade = String(gradeEl ? gradeEl.value : (state.assessment.grade || '4'));
  state.assessment.grade = currentGrade;
  const sectionEl = document.getElementById('assess-section');
  const currentSection = sectionEl ? sectionEl.value : 'all';

  const pool = typeof getAssessmentStudentPool === 'function' 
    ? getAssessmentStudentPool() 
    : (state.students || []).filter(s => String(s.grade) === currentGrade);

  if(pool.length === 0) {
    const secNotice = currentSection !== 'all' ? ` Section ${currentSection}` : '';
    alert(`No students found for Grade ${currentGrade}${secNotice}.`);
    return;
  }

  // Find students with lowest recitations in this grade & section
  const candidates = [...pool].sort((a, b) => (Number(a.recitations) || 0) - (Number(b.recitations) || 0));
  const suggestion = candidates[0];
  
  dropdown.value = String(suggestion.id);
  state.assessment.studentId = Number(suggestion.id);
  dropdown.dispatchEvent(new Event('change'));

  // UI Insight for Participation Tracking
  const insightText = document.getElementById('participation-text');
  if (insightText) {
    const recs = Number(suggestion.recitations) || 0;
    const secTag = suggestion.section ? ` [${suggestion.section}]` : '';
    if (recs === 0) {
      insightText.innerHTML = `<span style="color:#ef4444; font-weight:bold;">FAIRNESS CONTROL:</span> ${suggestion.name}${secTag} has <strong>NOT</strong> participated yet.`;
    } else {
      insightText.innerHTML = `Suggested: ${suggestion.name}${secTag} (${recs} participations so far)`;
    }
  }
  
  const secInfo = suggestion.section ? ` (${suggestion.section})` : '';
  alert(`💡 System suggests ${suggestion.name}${secInfo} for fairness.`);
}

function recordRubricResult(points) {
  const studentId = state.assessment.studentId;
  const student = state.students.find(s => s.id == studentId);

  if (student) {
    student.recitations++;
    student.totalScore += points;

    if (points > 0) {
      playChime('correct');
    } else {
      playChime('wrong');
    }

    // Update TV to show points awarded
    if (tvWindow && !tvWindow.closed) {
      tvWindow.postMessage({
        type: 'showFeedback',
        message: `Excellent! ${student.name} awarded ${points} points!`,
        score: points
      }, '*');
    }

    saveState();

    // Persist recitation record to database
    fetch('recitation_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        student_id: student.id,
        topic: state.assessment.topic,
        difficulty: state.assessment.difficulty,
        points: points,
        is_correct: points > 0 ? 1 : 0
      })
    }).catch(e => console.warn('Recitation DB write failed (offline?):', e));

    alert(`Recorded ${points} points for ${student.name}`);
    cancelQuiz();
    renderAssessmentStudents();
  }
}

// --- Lesson Delivery ---
async function updateLessonTopics() {
  const grade = document.getElementById('lesson-grade').value;
  state.lesson.grade = grade;

  const topicSelect = document.getElementById('lesson-topic');
  if(!topicSelect) return;
  topicSelect.innerHTML = '';

  const lessons = await fetchCurriculumLessons(grade);

  if(lessons.length > 0) {
    lessons.forEach(lesson => {
      const opt = document.createElement('option');
      opt.value = lesson.id;
      opt.textContent = `Term ${lesson.quarter} L${lesson.lesson_number}: ${lesson.topic}`;
      opt.dataset.curriculumId = lesson.id;
      topicSelect.appendChild(opt);
    });
    state.lesson.useCurriculum = true;
  } else {
    // No curriculum lessons in DB for this grade
    const noOpt = document.createElement('option');
    noOpt.value = '';
    noOpt.textContent = 'No lessons — add via Materials or Lessons tab';
    topicSelect.appendChild(noOpt);
    state.lesson.useCurriculum = false;
  }
}

async function loadLessonContent() {
  const grade = document.getElementById('lesson-grade').value;
  const topicSelect = document.getElementById('lesson-topic');
  if(!topicSelect) return;
  const topic = topicSelect.value;
  const curriculumId = topicSelect.options[topicSelect.selectedIndex]?.dataset.curriculumId;

  state.lesson.grade = grade;
  state.lesson.topic = topic;
  state.lesson.currentSlide = 0;

  // Check if using curriculum from database
  if(state.lesson.useCurriculum && curriculumId) {
    const slides = await fetchCurriculumSlides(curriculumId);
    if(slides.length > 0) {
      state.lesson.slides = slides.map(slide => ({
        title: slide.title,
        content: slide.content,
        mediaType: slide.media_type || 'none',
        mediaUrl: slide.media_url || ''
      }));
      state.lesson.curriculumId = curriculumId;
      state.lesson.hasQuestions = true;
    } else {
      await generateSlidesFromCurriculum(curriculumId);
    }
  } else if(topic.startsWith('custom_')) {
    const customId = parseInt(topic.replace('custom_', ''));
    const custom = state.customLessons.find(l => l.id === customId);
    if(custom) {
      state.lesson.slides = custom.slides;
      state.lesson.hasQuestions = true;
    }
  } else {
    // No local fallback — all lesson data is now in the database
    state.lesson.slides = [{ title: 'No Content', content: 'This lesson has no slides yet. Use the Lesson Builder to create slides, or import curriculum data.' }];
  }
}

async function generateSlidesFromCurriculum(curriculumId) {
  const lessons = await fetchCurriculumLessons(state.lesson.grade);
  const lesson = lessons.find(l => l.id == curriculumId);
  if(lesson) {
    state.lesson.slides = [
      { title: 'Learning Objectives', content: Array.isArray(lesson.objectives) ? lesson.objectives.join('\n\n') : (lesson.objectives || '') },
      { title: lesson.topic, content: (lesson.content || '').substring(0, 1000) }
    ];
    state.lesson.curriculumId = curriculumId;
    state.lesson.hasQuestions = lesson.questions && lesson.questions.length > 0;
  }
}

async function displayLessonOnTV() {
  const topicSelect = document.getElementById('lesson-topic');
  const topic = topicSelect ? topicSelect.value : null;
  if(!topic) return alert('Please select a topic first.');

  // Auto-open TV display if closed so lesson is projected
  if (!tvWindow || tvWindow.closed) {
    openTVDisplay();
  }

  await loadLessonContent();

  const lessonDisplay = document.getElementById('lesson-display');
  const lessonTitle = document.getElementById('lesson-display-title');
  if(!lessonDisplay) return;

  const displayLabel = topicSelect.options[topicSelect.selectedIndex]?.textContent || topic;
  if (lessonTitle) lessonTitle.textContent = displayLabel;

  renderCurrentSlide();

  lessonDisplay.classList.remove('hidden');

  // Double sync after window initialization delay
  setTimeout(() => {
    if (tvWindow && !tvWindow.closed) {
      renderCurrentSlide();
    }
  }, 600);
}

function renderCurrentSlide() {
  const lessonContent = document.getElementById('lesson-content-area');
  const slideCounter = document.getElementById('slide-counter');
  const recitationBtn = document.getElementById('start-recitation-btn');

  if(state.lesson.slides.length === 0) {
    lessonContent.innerHTML = '<p>No slides available for this topic.</p>';
    return;
  }

  const currentSlide = state.lesson.slides[state.lesson.currentSlide];
  
  let mediaHTML = '';
  if (currentSlide.mediaType === 'image') {
    mediaHTML = `<img src="${currentSlide.mediaUrl}" style="max-width:100%; border-radius:12px; margin-top:20px; box-shadow:0 10px 30px rgba(0,0,0,0.3);">`;
  } else if (currentSlide.mediaType === 'video') {
    mediaHTML = `<iframe src="${currentSlide.mediaUrl}" style="width:100%; height:400px; border-radius:12px; margin-top:20px;" frameborder="0" allowfullscreen></iframe>`;
  } else if (currentSlide.mediaType === 'interactive') {
    mediaHTML = `<button class="btn btn-primary mt-20" onclick="window.open('${currentSlide.mediaUrl}', '_blank')"><i class="fa-solid fa-flask-vial"></i> Launch Experiment Simulation</button>`;
  } else if (currentSlide.mediaType === 'step') {
    const steps = currentSlide.mediaUrl.split('|').map(s => `<li>${s.trim()}</li>`).join('');
    mediaHTML = `
      <div class="step-demo mt-20" style="background:rgba(255,255,255,0.05); padding:20px; border-radius:12px; border-left:4px solid var(--primary);">
        <h4 style="margin-bottom:10px;"><i class="fa-solid fa-list-ol"></i> Step-by-Step Procedure:</h4>
        <ol style="margin-left:20px; font-size:18px;">${steps}</ol>
      </div>
    `;
  }

  lessonContent.innerHTML = `
    <h3 style="font-size:28px; color:var(--primary); margin-bottom:15px;">${currentSlide.title}</h3>
    <div style="font-size:20px; line-height:1.6; color:var(--text-main); white-space: pre-wrap;">${currentSlide.content}</div>
    ${mediaHTML}
  `;

  slideCounter.textContent = `${state.lesson.currentSlide + 1} / ${state.lesson.slides.length}`;
  
  // Sync to TV
  if (tvWindow && !tvWindow.closed) {
    tvWindow.postMessage({ 
      type: 'showGame', 
      title: currentSlide.title, 
      content: `
        <div style="text-align:center; padding:40px;">
          <h2 style="font-size:48px; color:#3b82f6;">${currentSlide.title}</h2>
          <div style="font-size:32px; margin-top:30px; line-height:1.4;">${currentSlide.content}</div>
          ${mediaHTML.replace('height:400px', 'height:600px')}
        </div>
      `
    }, '*');
  }

  // Show recitation button on last slide if lesson has questions
  const isLastSlide = state.lesson.currentSlide === state.lesson.slides.length - 1;
  if(isLastSlide && state.lesson.hasQuestions) {
    recitationBtn.style.display = 'inline-block';
  } else {
    recitationBtn.style.display = 'none';
  }
}

function nextSlide() {
  if(state.lesson.currentSlide < state.lesson.slides.length - 1) {
    state.lesson.currentSlide++;
    renderCurrentSlide();
  }
}

function previousSlide() {
  if(state.lesson.currentSlide > 0) {
    state.lesson.currentSlide--;
    renderCurrentSlide();
  }
}

function closeLesson() {
  document.getElementById('lesson-display').classList.add('hidden');
  document.getElementById('lesson-display').classList.remove('lesson-fullscreen');
  state.lesson.currentSlide = 0;
}

async function startLessonRecitation() {
  // Close lesson display
  closeLesson();

  // Load questions from curriculum lesson and start assessment
  if(state.lesson.curriculumId) {
    const lessons = await fetchCurriculumLessons(state.lesson.grade);
    const lesson = lessons.find(l => l.id == state.lesson.curriculumId);
    if(lesson && lesson.questions && lesson.questions.length > 0) {
      const curriculumQuestions = lesson.questions.map(q => ({
        grade: state.lesson.grade,
        topic: lesson.topic,
        difficulty: 'Medium',
        text: q.question || q,
        options: q.options
      }));

      switchView('assessment');
      state.assessment.grade = state.lesson.grade;
      state.assessment.topic = lesson.topic;
      state.assessment.difficulty = 'Medium';
      state.questions = [...state.questions, ...curriculumQuestions];
      await updateAssessmentTopics();
      renderAssessmentStudents();
      alert(`Loaded ${curriculumQuestions.length} questions from lesson: ${lesson.topic}`);
    } else {
      alert('No questions available for this lesson.');
    }
  }
}

function toggleLessonFullscreen() {
  const lessonDisplay = document.getElementById('lesson-display');

  if(!document.fullscreenElement) {
    lessonDisplay.classList.add('lesson-fullscreen');
    lessonDisplay.requestFullscreen().catch(err => {
      alert(`Error attempting to enable fullscreen: ${err.message}`);
      lessonDisplay.classList.remove('lesson-fullscreen');
    });
  } else {
    document.exitFullscreen();
    lessonDisplay.classList.remove('lesson-fullscreen');
  }
}

function toggleGameFullscreen() {
  const gameDisplay = document.getElementById('game-display');

  if(!document.fullscreenElement) {
    gameDisplay.classList.add('game-fullscreen');
    gameDisplay.requestFullscreen().catch(err => {
      alert(`Error attempting to enable fullscreen: ${err.message}`);
      gameDisplay.classList.remove('game-fullscreen');
    });
  } else {
    document.exitFullscreen();
    gameDisplay.classList.remove('game-fullscreen');
  }
}

// --- Interactive Lesson Builder ---
let builderSlides = [];
let builderEditLessonId = null;

function addBuilderSlide(title = '', content = '', mediaType = 'none', mediaUrl = '') {
  const container = document.getElementById('builder-slides-container');
  if(!container) return;
  
  const slideId = Date.now() + Math.random().toString(36).substr(2, 5);
  const slideCard = document.createElement('div');
  slideCard.className = 'builder-slide-card';
  slideCard.id = `slide-${slideId}`;
  
  const slideNum = container.children.length + 1;
  
  slideCard.innerHTML = `
    <div class="slide-number">#${slideNum}</div>
    <div class="form-group">
      <label>Slide Title</label>
      <input type="text" class="form-control slide-title" placeholder="Slide heading" value="${title.replace(/"/g, '&quot;')}">
    </div>
    <div class="form-group mt-10">
      <label>Content (HTML/Text)</label>
      <textarea class="form-control slide-content" rows="3" placeholder="Explain the concept...">${content}</textarea>
    </div>
    <div class="form-group mt-10">
      <label>Interactive Element / Media</label>
      <select class="form-control slide-media-type">
        <option value="none" ${mediaType === 'none' ? 'selected' : ''}>None</option>
        <option value="image" ${mediaType === 'image' || mediaType === 'visual' ? 'selected' : ''}>Image URL</option>
        <option value="video" ${mediaType === 'video' ? 'selected' : ''}>Video URL</option>
        <option value="interactive" ${mediaType === 'interactive' ? 'selected' : ''}>Simulation/Experiment Link</option>
        <option value="step" ${mediaType === 'step' ? 'selected' : ''}>Step-by-Step Demo</option>
      </select>
      <input type="text" class="form-control slide-media-url mt-10" placeholder="Paste URL or Type Step Content" value="${mediaUrl.replace(/"/g, '&quot;')}">
    </div>
    <button class="btn btn-danger btn-sm mt-10" onclick="this.parentElement.remove()">Remove Slide</button>
  `;
  container.appendChild(slideCard);
}

function autoFillTestLesson(slideCount = 1) {
  const timestamp = Math.floor(Math.random() * 900 + 100);
  const isMulti = slideCount > 1;
  const lessonTitle = isMulti ? `Automated Multi-Slide Test Lesson #${timestamp}` : `Automated Single-Slide Test Lesson #${timestamp}`;
  const topicName = isMulti ? `Properties & Changes in Matter #${timestamp}` : `Introduction to Matter #${timestamp}`;

  const titleInput = document.getElementById('builder-lesson-title');
  const gradeInput = document.getElementById('builder-grade');
  const quarterInput = document.getElementById('builder-quarter');
  const numberInput = document.getElementById('builder-lesson-number');
  const topicInput = document.getElementById('builder-topic');
  const objectivesInput = document.getElementById('builder-objectives');

  if (titleInput) titleInput.value = lessonTitle;
  if (gradeInput) gradeInput.value = '4';
  if (quarterInput) quarterInput.value = '1';
  if (numberInput) numberInput.value = Math.floor(Math.random() * 50 + 1).toString();
  if (topicInput) topicInput.value = topicName;
  if (objectivesInput) {
    objectivesInput.value = isMulti
      ? "Identify physical and chemical properties of matter\nDemonstrate safety precautions during experiments\nFormulate scientific conclusions based on observations"
      : "Define matter and identify its basic states";
  }

  const container = document.getElementById('builder-slides-container');
  if (container) {
    container.innerHTML = '';
  }

  if (slideCount === 1) {
    addBuilderSlide(
      'Slide 1: What is Matter?',
      '<p><strong>Matter</strong> is anything that has mass and takes up space (volume). All physical objects around us are made of matter.</p><ul><li>Solid</li><li>Liquid</li><li>Gas</li></ul>',
      'image',
      'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=800'
    );
  } else {
    for (let i = 1; i <= slideCount; i++) {
      let mediaType = 'none';
      let mediaUrl = '';
      let slideTitle = '';
      let slideContent = '';

      if (i === 1) {
        slideTitle = 'Slide 1: Introduction to Matter';
        slideContent = '<p>Welcome to Grade 4 Science! Today we explore how materials change state under heat and pressure.</p>';
        mediaType = 'none';
      } else if (i === 2) {
        slideTitle = 'Slide 2: Visualizing States of Matter';
        slideContent = '<p>Observe the molecular structures of solids, liquids, and gases below.</p>';
        mediaType = 'image';
        mediaUrl = 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=800';
      } else if (i === 3) {
        slideTitle = 'Slide 3: Interactive Experiment Steps';
        slideContent = '<p>Follow these steps to conduct the ice melting experiment safely:</p>';
        mediaType = 'step';
        mediaUrl = 'Step 1: Measure 50g of ice cubes into beaker.\nStep 2: Apply low heat using safety burner.\nStep 3: Record temperature every 30 seconds until boiling.';
      } else {
        slideTitle = `Slide ${i}: Summary & Assessment`;
        slideContent = `<p>Summary of key concepts covered in slide ${i}. Answer the recitation question on your student app!</p>`;
        mediaType = 'interactive';
        mediaUrl = 'https://phet.colorado.edu/sims/html/states-of-matter-basics/latest/states-of-matter-basics_en.html';
      }

      addBuilderSlide(slideTitle, slideContent, mediaType, mediaUrl);
    }
  }
}

function saveCustomLesson() {
  let title = document.getElementById('builder-lesson-title').value.trim();
  const grade = document.getElementById('builder-grade').value;
  const quarter = document.getElementById('builder-quarter').value;
  const lessonNumber = document.getElementById('builder-lesson-number').value.trim();
  let topic = document.getElementById('builder-topic').value.trim();
  const objectivesText = document.getElementById('builder-objectives').value.trim();
  const slideCards = document.querySelectorAll('.builder-slide-card');
  
  if (!title && topic) title = topic;
  if (!topic && title) topic = title;
  if (!title && !topic) return alert('Please enter a lesson title / topic.');
  if (slideCards.length === 0) return alert('Add at least one slide.');
  
  const slides = [];
  slideCards.forEach(card => {
    slides.push({
      title: card.querySelector('.slide-title').value,
      content: card.querySelector('.slide-content').value,
      mediaType: card.querySelector('.slide-media-type').value,
      mediaUrl: card.querySelector('.slide-media-url').value
    });
  });

  const objectives = objectivesText ? objectivesText.split('\n').map(line => line.trim()).filter(line => line) : [];

  const payload = {
    grade: grade,
    quarter: quarter,
    lesson_number: lessonNumber || '1',
    topic: topic,
    objectives: objectives,
    slides: slides
  };

  const isEdit = builderEditLessonId !== null;
  const url = 'lessons_api.php';
  const method = isEdit ? 'PUT' : 'POST';
  
  if (isEdit) {
    payload.id = builderEditLessonId;
  } else {
    payload.action = 'create_lesson';
  }

  fetch(url, {
    method: method,
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      alert(isEdit ? '✅ Lesson updated successfully in the database!' : '✅ Lesson created successfully in the database!');
      
      // Invalidate the curriculum lessons cache for this grade so it re-fetches fresh from the DB
      const cache = loadCachedJson(CURRICULUM_LESSONS_CACHE_KEY);
      if (cache && cache[grade]) {
        delete cache[grade];
        saveCachedJson(CURRICULUM_LESSONS_CACHE_KEY, cache);
      }
      
      // Clear builder inputs and reset edit state
      builderEditLessonId = null;
      document.getElementById('builder-lesson-title').value = '';
      document.getElementById('builder-lesson-number').value = '';
      document.getElementById('builder-topic').value = '';
      document.getElementById('builder-objectives').value = '';
      document.getElementById('builder-slides-container').innerHTML = '';
      
      const formTitle = document.getElementById('builder-form-title');
      if (formTitle) {
        formTitle.innerHTML = `<i class="fa-solid fa-wand-magic-sparkles text-primary"></i> Create Interactive Lesson`;
      }
      
      renderCustomLessonsList();
    } else {
      alert('❌ Failed to save lesson: ' + data.message);
    }
  })
  .catch(err => {
    console.error('Error saving custom lesson:', err);
    alert('❌ Error saving lesson. Make sure XAMPP is running.');
  });
}

async function renderCustomLessonsList() {
  const list = document.getElementById('custom-lessons-list');
  if(!list) return;
  list.innerHTML = '<p class="text-muted"><i class="fa-solid fa-spinner fa-spin"></i> Loading curriculum...</p>';

  const grade = document.getElementById('builder-grade') ? document.getElementById('builder-grade').value : '4';
  
  try {
    const lessons = await fetchCurriculumLessons(grade);
    list.innerHTML = '';

    if (lessons.length === 0) {
      list.innerHTML = '<p class="text-muted">No lessons found in curriculum. Create one above!</p>';
      return;
    }

    lessons.forEach(lesson => {
      const div = document.createElement('div');
      div.className = 'lesson-item-card';
      div.style.display = 'flex';
      div.style.justifyContent = 'space-between';
      div.style.alignItems = 'center';
      div.style.padding = '12px';
      div.style.borderBottom = '1px solid rgba(255,255,255,0.06)';

      div.innerHTML = `
        <div style="flex:1;">
          <strong>${lesson.topic}</strong> <span class="badge badge-primary" style="background:var(--primary); padding:2px 8px; border-radius:12px; font-size:11px;">Term ${lesson.quarter} L${lesson.lesson_number}</span><br>
          <small class="text-muted">Grade ${lesson.grade} | Objectives: ${lesson.objectives ? lesson.objectives.length : 0}</small>
        </div>
        <div style="display:flex; gap:8px;">
          <button class="btn btn-primary btn-sm" onclick="app.presentCurriculumLessonDirect(${lesson.id})"><i class="fa-solid fa-play"></i> Use</button>
          <button class="btn btn-secondary btn-sm" onclick="app.editCurriculumLessonDirect(${lesson.id})"><i class="fa-solid fa-edit"></i> Edit</button>
          <button class="btn btn-danger btn-sm" onclick="app.deleteCurriculumLessonDirect(${lesson.id})"><i class="fa-solid fa-trash"></i></button>
        </div>
      `;
      list.appendChild(div);
    });
  } catch (e) {
    list.innerHTML = '<p style="color:var(--danger);">Error loading curriculum lessons.</p>';
  }
}

async function presentCurriculumLessonDirect(id) {
  try {
    const response = await fetch(`lessons_api.php`);
    const data = await response.json();
    if (data.status !== 'success') return alert("Error loading lesson.");
    const lessons = data.lessons || [];
    const lesson = lessons.find(l => l.id == id);
    if (!lesson) return alert("Lesson not found.");

    document.getElementById('lesson-grade').value = lesson.grade;
    await updateLessonTopics();

    const topicSelect = document.getElementById('lesson-topic');
    if (topicSelect) {
      topicSelect.value = lesson.id;
      loadLessonContent();
      switchLessonTab('delivery');
    }
  } catch (e) {
    alert("Failed to load lesson for presentation.");
  }
}

async function editCurriculumLessonDirect(id) {
  try {
    const response = await fetch(`lessons_api.php?slides=${id}`);
    const data = await response.json();
    if (data.status !== 'success') return alert("Failed to fetch lesson slides.");
    
    const lessonsRes = await fetch(`lessons_api.php`);
    const lessonsData = await lessonsRes.json();
    const lesson = (lessonsData.lessons || []).find(l => l.id == id);
    if (!lesson) return alert("Lesson not found in database.");

    document.getElementById('builder-lesson-title').value = lesson.topic;
    document.getElementById('builder-grade').value = lesson.grade;
    document.getElementById('builder-quarter').value = lesson.quarter;
    document.getElementById('builder-lesson-number').value = lesson.lesson_number;
    document.getElementById('builder-topic').value = lesson.topic;
    document.getElementById('builder-objectives').value = Array.isArray(lesson.objectives) ? lesson.objectives.join('\n') : '';

    builderEditLessonId = id;
    const formTitle = document.getElementById('builder-form-title');
    if (formTitle) {
      formTitle.innerHTML = `<i class="fa-solid fa-edit text-warning"></i> Edit Curriculum Lesson (ID: ${id})`;
    }

    const slidesContainer = document.getElementById('builder-slides-container');
    slidesContainer.innerHTML = '';

    const slides = data.slides || [];
    slides.forEach(slide => {
      addBuilderSlide(slide.title, slide.content, slide.slide_type, slide.media_url);
    });

    alert(`Loaded lesson "${lesson.topic}" into Builder. Modify slide contents and click Save to update database.`);
  } catch (e) {
    alert("Error loading lesson for editing.");
  }
}

async function deleteCurriculumLessonDirect(id) {
  if (!confirm("Are you sure you want to delete this lesson from the database completely? This action cannot be undone and will delete all of its slides.")) return;
  
  try {
    const res = await fetch('lessons_api.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    });
    const data = await res.json();
    if (data.status === 'success') {
      alert("Lesson deleted successfully from database!");
      
      const cache = loadCachedJson(CURRICULUM_LESSONS_CACHE_KEY);
      const grade = document.getElementById('builder-grade') ? document.getElementById('builder-grade').value : '4';
      if (cache && cache[grade]) {
        delete cache[grade];
        saveCachedJson(CURRICULUM_LESSONS_CACHE_KEY, cache);
      }
      
      renderCustomLessonsList();
    } else {
      alert("Failed to delete lesson: " + data.message);
    }
  } catch (e) {
    alert("Error deleting lesson from database.");
  }
}
// --- PPTX Upload & Playback ---

function showPPTXUploadModal() {
  const existing = document.getElementById('pptx-upload-modal');
  if (existing) existing.remove();

  const modal = document.createElement('div');
  modal.id = 'pptx-upload-modal';
  modal.className = 'export-modal';
  modal.innerHTML = `
    <div class="glass-card export-modal-content">
      <h2 style="margin-bottom:20px;"><i class="fa-solid fa-file-powerpoint text-primary"></i> Import PowerPoint</h2>
      <p class="text-muted" style="margin-bottom:16px;">Upload a .pptx file to convert into a Canva-style presentation.<br>
      Naming format: <code>PPT_SCIENCE_G4_Q3_W4.pptx</code> auto-detects grade & term.</p>
      
      <div class="form-group" style="margin-bottom:14px;">
        <label>Override Grade (optional)</label>
        <select id="pptx-grade" class="form-control">
          <option value="">Auto-detect from filename</option>
          <option value="3">Grade 3</option>
          <option value="4">Grade 4</option>
          <option value="5">Grade 5</option>
          <option value="6">Grade 6</option>
        </select>
      </div>

      <div class="form-group" style="margin-bottom:14px;">
        <label>Override Term (optional)</label>
        <select id="pptx-quarter" class="form-control">
          <option value="">Auto-detect from filename</option>
          <option value="1">Term 1</option>
          <option value="2">Term 2</option>
          <option value="3">Term 3</option>
          <option value="4">Term 4</option>
        </select>
      </div>
      
      <div style="border:2px dashed var(--glass-border);border-radius:12px;padding:30px;text-align:center;margin-bottom:20px;cursor:pointer;" onclick="document.getElementById('pptx-file-input').click()">
        <i class="fa-solid fa-file-powerpoint" style="font-size:48px;color:#c4532e;margin-bottom:10px;display:block;"></i>
        <p style="color:var(--text-muted);">Click to browse or drop a .pptx file</p>
        <p id="pptx-file-name" style="color:var(--primary);margin-top:8px;font-weight:600;"></p>
        <input type="file" id="pptx-file-input" class="hidden" accept=".pptx" onchange="document.getElementById('pptx-file-name').textContent = this.files[0] ? this.files[0].name + ' (' + (this.files[0].size/1024).toFixed(1) + ' KB)' : ''">
      </div>
      
      <div style="display:flex;gap:12px;justify-content:flex-end;">
        <button class="btn btn-secondary" onclick="document.getElementById('pptx-upload-modal').remove()">Cancel</button>
        <button class="btn btn-success" onclick="uploadPPTX()"><i class="fa-solid fa-upload"></i> Upload & Convert</button>
      </div>
    </div>
  `;
  document.body.appendChild(modal);
  modal.addEventListener('click', (e) => { if (e.target === modal) modal.remove(); });
}

async function uploadPPTX() {
  const fileInput = document.getElementById('pptx-file-input');
  if (!fileInput || !fileInput.files.length) {
    alert('Please select a .pptx file first.');
    return;
  }

  const file = fileInput.files[0];
  const grade = document.getElementById('pptx-grade').value;
  const quarter = document.getElementById('pptx-quarter').value;

  const formData = new FormData();
  formData.append('pptx', file);
  if (grade) formData.append('grade', grade);
  if (quarter) formData.append('quarter', quarter);
  formData.append('username', state.currentUser || '');

  try {
    const btn = document.querySelector('#pptx-upload-modal .btn-success');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Converting...';
    }

    const res = await fetch('pptx_api.php', { method: 'POST', body: formData });
    const data = await res.json();

    if (data.status === 'success') {
      alert('✅ ' + data.message);
      document.getElementById('pptx-upload-modal').remove();
      // Refresh lessons list & topic selectors
      if (typeof loadPPTXList === 'function') loadPPTXList();
      if (typeof refreshAllTopicSelectors === 'function') await refreshAllTopicSelectors();
      // Offer to open in presenter
      if (data.curriculum_lesson_id && confirm('Open the presentation now?')) {
        window.location.href = 'presenter.html?id=' + data.curriculum_lesson_id;
      }
    } else {
      alert('❌ ' + data.message);
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-upload"></i> Upload & Convert';
      }
    }
  } catch (e) {
    alert('Upload failed. Is XAMPP running?');
  }
}

// --- Admin Interface (Database-Driven via admin_api.php) ---

async function loadAdminStats() {
  const loading = document.getElementById('admin-stats-loading');
  const content = document.getElementById('admin-stats-content');
  const grid = document.getElementById('admin-stat-grid');
  const recentList = document.getElementById('admin-recent-recitations');
  if (!grid) return;

  if (loading) loading.style.display = 'block';
  if (content) content.style.display = 'none';

  try {
    const res = await fetch('admin_api.php?action=stats');
    const data = await res.json();
    if (data.status !== 'success') throw new Error(data.message);

    const s = data.stats;
    grid.innerHTML = `
      <div class="stat-card"><div class="stat-num">${s.total_users}</div><div class="stat-label">Users</div></div>
      <div class="stat-card"><div class="stat-num">${s.total_students}</div><div class="stat-label">Students</div></div>
      <div class="stat-card"><div class="stat-num">${s.total_questions}</div><div class="stat-label">Questions</div></div>
      <div class="stat-card"><div class="stat-num">${s.total_lessons}</div><div class="stat-label">Lessons</div></div>
      <div class="stat-card"><div class="stat-num">${s.total_recitations}</div><div class="stat-label">Recitations</div></div>
    `;

    // Students by grade breakdown
    if (s.students_by_grade && s.students_by_grade.length > 0) {
      grid.innerHTML += s.students_by_grade.map(g =>
        `<div class="stat-card"><div class="stat-num">${g.count}</div><div class="stat-label">Grade ${g.grade} Students</div></div>`
      ).join('');
    }

    // Recent recitations
    if (recentList) {
      if (s.recent_recitations && s.recent_recitations.length > 0) {
        recentList.innerHTML = s.recent_recitations.map(r => `
          <li>
            <span><strong>${r.student_name || 'ID:' + r.student_id}</strong> — ${r.topic} (${r.difficulty})</span>
            <span>${r.is_correct ? '✅' : '❌'} ${r.points} pts — ${new Date(r.created_at).toLocaleString()}</span>
          </li>
        `).join('');
      } else {
        recentList.innerHTML = '<li style="color:var(--text-muted);">No recitations yet</li>';
      }
    }

    if (loading) loading.style.display = 'none';
    if (content) content.style.display = 'block';
  } catch (e) {
    if (loading) loading.innerHTML = '<p style="color:var(--danger);">Failed to load stats. Is XAMPP running?</p>';
    console.error('Admin stats error:', e);
  }
}

async function loadAdminUsers() {
  const loading = document.getElementById('admin-users-loading');
  const table = document.getElementById('admin-users-table');
  const tbody = document.getElementById('admin-users-tbody');
  if (!tbody) return;

  if (loading) loading.style.display = 'block';
  if (table) table.style.display = 'none';

  try {
    const res = await fetch('admin_api.php?action=users');
    const data = await res.json();
    if (data.status !== 'success') throw new Error(data.message);

    tbody.innerHTML = data.users.map(u => {
      const uInitials = (u.display_name || u.username).substring(0, 2).toUpperCase();
      const uAvatar = u.avatar_data
        ? `<img src="${u.avatar_data}" style="width:28px; height:28px; border-radius:50%; object-fit:cover; vertical-align:middle; margin-right:8px; border:1.5px solid var(--primary);" title="${u.username}">`
        : `<span style="display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:50%; background:var(--primary); color:#fff; font-size:11px; font-weight:700; vertical-align:middle; margin-right:8px;">${uInitials}</span>`;
      return `
        <tr>
          <td>${u.id}</td>
          <td>${uAvatar}<strong>${u.username}</strong></td>
          <td>${u.display_name || '—'}</td>
          <td>${u.email || '—'}</td>
          <td><span class="badge-role badge-${u.role}">${u.role}</span></td>
          <td>${u.created_at ? new Date(u.created_at).toLocaleDateString() : '—'}</td>
          <td>
            <button class="btn-xs btn-edit" onclick="app.adminEditUser(${u.id}, '${u.username}', '${(u.display_name||'').replace(/'/g,"\\'")}', '${(u.email||'').replace(/'/g,"\\'")}', '${u.role}')"><i class="fa-solid fa-pen"></i></button>
            <button class="btn-xs btn-del" onclick="app.adminDeleteUser(${u.id}, '${u.username}')"><i class="fa-solid fa-trash"></i></button>
          </td>
        </tr>
      `;
    }).join('');

    if (loading) loading.style.display = 'none';
    if (table) table.style.display = 'table';
  } catch (e) {
    if (loading) loading.innerHTML = '<p style="color:var(--danger);">Failed to load users. Is XAMPP running?</p>';
    console.error('Admin users error:', e);
  }
}

// Legacy wrappers for DOMContentLoaded init
function renderAdminUsers() { loadAdminUsers(); }
function addTeacherAccount() { adminCreateUser(); }
function removeTeacherAccount(username) { /* legacy stub — now uses ID-based delete */ }

async function adminCreateUser() {
  const username = document.getElementById('admin-new-username').value.trim();
  const displayName = document.getElementById('admin-new-displayname') ? document.getElementById('admin-new-displayname').value.trim() : '';
  const email = document.getElementById('admin-new-email') ? document.getElementById('admin-new-email').value.trim() : '';
  const password = document.getElementById('admin-new-password').value.trim();
  const role = document.getElementById('admin-new-role') ? document.getElementById('admin-new-role').value : 'teacher';

  if (!username || !password) return alert('Username and password are required.');

  try {
    const res = await fetch('admin_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'create_user', username, password, display_name: displayName || username, email, role })
    });
    const data = await res.json();
    if (data.status === 'success') {
      alert('✅ ' + data.message);
      document.getElementById('admin-new-username').value = '';
      if (document.getElementById('admin-new-displayname')) document.getElementById('admin-new-displayname').value = '';
      if (document.getElementById('admin-new-email')) document.getElementById('admin-new-email').value = '';
      document.getElementById('admin-new-password').value = '';
      loadAdminUsers();
    } else {
      alert('❌ ' + data.message);
    }
  } catch (e) {
    alert('Failed to create user. Is XAMPP running?');
  }
}

async function adminCreateStudent() {
  const nameInput = document.getElementById('admin-student-name');
  const gradeInput = document.getElementById('admin-student-grade');
  const sectionInput = document.getElementById('admin-student-section');
  const quarterInput = document.getElementById('admin-student-quarter');

  const name = nameInput ? nameInput.value.trim() : '';
  const grade = gradeInput ? gradeInput.value : '7';
  const section = sectionInput ? sectionInput.value : 'A';

  if (!name) return alert('Please enter student full name (e.g. abc def ghi).');

  const studentId = Date.now();
  const payload = {
    id: studentId,
    name: name,
    grade: grade,
    section: section,
    recitations: 8,
    totalScore: 90
  };

  try {
    const res = await fetch('student_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.status === 'success') {
      alert(`✅ Student "${name}" created in Grade ${grade} (Sec ${section})!`);
      if (nameInput) nameInput.value = '';
      if (typeof loadAdminStats === 'function') loadAdminStats();
    } else {
      alert('❌ Failed to create student: ' + data.message);
    }
  } catch (e) {
    alert('Error creating student. Make sure XAMPP is running.');
  }
}

async function autoGenerateDemoQuestions() {
  try {
    const res = await fetch('seed_demo_data.php');
    const data = await res.json();
    if (data.status === 'success') {
      alert(`✅ Question Bank populated! ${data.questions_inserted} new demonstration questions added across all grades & difficulty measures.`);
      renderQuestionBank();
    } else {
      alert('❌ Error: ' + data.message);
    }
  } catch(e) {
    alert('Error auto-generating demo questions.');
  }
}

function adminEditUser(id, username, displayName, email, role) {
  // Remove existing modal
  const existing = document.getElementById('admin-edit-modal');
  if (existing) existing.remove();

  const modal = document.createElement('div');
  modal.id = 'admin-edit-modal';
  modal.className = 'edit-modal-overlay';
  modal.innerHTML = `
    <div class="edit-modal-box">
      <h3><i class="fa-solid fa-user-pen text-primary"></i> Edit User: ${username}</h3>
      <div class="form-group"><label>Display Name</label><input type="text" id="edit-displayname" class="form-control" value="${displayName}"></div>
      <div class="form-group"><label>Email</label><input type="email" id="edit-email" class="form-control" value="${email}"></div>
      <div class="form-group"><label>New Password (leave blank to keep)</label><input type="password" id="edit-password" class="form-control" placeholder=""></div>
      <div class="form-group"><label>Role</label>
        <select id="edit-role" class="form-control">
          <option value="teacher" ${role==='teacher'?'selected':''}>Teacher</option>
          <option value="admin" ${role==='admin'?'selected':''}>Admin</option>
        </select>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
        <button class="btn btn-secondary" onclick="document.getElementById('admin-edit-modal').remove()">Cancel</button>
        <button class="btn btn-primary" onclick="app.adminSaveUser(${id})"><i class="fa-solid fa-save"></i> Save</button>
      </div>
    </div>
  `;
  document.body.appendChild(modal);
  modal.addEventListener('click', (e) => { if (e.target === modal) modal.remove(); });
}

async function adminSaveUser(id) {
  const payload = {
    action: 'update_user',
    id: id,
    display_name: document.getElementById('edit-displayname').value.trim(),
    email: document.getElementById('edit-email').value.trim(),
    role: document.getElementById('edit-role').value
  };
  const pw = document.getElementById('edit-password').value.trim();
  if (pw) payload.password = pw;

  try {
    const res = await fetch('admin_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.status === 'success') {
      alert('✅ User updated');
      document.getElementById('admin-edit-modal').remove();
      loadAdminUsers();
    } else {
      alert('❌ ' + data.message);
    }
  } catch (e) {
    alert('Failed to update user. Is XAMPP running?');
  }
}

async function adminDeleteUser(id, username) {
  if (!confirm(`Delete user "${username}"? This cannot be undone.`)) return;
  try {
    const res = await fetch('admin_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete_user', id: id })
    });
    const data = await res.json();
    if (data.status === 'success') {
      alert('✅ User deleted');
      loadAdminUsers();
    } else {
      alert('❌ ' + data.message);
    }
  } catch (e) {
    alert('Failed to delete user. Is XAMPP running?');
  }
}

function downloadEClassRecord() {
  const grade = document.getElementById('eclass-grade') ? document.getElementById('eclass-grade').value : 'all';
  const section = document.getElementById('eclass-section') ? document.getElementById('eclass-section').value : 'all';
  const quarter = document.getElementById('eclass-quarter') ? document.getElementById('eclass-quarter').value : '1';
  window.open(`admin_api.php?action=export_eclass&grade=${encodeURIComponent(grade)}&section=${encodeURIComponent(section)}&quarter=${encodeURIComponent(quarter)}`, '_blank');
}

function onAdminEClassGradeChange() {
  updateSectionDropdown('eclass-grade', 'eclass-section', true);
  previewEClassRecord();
}

async function previewEClassRecord() {
  const gradeEl = document.getElementById('eclass-grade');
  const sectionEl = document.getElementById('eclass-section');
  const previewDiv = document.getElementById('eclass-preview');
  const head = document.getElementById('eclass-preview-head');
  const body = document.getElementById('eclass-preview-body');
  const countBadge = document.getElementById('eclass-preview-count-badge');
  if (!previewDiv || !head || !body) return;

  const grade = gradeEl ? gradeEl.value : 'all';
  const section = sectionEl ? sectionEl.value : 'all';

  // Build preview from local state using DepEd weights
  let students = grade === 'all' ? [...state.students] : state.students.filter(s => String(s.grade) === String(grade));
  if (section && section !== 'all') {
    students = students.filter(s => (s.section || '').trim().toLowerCase() === section.trim().toLowerCase());
  }

  if (countBadge) {
    countBadge.textContent = `${students.length} Learner${students.length === 1 ? '' : 's'}`;
  }

  if (students.length === 0) {
    previewDiv.style.display = 'block';
    head.innerHTML = '';
    body.innerHTML = '<tr><td colspan="10" style="text-align:center;color:var(--text-muted);padding:30px;"><i class="fa-solid fa-circle-info"></i> No learners found for the selected grade and section.</td></tr>';
    return;
  }

  head.innerHTML = `<tr>
    <th style="width:45px; text-align:center;">#</th>
    <th>Learner Name</th>
    <th style="text-align:center;">Grade</th>
    <th style="text-align:center;">Section</th>
    <th style="text-align:center;">WW (40%)</th>
    <th style="text-align:center;">PT (40%)</th>
    <th style="text-align:center;">QA (20%)</th>
    <th style="text-align:center;">Initial</th>
    <th style="text-align:center;">Transmuted</th>
    <th style="text-align:center;">DepEd Level</th>
  </tr>`;

  body.innerHTML = students.map((s, i) => {
    const totalAttempts = s.recitations || 0;
    const totalPoints = s.totalScore || 0;
    const wwPS = totalAttempts > 0 ? Math.round((totalPoints / (totalAttempts * 5)) * 100) : 0;
    const wwWS = (Math.min(wwPS, 100) * 0.40).toFixed(1);
    const ptPS = totalAttempts > 0 ? Math.min(Math.round((totalPoints / (totalAttempts * 5)) * 100), 100) : 0;
    const ptWS = (ptPS * 0.40).toFixed(1);
    const qaPS = totalAttempts > 0 ? Math.min(Math.round((totalPoints / (totalAttempts * 3)) * 100), 100) : 0;
    const qaWS = (qaPS * 0.20).toFixed(1);
    const initial = (parseFloat(wwWS) + parseFloat(ptWS) + parseFloat(qaWS)).toFixed(1);
    const trans = transmute(parseFloat(initial));
    const lvl = getDepEdLevel(trans);
    const secTag = s.section ? `<span class="badge" style="background:rgba(59,130,246,0.12); color:var(--primary); font-size:11px; padding:2px 8px; border-radius:6px; font-weight:600;">${s.section}</span>` : '<span style="color:var(--text-muted);">-</span>';
    return `<tr>
      <td style="text-align:center; color:var(--text-muted); font-size:12px;">${i+1}</td>
      <td style="font-weight:600; color:var(--text-main);">${s.name}</td>
      <td style="text-align:center; color:var(--text-muted);">Grade ${s.grade}</td>
      <td style="text-align:center;">${secTag}</td>
      <td style="text-align:center;">${wwWS}</td>
      <td style="text-align:center;">${ptWS}</td>
      <td style="text-align:center;">${qaWS}</td>
      <td style="text-align:center; font-weight:600;">${initial}</td>
      <td style="text-align:center; font-weight:700; color:var(--primary); font-size:14px;">${trans}</td>
      <td style="text-align:center;"><span class="${lvl.cssClass}" style="padding:3px 10px;border-radius:10px;font-size:11px;font-weight:700;">${lvl.abbr}</span></td>
    </tr>`;
  }).join('');
  previewDiv.style.display = 'block';
}

function generateReport() {
  const grade = document.getElementById('admin-report-grade') ? document.getElementById('admin-report-grade').value : 'all';
  const output = document.getElementById('admin-report-output');
  if (!output) return;

  let filtered = grade === 'all'
    ? [...state.students]
    : state.students.filter(s => s.grade === grade);

  if(filtered.length === 0) {
    output.style.display = 'none';
    return alert('No students found for the selected grade.');
  }

  const totalRecitations = filtered.reduce((sum, s) => sum + (s.recitations||0), 0);
  const totalPoints = filtered.reduce((sum, s) => sum + (s.totalScore||0), 0);
  const avgProficiency = filtered.length > 0
    ? Math.round(filtered.reduce((sum, s) => sum + (s.recitations > 0 ? Math.round((s.totalScore / (s.recitations * 3)) * 100) : 0), 0) / filtered.length)
    : 0;

  const report = `
=== PARTICIPATION REPORT ===
Grade: ${grade === 'all' ? 'All Grades' : 'Grade ' + grade}
Generated: ${new Date().toLocaleString()}

SUMMARY STATISTICS
------------------
Total Students: ${filtered.length}
Total Recitations: ${totalRecitations}
Total Points Awarded: ${totalPoints}
Average Proficiency: ${avgProficiency}%

STUDENT DETAILS
---------------
${filtered.map(s => {
  const prof = s.recitations > 0 ? Math.round((s.totalScore / (s.recitations * 3)) * 100) + '%' : '0%';
  return `${s.name} | Grade ${s.grade} | Recitations: ${s.recitations||0} | Points: ${s.totalScore||0} | Proficiency: ${prof}`;
}).join('\n')}

FAIRNESS ANALYSIS
----------------
Students with 0 participations: ${filtered.filter(s => (s.recitations||0) === 0).length}
Students with < 3 participations: ${filtered.filter(s => (s.recitations||0) < 3).length}
  `;

  output.textContent = report;
  output.style.display = 'block';
}

function loadSystemSettings() {
  const easyEl = document.getElementById('admin-easy-pts');
  const medEl = document.getElementById('admin-medium-pts');
  const hardEl = document.getElementById('admin-hard-pts');
  if (easyEl) easyEl.value = state.pointsMap['Easy'];
  if (medEl) medEl.value = state.pointsMap['Medium'];
  if (hardEl) hardEl.value = state.pointsMap['Hard'];
}

function saveSystemSettings() {
  state.pointsMap['Easy'] = parseInt(document.getElementById('admin-easy-pts').value) || 1;
  state.pointsMap['Medium'] = parseInt(document.getElementById('admin-medium-pts').value) || 3;
  state.pointsMap['Hard'] = parseInt(document.getElementById('admin-hard-pts').value) || 5;
  saveState();
  alert('System settings saved successfully!');
}

function exportData() {
  const exportData = {
    state: state,
    adminUsers: adminUsers,
    exportDate: new Date().toISOString()
  };

  const dataStr = JSON.stringify(exportData, null, 2);
  const dataBlob = new Blob([dataStr], { type: 'application/json' });
  const url = URL.createObjectURL(dataBlob);

  const link = document.createElement('a');
  link.href = url;
  link.download = `ilikesci_backup_${new Date().toISOString().split('T')[0]}.json`;
  link.click();

  URL.revokeObjectURL(url);
}

function importData(event) {
  const file = event.target.files[0];
  if(!file) return;

  if(!confirm('This will overwrite all current data. Continue?')) return;

  const reader = new FileReader();
  reader.onload = (e) => {
    try {
      const imported = JSON.parse(e.target.result);
      if(imported.state) {
        state = { ...defaultState, ...imported.state };
      }
      if(imported.adminUsers) {
        adminUsers = imported.adminUsers;
      }
      saveState();
      alert('Data imported successfully! The page will now reload.');
      location.reload();
    } catch(err) {
      alert('Error importing data: Invalid file format.');
    }
  };
  reader.readAsText(file);
  event.target.value = '';
}

// --- Theme Management ---
function loadTheme() {
  const savedTheme = localStorage.getItem('ilikesci_theme') || 'dark';
  document.documentElement.setAttribute('data-theme', savedTheme);
  updateThemeIcon(savedTheme);
}

function toggleTheme() {
  const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
  const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', newTheme);
  localStorage.setItem('ilikesci_theme', newTheme);
  updateThemeIcon(newTheme);
}

function updateThemeIcon(theme) {
  const icon = theme === 'dark' ? 'fa-moon' : 'fa-sun';
  const icons = document.querySelectorAll('#theme-icon, #theme-icon-mobile');
  icons.forEach(el => {
    el.className = `fa-solid ${icon}`;
  });
}

// --- Sidebar Toggle ---
function toggleSidebar() {
  const mainNav = document.querySelector('.main-nav');
  const contentArea = document.querySelector('.content-area');
  const toggleBtn = document.querySelector('.sidebar-toggle');

  mainNav.classList.toggle('collapsed');
  contentArea.classList.toggle('expanded');

  // Update toggle button icon
  const icon = toggleBtn.querySelector('i');
  if (mainNav.classList.contains('collapsed')) {
    icon.className = 'fa-solid fa-bars';
  } else {
    icon.className = 'fa-solid fa-xmark';
  }
}

// --- Gamer-Style Keyboard Shortcuts ---
function initKeyboardShortcuts() {
  document.addEventListener('keydown', (e) => {
    const key = e.key.toLowerCase();

    // ESC - Open/Close Options Menu
    if (e.key === 'Escape') {
      e.preventDefault();
      toggleOptionsMenu();
    }

    // X - Quit / Logout
    if (key === 'x' && state.isLoggedIn) {
      e.preventDefault();
      handleLogout();
    }

    // Enter - Login (on login screen) or Accept selections
    if (e.key === 'Enter') {
      const loginScreen = document.getElementById('login-screen');
      if (!loginScreen.classList.contains('hidden')) {
        e.preventDefault();
        handleLogin();
      }
    }
  });

  // Enter key for login inputs
  const loginInputs = document.querySelectorAll('#login-user, #login-pass');
  loginInputs.forEach(input => {
    input.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        handleLogin();
      }
    });
  });
}

function toggleOptionsMenu() {
  const optionsMenu = document.getElementById('options-menu');
  optionsMenu.classList.toggle('hidden');
}

function closeOptionsMenu() {
  const optionsMenu = document.getElementById('options-menu');
  optionsMenu.classList.add('hidden');
}

function toggleSound() {
  state.soundEnabled = !state.soundEnabled;
  const btn = document.getElementById('toggle-sound');
  if (state.soundEnabled) {
    btn.innerHTML = '<i class="fa-solid fa-volume-high"></i> ON';
    btn.classList.remove('off');
  } else {
    btn.innerHTML = '<i class="fa-solid fa-volume-xmark"></i> OFF';
    btn.classList.add('off');
  }
  saveState();
}

function toggleMusic() {
  state.musicEnabled = !state.musicEnabled;
  const btn = document.getElementById('toggle-music');
  if (state.musicEnabled) {
    btn.innerHTML = '<i class="fa-solid fa-music"></i> ON';
    btn.classList.remove('off');
  } else {
    btn.innerHTML = '<i class="fa-solid fa-volume-xmark"></i> OFF';
    btn.classList.add('off');
  }
  saveState();
}

// Procedural Web Audio Synthesizer (100% Offline, Zero external media files)
let appAudioCtx = null;
function playChime(type) {
  if (!state.soundEnabled) return;
  try {
    if (!appAudioCtx) {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (AudioCtx) appAudioCtx = new AudioCtx();
    }
    if (appAudioCtx && appAudioCtx.state === 'suspended') {
      appAudioCtx.resume();
    }
    if (!appAudioCtx) return;

    const now = appAudioCtx.currentTime;

    if (type === 'correct') {
      // Ascending C Major Triad + High C (C5, E5, G5, C6)
      [523.25, 659.25, 783.99, 1046.5].forEach((freq, idx) => {
        const osc = appAudioCtx.createOscillator();
        const gain = appAudioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(freq, now + idx * 0.08);
        gain.gain.setValueAtTime(0.2, now + idx * 0.08);
        gain.gain.exponentialRampToValueAtTime(0.001, now + idx * 0.08 + 0.35);
        osc.connect(gain);
        gain.connect(appAudioCtx.destination);
        osc.start(now + idx * 0.08);
        osc.stop(now + idx * 0.08 + 0.36);
      });
    } else if (type === 'wrong') {
      // Low dual-tone buzzer
      const osc = appAudioCtx.createOscillator();
      const gain = appAudioCtx.createGain();
      osc.type = 'sawtooth';
      osc.frequency.setValueAtTime(150, now);
      osc.frequency.linearRampToValueAtTime(110, now + 0.25);
      gain.gain.setValueAtTime(0.25, now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.28);
      osc.connect(gain);
      gain.connect(appAudioCtx.destination);
      osc.start(now);
      osc.stop(now + 0.3);
    } else if (type === 'fanfare') {
      // Triumphant Winner Fanfare (C5, G5, C6)
      const notes = [
        { f: 523.25, d: 0.12, t: 0 },
        { f: 523.25, d: 0.12, t: 0.14 },
        { f: 523.25, d: 0.12, t: 0.28 },
        { f: 659.25, d: 0.2, t: 0.42 },
        { f: 783.99, d: 0.2, t: 0.64 },
        { f: 1046.5, d: 0.5, t: 0.86 }
      ];
      notes.forEach(n => {
        const osc = appAudioCtx.createOscillator();
        const gain = appAudioCtx.createGain();
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(n.f, now + n.t);
        gain.gain.setValueAtTime(0.25, now + n.t);
        gain.gain.exponentialRampToValueAtTime(0.001, now + n.t + n.d);
        osc.connect(gain);
        gain.connect(appAudioCtx.destination);
        osc.start(now + n.t);
        osc.stop(now + n.t + n.d + 0.02);
      });
    } else if (type === 'coin') {
      // Upward frequency chirp
      const osc = appAudioCtx.createOscillator();
      const gain = appAudioCtx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(987.77, now);
      osc.frequency.setValueAtTime(1318.51, now + 0.08);
      gain.gain.setValueAtTime(0.2, now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
      osc.connect(gain);
      gain.connect(appAudioCtx.destination);
      osc.start(now);
      osc.stop(now + 0.36);
    } else if (type === 'tick') {
      // Woodblock tap
      const osc = appAudioCtx.createOscillator();
      const gain = appAudioCtx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(800, now);
      gain.gain.setValueAtTime(0.12, now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.04);
      osc.connect(gain);
      gain.connect(appAudioCtx.destination);
      osc.start(now);
      osc.stop(now + 0.05);
    } else if (type === 'buzzer') {
      // Time's up alarm
      [0, 0.2].forEach(offset => {
        const osc = appAudioCtx.createOscillator();
        const gain = appAudioCtx.createGain();
        osc.type = 'sawtooth';
        osc.frequency.setValueAtTime(220, now + offset);
        gain.gain.setValueAtTime(0.3, now + offset);
        gain.gain.exponentialRampToValueAtTime(0.001, now + offset + 0.15);
        osc.connect(gain);
        gain.connect(appAudioCtx.destination);
        osc.start(now + offset);
        osc.stop(now + offset + 0.16);
      });
    } else if (type === 'click') {
      // Soft UI Click
      const osc = appAudioCtx.createOscillator();
      const gain = appAudioCtx.createGain();
      osc.type = 'triangle';
      osc.frequency.setValueAtTime(450, now);
      gain.gain.setValueAtTime(0.08, now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.03);
      osc.connect(gain);
      gain.connect(appAudioCtx.destination);
      osc.start(now);
      osc.stop(now + 0.04);
    }
  } catch(e) {}
}

// Native Offline Text-to-Speech Engine (0 bytes download, native Web Speech API)
function speakText(text, lang = 'en-US') {
  if (!text || typeof window.speechSynthesis === 'undefined') return;
  try {
    window.speechSynthesis.cancel(); // Stop any active speech
    const cleanText = text.replace(/<[^>]*>?/gm, '').replace(/[_*#]/g, '');
    const utterance = new SpeechSynthesisUtterance(cleanText);
    utterance.lang = lang;
    utterance.rate = 0.9; // Clear, natural speed for young learners
    utterance.pitch = 1.0;
    window.speechSynthesis.speak(utterance);
  } catch(e) {
    console.warn('Speech synthesis unavailable:', e);
  }
}

// Global Science Curriculum MIDI Player Helper
async function playCurriculumMidi(fileKey) {
  if (window.midiPlayer) {
    try {
      await window.midiPlayer.load(`audio/${fileKey}`);
      window.midiPlayer.play();
      return true;
    } catch (e) {
      console.warn('MIDI playback error:', e);
      return false;
    }
  }
  return false;
}

function stopCurriculumMidi() {
  if (window.midiPlayer) {
    window.midiPlayer.stop();
  }
}

// Quick Science Question Seeder for Question Bank
async function seedScienceQuestions() {
  const sampleItems = [
    { grade: '3', topic: 'Living Things', difficulty: 'Easy', text: 'Which part of the plant absorbs water and nutrients from the soil?', type: 'multiple-choice' },
    { grade: '3', topic: 'Living Things', difficulty: 'Medium', text: 'Name the process by which green plants make their own food.', type: 'identification' },
    { grade: '3', topic: 'Matter', difficulty: 'Easy', text: 'Which state of matter has a definite shape and volume?', type: 'multiple-choice' },
    { grade: '4', topic: 'Materials', difficulty: 'Easy', text: 'Materials that allow water to pass through them are called porous materials.', type: 'multiple-choice' },
    { grade: '4', topic: 'Materials', difficulty: 'Medium', text: 'What property of materials describes their ability to decay and decompose naturally?', type: 'identification' },
    { grade: '4', topic: 'Living Things', difficulty: 'Hard', text: 'Explain the function of chloroplasts in plant photosynthesis.', type: 'open-ended' },
    { grade: '5', topic: 'Simple Machines', difficulty: 'Easy', text: 'A ramp used to load heavy boxes onto a truck is an example of which simple machine?', type: 'multiple-choice' },
    { grade: '5', topic: 'Simple Machines', difficulty: 'Medium', text: 'What is the point on which a lever rests or turns called?', type: 'identification' },
    { grade: '5', topic: 'Electricity', difficulty: 'Medium', text: 'In a complete electrical circuit, electrons flow from the negative terminal to the positive terminal.', type: 'multiple-choice' },
    { grade: '6', topic: 'Solar System', difficulty: 'Easy', text: 'Which planet is known as the Red Planet in our Solar System?', type: 'multiple-choice' },
    { grade: '6', topic: 'Ecosystems', difficulty: 'Hard', text: 'Describe how energy flows through a terrestrial food web starting from producers.', type: 'open-ended' }
  ];

  let added = 0;
  for (const q of sampleItems) {
    try {
      await fetch('questions_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(q)
      });
      added++;
    } catch(e) {}
  }

  await fetchQuestionsFromDB();
  if (typeof renderQuestionBank === 'function') renderQuestionBank();
  playChime('fanfare');
  alert(`🎉 Successfully seeded ${added} curriculum science questions into the database!`);
}

function restartApp() {
  if (confirm('Restart the application?')) {
    location.reload();
  }
}

function quitApp() {
  if (confirm('Quit / Logout?')) {
    handleLogout();
    closeOptionsMenu();
  }
}

// --- TV Display / Dual-Screen Presenter View ---
let tvWindow = null;

function openTVDisplay() {
  if (tvWindow && !tvWindow.closed) {
    tvWindow.focus();
    return;
  }
  
  tvWindow = window.open('tv_display.html', 'TVDisplay', 'width=1920,height=1080');
  
  // Check if popup was blocked
  if (!tvWindow) {
    alert('Please allow popups for this site to use TV Mode. Check your browser settings.');
  }
  
  // Monitor window closure
  const checkClosed = setInterval(() => {
    if (tvWindow && tvWindow.closed) {
      clearInterval(checkClosed);
      tvWindow = null;
    }
  }, 1000);
}

function closeTVDisplay() {
  if (tvWindow && !tvWindow.closed) {
    tvWindow.close();
    tvWindow = null;
  }
}

function sendToTV(type, data) {
  if (!tvWindow || tvWindow.closed) {
    openTVDisplay();
    setTimeout(() => {
      if (tvWindow && !tvWindow.closed) {
        tvWindow.postMessage({ type, ...data }, '*');
      }
    }, 600);
    return;
  }
  tvWindow.postMessage({ type, ...data }, '*');
}

function showQuestionOnTV(question) {
  sendToTV('showQuestion', { question });
}

function showTimerOnTV(time) {
  sendToTV('showTimer', { time });
}

function updateTimerOnTV(time) {
  sendToTV('updateTimer', { time });
}

function showGroupingOnTV(grouping, instructions, teams) {
  sendToTV('showGrouping', { grouping, instructions, teams });
}

function showGameOnTV(title, content) {
  sendToTV('showGame', { title, content });
}

function showWelcomeOnTV() {
  sendToTV('showWelcome', {});
}

function toggleTVFullscreen() {
  sendToTV('fullscreen', {});
}

function showVideoOnTV(videoUrl) {
  sendToTV('showVideo', { videoUrl });
}

// --- Global Escape Key Handler ---
function setupGlobalEscapeHandler() {
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      // Close any open modals or overlays
      const modals = document.querySelectorAll('[class*="modal"], [class*="overlay"]');
      modals.forEach(modal => {
        if (modal.id && (modal.id.includes('modal') || modal.id.includes('popup'))) {
          modal.classList.add('hidden');
        }
      });

      // Close slide preview modal if open
      const slidePreviewModal = document.getElementById('slide-preview-modal');
      if (slidePreviewModal && !slidePreviewModal.classList.contains('hidden')) {
        closeSlidePreview();
      }

      // Close game display if open
      const gameDisplay = document.getElementById('game-display');
      if (gameDisplay && !gameDisplay.classList.contains('hidden')) {
        closeGame();
      }

      // Close lesson display if open
      const lessonDisplay = document.getElementById('lesson-display');
      if (lessonDisplay && !lessonDisplay.classList.contains('hidden')) {
        closeLesson();
      }

      // Close flash quiz if open
      const flashScreen = document.getElementById('flash-screen');
      if (flashScreen && !flashScreen.classList.contains('hidden')) {
        cancelQuiz();
      }

      // Close assessment flash if open
      const assessmentFlash = document.getElementById('assessment-flash');
      if (assessmentFlash && !assessmentFlash.classList.contains('hidden')) {
        cancelQuiz();
      }
    }
  });

  // Global keyboard navigation for in-page slide deck preview
  document.addEventListener('keydown', (e) => {
    const modal = document.getElementById('slide-preview-modal');
    if (modal && !modal.classList.contains('hidden')) {
      if (['input', 'select', 'textarea'].includes(document.activeElement?.tagName?.toLowerCase())) return;
      if (e.key === 'ArrowRight' || e.key === ' ') {
        e.preventDefault();
        previewNextSlide();
      } else if (e.key === 'ArrowLeft') {
        e.preventDefault();
        previewPrevSlide();
      }
    }
  });
}

// Initialize escape key handler
setupGlobalEscapeHandler();

// Safe HTML escaper
function escapeHtml(str) {
  if (typeof str !== 'string') return String(str || '');
  return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

// --- Slide Preview State & Controls (for Index Slide Viewer) ---
let currentPreviewDeck = {
  id: null,
  title: '',
  totalSlides: 0,
  currentSlide: 1,
  slides: [],
  fileType: 'pdf'
};

let dashboardActiveView = 'all';

function filterDashboardView(viewType) {
  dashboardActiveView = viewType || 'all';
  const pills = document.querySelectorAll('#dash-view-filters .deck-filter-pill');
  pills.forEach(p => {
    if (p.getAttribute('data-view') === dashboardActiveView) p.classList.add('active');
    else p.classList.remove('active');
  });
  renderDashboardLessons();
}

async function openSlidePreview(pptxId, initialSlide = 1) {
  const modal = document.getElementById('slide-preview-modal');
  if (!modal) return;

  modal.classList.remove('hidden');
  const titleEl = document.getElementById('preview-deck-title');
  const counterEl = document.getElementById('preview-counter');
  const imgEl = document.getElementById('preview-slide-img');
  const filmstripEl = document.getElementById('preview-filmstrip');

  if (titleEl) titleEl.textContent = 'Loading presentation slides...';
  if (counterEl) counterEl.textContent = 'Loading...';
  if (imgEl) imgEl.src = '';
  if (filmstripEl) filmstripEl.innerHTML = '<div style="color:var(--text-muted);font-size:12px;padding:8px;"><i class="fa-solid fa-spinner fa-spin"></i> Loading slide images...</div>';

  try {
    const res = await fetch(`pptx_api.php?action=slides&id=${pptxId}`);
    const data = await res.json();
    if (data.status !== 'success' || !data.slides || data.slides.length === 0) {
      if (titleEl) titleEl.textContent = 'No slides found for this presentation';
      return;
    }

    const upload = data.upload || {};
    currentPreviewDeck = {
      id: pptxId,
      title: upload.topic || upload.original_name || `Presentation #${pptxId}`,
      totalSlides: data.slides.length,
      currentSlide: Math.max(1, Math.min(initialSlide, data.slides.length)),
      slides: data.slides,
      fileType: upload.file_type || (upload.original_name && upload.original_name.toLowerCase().endsWith('.pdf') ? 'pdf' : 'pptx')
    };

    if (titleEl) titleEl.textContent = currentPreviewDeck.title;
    const pdfBtn = document.getElementById('preview-open-pdf-btn');
    if (pdfBtn) {
      pdfBtn.href = `pptx_api.php?action=pdf_raw&id=${pptxId}`;
      pdfBtn.style.display = currentPreviewDeck.fileType === 'pdf' ? 'inline-flex' : 'none';
    }
    const presBtn = document.getElementById('preview-full-present-btn');
    if (presBtn) presBtn.href = `presenter.html?pptx=${pptxId}`;

    const badge = document.getElementById('preview-badge');
    if (badge) {
      const isPdf = currentPreviewDeck.fileType === 'pdf';
      badge.className = `deck-format-badge ${isPdf ? 'badge-pdf' : 'badge-pptx'}`;
      badge.style.position = 'static';
      badge.innerHTML = `<i class="${isPdf ? 'fa-solid fa-file-pdf' : 'fa-solid fa-file-powerpoint'}"></i> ${currentPreviewDeck.fileType.toUpperCase()} Slides`;
    }

    // Render filmstrip thumbnails
    if (filmstripEl) {
      filmstripEl.innerHTML = currentPreviewDeck.slides.map((s, idx) => {
        const slideNum = idx + 1;
        return `
          <img class="filmstrip-thumb ${slideNum === currentPreviewDeck.currentSlide ? 'active' : ''}" 
               id="thumb-${slideNum}" 
               src="${s.image_url}" 
               alt="Slide ${slideNum}" 
               title="Slide ${slideNum}" 
               onclick="app.previewGoToSlide(${slideNum})">
        `;
      }).join('');
    }

    renderCurrentPreviewSlide();
  } catch(e) {
    console.error('Slide preview error:', e);
    if (titleEl) titleEl.textContent = 'Error loading presentation slides';
  }
}

function renderCurrentPreviewSlide() {
  if (!currentPreviewDeck.slides || currentPreviewDeck.slides.length === 0) return;
  const cur = currentPreviewDeck.currentSlide;
  const total = currentPreviewDeck.totalSlides;
  const slideObj = currentPreviewDeck.slides[cur - 1];

  const counterEl = document.getElementById('preview-counter');
  if (counterEl) counterEl.textContent = `Slide ${cur} / ${total}`;

  const img = document.getElementById('preview-slide-img');
  if (img && slideObj) {
    img.style.opacity = '0.5';
    img.onload = () => { img.style.opacity = '1'; };
    img.src = slideObj.image_url;
  }

  // Update filmstrip active state
  document.querySelectorAll('.filmstrip-thumb').forEach(th => th.classList.remove('active'));
  const activeThumb = document.getElementById(`thumb-${cur}`);
  if (activeThumb) {
    activeThumb.classList.add('active');
    activeThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
  }
}

function closeSlidePreview() {
  const modal = document.getElementById('slide-preview-modal');
  if (modal) modal.classList.add('hidden');
}

function previewNextSlide() {
  if (currentPreviewDeck.currentSlide < currentPreviewDeck.totalSlides) {
    currentPreviewDeck.currentSlide++;
    renderCurrentPreviewSlide();
  }
}

function previewPrevSlide() {
  if (currentPreviewDeck.currentSlide > 1) {
    currentPreviewDeck.currentSlide--;
    renderCurrentPreviewSlide();
  }
}

function previewGoToSlide(num) {
  if (num >= 1 && num <= currentPreviewDeck.totalSlides) {
    currentPreviewDeck.currentSlide = num;
    renderCurrentPreviewSlide();
  }
}

function previewCastCurrentToTV() {
  if (!currentPreviewDeck.id) return;
  castSlideToTV(currentPreviewDeck.id, currentPreviewDeck.title);
}

function castSlideToTV(pptxId, title) {
  const embedCode = `<iframe src="presenter.html?pptx=${pptxId}" style="width:100%;height:100vh;border:none;"></iframe>`;
  if (typeof sendToTV === 'function') {
    sendToTV(title || 'Presentation', embedCode);
  } else if (typeof showGameOnTV === 'function') {
    showGameOnTV(title || 'Presentation', embedCode);
  } else if (typeof openTVDisplay === 'function') {
    openTVDisplay();
    setTimeout(() => {
      if (typeof sendToTV === 'function') sendToTV(title || 'Presentation', embedCode);
    }, 500);
  }
}

// --- Dashboard Path Rendering (DB-driven) ---
async function updateDashboardTopics() {
  const gradeSelect = document.getElementById('dash-grade-select');
  const topicSelect = document.getElementById('dash-topic-select');
  if (!gradeSelect || !topicSelect) return;

  // Check URL parameters for initial grade/topic selection
  const urlParams = new URLSearchParams(window.location.search);
  const urlGrade = urlParams.get('grade');
  const urlTopic = urlParams.get('topic');

  if (urlGrade && ['3','4','5','6'].includes(urlGrade)) {
    gradeSelect.value = urlGrade;
  }

  const prevSelected = topicSelect.value;
  const grade = gradeSelect.value;
  topicSelect.innerHTML = '';

  const uniqueTopics = await fetchAllTopicsForGrade(grade);

  // Add "All Topics & Slide Decks" as top default option
  const allOpt = document.createElement('option');
  allOpt.value = 'all';
  allOpt.textContent = `🌟 All Slide Decks & Topics (Grade ${grade})`;
  topicSelect.appendChild(allOpt);

  if (uniqueTopics.length > 0) {
    uniqueTopics.forEach(topic => {
      const opt = document.createElement('option');
      opt.value = topic;
      opt.textContent = topic;
      topicSelect.appendChild(opt);
    });

    // Selection priority: URL topic > previously selected topic > 'all'
    if (urlTopic && (urlTopic === 'all' || uniqueTopics.includes(urlTopic))) {
      topicSelect.value = urlTopic;
    } else if (prevSelected && (prevSelected === 'all' || uniqueTopics.includes(prevSelected))) {
      topicSelect.value = prevSelected;
    } else {
      topicSelect.value = 'all';
    }
  } else {
    topicSelect.value = 'all';
  }

  await renderDashboardLessons();
}

async function renderDashboardLessons() {
  const gradeSelect = document.getElementById('dash-grade-select');
  const topicSelect = document.getElementById('dash-topic-select');
  const termSelect = document.getElementById('dash-term-select');
  const pathContainer = document.getElementById('lesson-path-container');
  const showcaseContainer = document.getElementById('dash-slides-showcase');
  if (!gradeSelect || !pathContainer) return;

  const grade = String(gradeSelect.value || '4');
  const topic = topicSelect ? (topicSelect.value || 'all').trim() : 'all';
  const selectedTerm = termSelect ? String(termSelect.value || 'all') : 'all';

  pathContainer.innerHTML = '';
  if (showcaseContainer) showcaseContainer.innerHTML = '';

  const isAllTopics = (topic === 'all' || topic === '');
  const normTopic = topic.toLowerCase();

  // 1. Fetch curriculum lessons from DB
  let topicLessons = [];
  try {
    const allLessons = await fetchCurriculumLessons(grade);
    topicLessons = (allLessons || []).filter(l => {
      if (String(l.grade) !== grade) return false;
      if (selectedTerm !== 'all' && String(l.quarter) !== selectedTerm) return false;
      if (!isAllTopics && (l.topic || '').trim().toLowerCase() !== normTopic) return false;
      return true;
    });
  } catch(e) {}

  // 2. Fetch PPTX / PDF uploads from DB
  let matchingPPTXs = [];
  try {
    const res = await fetch('pptx_api.php?action=list');
    const data = await res.json();
    if (data.status === 'success' && Array.isArray(data.uploads)) {
      matchingPPTXs = data.uploads.filter(u => {
        if (String(u.grade) !== grade) return false;
        if (selectedTerm !== 'all' && String(u.quarter) !== selectedTerm) return false;
        if (!isAllTopics) {
          const tMatch = (u.topic || '').trim().toLowerCase() === normTopic ||
                         (u.original_name || '').trim().toLowerCase().includes(normTopic);
          if (!tMatch) return false;
        }
        return true;
      });
    }
  } catch(e) {}

  // Update slide count badge
  const slideBadge = document.getElementById('dash-slide-badge');
  if (slideBadge) slideBadge.textContent = matchingPPTXs.length;

  // 3. Fetch Questions from DB
  let matchingQuestions = [];
  try {
    await fetchQuestionsFromDB();
    matchingQuestions = (dbQuestions || []).filter(q => {
      if (String(q.grade) !== grade) return false;
      if (!isAllTopics && (q.topic || '').trim().toLowerCase() !== normTopic) return false;
      return true;
    });
  } catch(e) {}

  // A. Render Dedicated Slide Decks Showcase (if view is 'all' or 'slides')
  if (showcaseContainer && (dashboardActiveView === 'all' || dashboardActiveView === 'slides')) {
    if (matchingPPTXs.length > 0) {
      let cardsHtml = matchingPPTXs.map(p => {
        const isPdf = (p.file_type === 'pdf' || (p.original_name && p.original_name.toLowerCase().endsWith('.pdf')));
        const formatLabel = isPdf ? 'PDF' : 'PPTX';
        const badgeClass = isPdf ? 'badge-pdf' : 'badge-pptx';
        const formatIcon = isPdf ? 'fa-file-pdf' : 'fa-file-powerpoint';
        const thumbUrl = `pptx_api.php?action=slide_image&id=${p.id}&slide=1`;
        const titleText = p.topic || p.original_name.replace(/\.(pptx?|pdf)$/i, '');
        const escapedTitle = (titleText || '').replace(/'/g, "\\'");

        return `
          <div class="deck-card">
            <div class="deck-thumb-wrap" onclick="window.location.href='presenter.html?pptx=${p.id}'" title="Click to Present Slide Deck">
              <img class="deck-thumb-img" src="${thumbUrl}" alt="Slide preview" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
              <div style="display:none;width:100%;height:100%;align-items:center;justify-content:center;background:#1e293b;color:var(--text-muted);font-size:32px;">
                <i class="fa-solid ${formatIcon}"></i>
              </div>
              <span class="deck-format-badge ${badgeClass}">
                <i class="fa-solid ${formatIcon}"></i> ${formatLabel}
              </span>
              <span class="deck-overlay-badge">
                <i class="fa-solid fa-layer-group"></i> ${p.slide_count || 1} Slides
              </span>
            </div>
            <div class="deck-content">
              <h3 class="deck-title" title="${escapeHtml(titleText)}">${escapeHtml(titleText)}</h3>
              <div class="deck-meta">
                <span class="deck-meta-tag"><i class="fa-solid fa-graduation-cap text-primary"></i> Grade ${p.grade}</span>
                <span class="deck-meta-tag"><i class="fa-regular fa-calendar text-warning"></i> Term ${p.quarter || '1'}</span>
                <span class="deck-meta-tag text-muted"><i class="fa-regular fa-file"></i> ${escapeHtml(p.original_name)}</span>
              </div>
              <div class="deck-actions">
                <button class="btn btn-primary btn-sm flex-1" onclick="window.location.href='presenter.html?pptx=${p.id}'" title="Present Slides in Full-Screen Presenter Mode">
                  <i class="fa-solid fa-chalkboard-user"></i> Present
                </button>
                <button class="btn btn-secondary btn-sm" onclick="app.openSlidePreview(${p.id})" title="Preview Slides on this Page">
                  <i class="fa-solid fa-eye"></i> Preview
                </button>
                <a href="pptx_api.php?action=pdf_raw&id=${p.id}" target="_blank" class="btn btn-secondary btn-sm" title="Open Original PDF Document">
                  <i class="fa-solid fa-file-pdf"></i> PDF
                </a>
                <button class="btn btn-secondary btn-sm" onclick="app.castSlideToTV(${p.id}, '${escapedTitle}')" title="Broadcast to Classroom TV Display">
                  <i class="fa-solid fa-display"></i> TV
                </button>
              </div>
            </div>
          </div>
        `;
      }).join('');

      showcaseContainer.innerHTML = `
        <div class="glass-card mb-20" style="padding:18px 20px;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
            <div style="display:flex;align-items:center;gap:10px;">
              <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#ef4444,#dc2626);display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;">
                <i class="fa-solid fa-file-pdf"></i>
              </div>
              <div>
                <h2 style="font-size:17px;font-weight:700;margin:0;color:var(--text-main);display:flex;align-items:center;gap:8px;">
                  Official Presentation Slides & PDF Decks
                  <span style="font-size:12px;background:rgba(59,130,246,0.2);color:#60a5fa;border:1px solid rgba(59,130,246,0.3);padding:2px 8px;border-radius:12px;">Grade ${grade}</span>
                </h2>
                <p style="font-size:12px;color:var(--text-muted);margin:2px 0 0 0;">Direct access to converted high-res slide images from the slides directory. Click "Present" to deliver or "Preview" to browse.</p>
              </div>
            </div>
            <span style="font-size:13px;font-weight:600;color:var(--text-muted);background:rgba(255,255,255,0.06);padding:4px 10px;border-radius:8px;">
              <i class="fa-solid fa-layer-group text-primary"></i> ${matchingPPTXs.length} Decks Available
            </span>
          </div>
          <div class="deck-grid">
            ${cardsHtml}
          </div>
        </div>
      `;
    } else if (dashboardActiveView === 'slides') {
      showcaseContainer.innerHTML = `
        <div class="glass-card text-center" style="padding:32px;">
          <i class="fa-solid fa-file-circle-xmark text-muted" style="font-size:36px;margin-bottom:10px;"></i>
          <h3>No slide presentations found for this filter</h3>
          <p class="text-muted" style="font-size:13px;">No PDF or PPTX slides found for Grade ${grade} with current filter settings.</p>
        </div>
      `;
    }
  }

  // If view is 'slides', do not render learning ladder
  if (dashboardActiveView === 'slides') {
    return;
  }

  // B. Render Learning Path Ladder
  let nodeCount = 0;

  // Render curriculum lessons
  if (dashboardActiveView === 'all' || dashboardActiveView === 'lessons') {
    topicLessons.forEach((lesson, idx) => {
      nodeCount++;
      const isCompleted = idx === 0;
      const statusClass = isCompleted ? 'completed' : 'current';
      const contentPreview = lesson.content ? lesson.content.substring(0, 70).replace(/\n/g, ' ') + '...' : `Term ${lesson.quarter}, Lesson ${lesson.lesson_number}`;

      // Check if this lesson has a matched PDF / PPTX slide deck
      const matchedPptx = matchingPPTXs.find(p => 
        p.curriculum_lesson_id == lesson.id ||
        (p.topic && p.topic.trim().toLowerCase() === (lesson.topic || '').trim().toLowerCase())
      );

      let visualDeckHtml = '';
      if (matchedPptx) {
        visualDeckHtml = `
          <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;margin-top:8px;">
            <button class="btn-lesson btn-start" onclick="window.location.href='presenter.html?pptx=${matchedPptx.id}'" style="background:linear-gradient(135deg,var(--primary),var(--secondary));" title="Present Visual Slides (from PDF deck)">
              <i class="fa-solid fa-play"></i> Present Slides (${matchedPptx.slide_count} Slides)
            </button>
            <button class="btn-lesson btn-review" onclick="app.openSlidePreview(${matchedPptx.id})" title="Preview slides on this page">
              <i class="fa-solid fa-eye"></i> Preview
            </button>
            <a href="pptx_api.php?action=pdf_raw&id=${matchedPptx.id}" target="_blank" class="btn-lesson btn-review" style="background:rgba(239,68,68,0.2);color:#ef4444;border-color:rgba(239,68,68,0.4);" title="Open Original PDF Document">
              <i class="fa-solid fa-file-pdf"></i> PDF
            </a>
          </div>
        `;
      } else {
        visualDeckHtml = `
          <div style="margin-top:8px;">
            <button class="btn-lesson btn-start" onclick="window.location.href='presenter.html?id=${lesson.id}'">
              <i class="fa-solid fa-play"></i> Present Lesson
            </button>
          </div>
        `;
      }

      pathContainer.innerHTML += `
        <div class="lesson-node ${statusClass}" style="margin-bottom:12px;">
          <div class="lesson-icon">
            <i class="fa-solid fa-book-open"></i>
          </div>
          <div class="lesson-info" style="flex:1;">
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:4px;">
              <span style="font-size:11px;font-weight:700;color:var(--text-muted);background:rgba(255,255,255,0.06);padding:2px 6px;border-radius:4px;">
                Term ${lesson.quarter} • L${lesson.lesson_number}
              </span>
              ${matchedPptx ? `<span style="font-size:11px;font-weight:700;color:#60a5fa;background:rgba(59,130,246,0.18);border:1px solid rgba(59,130,246,0.3);padding:2px 6px;border-radius:4px;"><i class="fa-solid fa-file-pdf"></i> Visual PDF Slides</span>` : ''}
            </div>
            <h3>${lesson.topic}</h3>
            <p>${contentPreview}</p>
            ${visualDeckHtml}
          </div>
        </div>
      `;
    });
  }

  // Render standalone PPTX uploads (if not already matched to a lesson)
  if (dashboardActiveView === 'all' || dashboardActiveView === 'slides') {
    matchingPPTXs.forEach((pptx) => {
      const alreadyRendered = topicLessons.some(l => 
        l.id == pptx.curriculum_lesson_id ||
        (l.topic && pptx.topic && l.topic.trim().toLowerCase() === pptx.topic.trim().toLowerCase())
      );
      if (!alreadyRendered) {
        nodeCount++;
        const isPdf = (pptx.file_type === 'pdf' || (pptx.original_name && pptx.original_name.toLowerCase().endsWith('.pdf')));
        const formatIcon = isPdf ? 'fa-file-pdf' : 'fa-file-powerpoint';
        const formatBg = isPdf ? 'linear-gradient(135deg,#ef4444,#b91c1c)' : 'linear-gradient(135deg,#c4532e,#e8734a)';

        pathContainer.innerHTML += `
          <div class="lesson-node current" style="margin-bottom:12px;">
            <div class="lesson-icon" style="background:${formatBg};">
              <i class="fa-solid ${formatIcon}" style="color:#fff;"></i>
            </div>
            <div class="lesson-info" style="flex:1;">
              <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:4px;">
                <span style="font-size:11px;font-weight:700;color:var(--text-muted);background:rgba(255,255,255,0.06);padding:2px 6px;border-radius:4px;">
                  Term ${pptx.quarter || '1'} • ${isPdf ? 'PDF Deck' : 'PowerPoint'}
                </span>
                <span style="font-size:11px;font-weight:700;color:#10b981;background:rgba(16,185,129,0.18);border:1px solid rgba(16,185,129,0.3);padding:2px 6px;border-radius:4px;">
                  ${pptx.slide_count || 1} Slides
                </span>
              </div>
              <h3>${escapeHtml(pptx.topic || pptx.original_name)}</h3>
              <p>${escapeHtml(pptx.original_name)}</p>
              <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;margin-top:8px;">
                <button class="btn-lesson btn-start" onclick="window.location.href='presenter.html?pptx=${pptx.id}'" style="background:${formatBg};">
                  <i class="fa-solid fa-play"></i> Present Slides
                </button>
                <button class="btn-lesson btn-review" onclick="app.openSlidePreview(${pptx.id})" title="Preview in page">
                  <i class="fa-solid fa-eye"></i> Preview
                </button>
                <a href="pptx_api.php?action=pdf_raw&id=${pptx.id}" target="_blank" class="btn-lesson btn-review" style="background:rgba(239,68,68,0.2);color:#ef4444;border-color:rgba(239,68,68,0.4);" title="Open PDF">
                  <i class="fa-solid fa-file-pdf"></i> PDF
                </a>
              </div>
            </div>
          </div>
        `;
      }
    });
  }

  // Render Practice Quiz node if questions exist for this topic
  if ((dashboardActiveView === 'all' || dashboardActiveView === 'quizzes') && matchingQuestions.length > 0) {
    nodeCount++;
    pathContainer.innerHTML += `
      <div class="lesson-node completed" style="margin-bottom:12px;">
        <div class="lesson-icon" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
          <i class="fa-solid fa-dumbbell" style="color:#fff;"></i>
        </div>
        <div class="lesson-info" style="flex:1;">
          <h3>Practice Quiz: ${isAllTopics ? `Grade ${grade} Question Bank` : topic}</h3>
          <p>${matchingQuestions.length} validated DepEd assessment questions available for recitation & flash quiz</p>
        </div>
        <button class="btn-lesson btn-review" onclick="window.location.href='assessment.html?grade=${grade}${isAllTopics ? '' : '&topic=' + encodeURIComponent(topic)}'" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
          <i class="fa-solid fa-play"></i> Flash Quiz
        </button>
      </div>
    `;
  }

  if (nodeCount === 0 && matchingPPTXs.length === 0) {
    pathContainer.innerHTML = `
      <div class="lesson-node" style="padding:24px;text-align:center;">
        <i class="fa-solid fa-circle-info" style="font-size:2rem;color:var(--text-muted);margin-bottom:10px;"></i>
        <h3>No materials found for current selection</h3>
        <p style="color:var(--text-muted);margin-top:6px;">Try selecting "All Topics" or switching the Term filter above.</p>
      </div>
    `;
  }
}

// --- Fullscreen Assessment Mode (for student test-taking) ---
function toggleAssessmentFullscreen() {
  const flashScreen = document.getElementById('flash-screen') || document.getElementById('assessment-flash');
  if (!flashScreen) return;

  if (flashScreen.classList.contains('assessment-fullscreen')) {
    flashScreen.classList.remove('assessment-fullscreen');
  } else {
    flashScreen.classList.add('assessment-fullscreen');
    // Also request browser fullscreen API
    if (flashScreen.requestFullscreen) {
      flashScreen.requestFullscreen().catch(() => {});
    } else if (flashScreen.webkitRequestFullscreen) {
      flashScreen.webkitRequestFullscreen();
    }
  }
}

// --- File Export Modal (grade/section/format selection) ---
function showExportModal() {
  const existing = document.getElementById('export-modal');
  if (existing) existing.remove();

  const modal = document.createElement('div');
  modal.id = 'export-modal';
  modal.className = 'export-modal';
  modal.innerHTML = `
    <div class="glass-card export-modal-content">
      <h2 style="margin-bottom:20px;"><i class="fa-solid fa-file-export text-primary"></i> Export Data</h2>
      
      <div class="form-group" style="margin-bottom:14px;">
        <label>Data Type</label>
        <select id="export-data-type" class="form-control">
          <option value="all">All Data (Students + Questions + Records)</option>
          <option value="students">Students Only</option>
          <option value="questions">Questions Only</option>
          <option value="records">Recitation Records Only</option>
        </select>
      </div>
      
      <div class="form-group" style="margin-bottom:14px;">
        <label>Grade Filter</label>
        <select id="export-grade" class="form-control">
          <option value="all">All Grades</option>
          <option value="3">Grade 3</option>
          <option value="4">Grade 4</option>
          <option value="5">Grade 5</option>
          <option value="6">Grade 6</option>
        </select>
      </div>
      
      <div class="form-group" style="margin-bottom:20px;">
        <label>File Format</label>
        <select id="export-format" class="form-control">
          <option value="json">JSON (.json)</option>
          <option value="csv">CSV (.csv)</option>
        </select>
      </div>
      
      <div style="display:flex;gap:12px;justify-content:flex-end;">
        <button class="btn btn-secondary" onclick="document.getElementById('export-modal').remove()">Cancel</button>
        <button class="btn btn-primary" onclick="executeExport()"><i class="fa-solid fa-download"></i> Export & Save</button>
      </div>
    </div>
  `;
  document.body.appendChild(modal);
  modal.addEventListener('click', (e) => { if (e.target === modal) modal.remove(); });
}

function executeExport() {
  const dataType = document.getElementById('export-data-type').value;
  const grade = document.getElementById('export-grade').value;
  const format = document.getElementById('export-format').value;

  const formData = new FormData();
  formData.append('action', 'export');
  formData.append('format', format);
  formData.append('grade', grade);
  formData.append('dataType', dataType);
  formData.append('stateData', JSON.stringify(state));

  fetch('file_manager.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      document.getElementById('export-modal').remove();
      if (data.status === 'success') {
        const files = data.files ? data.files.join(', ') : data.file;
        alert(`✅ Export successful!\nSaved to: savestates/${files}`);
      } else {
        alert('Export failed: ' + data.message);
      }
    })
    .catch(err => {
      // Offline fallback — download as JSON/CSV directly
      document.getElementById('export-modal').remove();
      if (format === 'json') {
        const payload = { students: state.students, exportDate: new Date().toISOString() };
        const blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `ilikesci_${dataType}_${grade}_${new Date().toISOString().split('T')[0]}.json`;
        a.click();
      } else {
        let csv = 'Name,Grade,Recitations,Total Score\n';
        let students = grade === 'all' ? state.students : state.students.filter(s => s.grade === grade);
        students.forEach(s => csv += `"${s.name}","${s.grade}",${s.recitations},${s.totalScore}\n`);
        const blob = new Blob([csv], { type: 'text/csv' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `ilikesci_${dataType}_${grade}_${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
      }
      alert('Exported as direct download (offline mode).');
    });
}

// --- File Import (PDF, PPTX, DOCX, Images, Videos, JSON, CSV) ---
function showImportModal() {
  const existing = document.getElementById('import-modal');
  if (existing) existing.remove();

  const modal = document.createElement('div');
  modal.id = 'import-modal';
  modal.className = 'export-modal';
  modal.innerHTML = `
    <div class="glass-card export-modal-content">
      <h2 style="margin-bottom:20px;"><i class="fa-solid fa-file-import text-primary"></i> Import File</h2>
      <p class="text-muted" style="margin-bottom:16px;">Supported formats: PDF, PPTX, DOCX, Images, Videos, JSON, CSV</p>
      
      <div class="form-group" style="margin-bottom:14px;">
        <label>Assign to Grade</label>
        <select id="import-grade" class="form-control">
          <option value="all">General / All Grades</option>
          <option value="3">Grade 3</option>
          <option value="4">Grade 4</option>
          <option value="5">Grade 5</option>
          <option value="6">Grade 6</option>
        </select>
      </div>
      
      <div class="form-group" style="margin-bottom:14px;">
        <label>Category</label>
        <select id="import-category" class="form-control">
          <option value="general">General</option>
          <option value="lessons">Lessons</option>
          <option value="assessments">Assessments</option>
          <option value="media">Media (Images/Videos)</option>
        </select>
      </div>
      
      <div style="border:2px dashed var(--glass-border);border-radius:12px;padding:30px;text-align:center;margin-bottom:20px;cursor:pointer;" onclick="document.getElementById('import-file-input').click()">
        <i class="fa-solid fa-cloud-arrow-up" style="font-size:40px;color:var(--primary);margin-bottom:10px;display:block;"></i>
        <p style="color:var(--text-muted);">Click to browse or drag a file here</p>
        <p id="import-file-name" style="color:var(--primary);margin-top:8px;font-weight:600;"></p>
        <input type="file" id="import-file-input" class="hidden" accept=".pdf,.pptx,.ppt,.docx,.doc,.jpg,.jpeg,.png,.gif,.mp4,.webm,.avi,.json,.csv" onchange="showSelectedFile(this)">
      </div>
      
      <div style="display:flex;gap:12px;justify-content:flex-end;">
        <button class="btn btn-secondary" onclick="document.getElementById('import-modal').remove()">Cancel</button>
        <button class="btn btn-success" onclick="executeImport()"><i class="fa-solid fa-upload"></i> Import & Save</button>
      </div>
    </div>
  `;
  document.body.appendChild(modal);
  modal.addEventListener('click', (e) => { if (e.target === modal) modal.remove(); });
}

function showSelectedFile(input) {
  const label = document.getElementById('import-file-name');
  if (input.files.length > 0) {
    label.textContent = input.files[0].name + ' (' + (input.files[0].size / 1024).toFixed(1) + ' KB)';
  }
}

function executeImport() {
  const fileInput = document.getElementById('import-file-input');
  if (!fileInput || !fileInput.files.length) {
    alert('Please select a file first.');
    return;
  }

  const file = fileInput.files[0];
  const grade = document.getElementById('import-grade').value;
  const category = document.getElementById('import-category').value;
  const ext = file.name.split('.').pop().toLowerCase();

  const formData = new FormData();
  formData.append('file', file);
  formData.append('grade', grade);

  // JSON/CSV files get imported as data, others get uploaded as media
  if (ext === 'json' || ext === 'csv') {
    formData.append('action', 'import');
  } else {
    formData.append('action', 'upload_media');
    formData.append('category', category);
  }

  fetch('file_manager.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      document.getElementById('import-modal').remove();
      if (data.status === 'success') {
        if (ext === 'json' && data.data) {
          // If it's a full state backup, offer to restore
          if (data.data.students || data.data.state) {
            if (confirm('This file contains ILikeSci data. Merge into current system?')) {
              if (data.data.students) {
                data.data.students.forEach(imported => {
                  if (!state.students.find(s => s.id === imported.id)) {
                    state.students.push(imported);
                  }
                });
              }
              if (data.data.state) {
                // Full state restore
                Object.assign(state, data.data.state);
              }
              saveState();
              alert('✅ Data merged successfully! Reloading...');
              window.location.reload();
              return;
            }
          }
        }
        alert(`✅ File imported successfully!\nSaved as: ${data.savedAs || data.file}`);
      } else {
        alert('Import failed: ' + data.message);
      }
    })
    .catch(err => {
      document.getElementById('import-modal').remove();
      alert('Import error: ' + err.message + '\nMake sure XAMPP MySQL is running.');
    });
}

// --- PPTX Upload & Playback ---
function showPPTXUploadModal() {
  const existing = document.getElementById('pptx-upload-modal');
  if (existing) existing.remove();

  const modal = document.createElement('div');
  modal.id = 'pptx-upload-modal';
  modal.className = 'export-modal';
  modal.innerHTML = `
    <div class="glass-card export-modal-content">
      <h2 style="margin-bottom:20px;"><i class="fa-solid fa-file-powerpoint text-primary"></i> Import Presentation (PPTX / PDF)</h2>
      <p class="text-muted" style="margin-bottom:16px;">Upload a .pptx or .pdf presentation to generate interactive slides for the viewer</p>

      <div class="form-group" style="margin-bottom:14px;">
        <label>Grade Level</label>
        <select id="pptx-grade" class="form-control">
          <option value="">Auto-detect from filename</option>
          <option value="3">Grade 3</option>
          <option value="4">Grade 4</option>
          <option value="5">Grade 5</option>
          <option value="6">Grade 6</option>
        </select>
      </div>

      <div class="form-group" style="margin-bottom:14px;">
        <label>Term</label>
        <select id="pptx-quarter" class="form-control">
          <option value="">Auto-detect from filename</option>
          <option value="1">Term 1</option>
          <option value="2">Term 2</option>
          <option value="3">Term 3</option>
          <option value="4">Term 4</option>
        </select>
      </div>

      <div class="form-group" style="margin-bottom:14px;">
        <label>Topic (optional)</label>
        <input type="text" id="pptx-topic" class="form-control" placeholder="Leave blank to use filename">
      </div>

      <div id="pptx-drop-zone" style="border:2px dashed var(--glass-border);border-radius:12px;padding:30px;text-align:center;margin-bottom:20px;cursor:pointer;" onclick="document.getElementById('pptx-file-input').click()">
        <i class="fa-solid fa-file-arrow-up" style="font-size:40px;color:var(--primary);margin-bottom:10px;display:block;"></i>
        <p style="color:var(--text-muted);">Click to browse or drag .pptx / .pdf presentation files here</p>
        <p id="pptx-file-name" style="color:var(--primary);margin-top:8px;font-weight:600;"></p>
        <input type="file" id="pptx-file-input" class="hidden" accept=".pptx,.pdf" multiple onchange="var n=this.files.length; var sz=0; for(var i=0;i<n;i++) sz+=this.files[i].size; document.getElementById('pptx-file-name').textContent = n ? n+' file(s) — '+(sz/1024/1024).toFixed(1)+' MB total' : ''">
      </div>

      <div id="pptx-upload-progress" class="hidden" style="margin-bottom:16px;">
        <div style="background:rgba(255,255,255,0.08);border-radius:8px;overflow:hidden;height:6px;">
          <div id="pptx-progress-bar" style="height:100%;background:linear-gradient(90deg,var(--primary),var(--accent));width:0%;transition:width 0.3s;"></div>
        </div>
        <p id="pptx-upload-status" style="color:var(--text-muted);font-size:0.85rem;margin-top:6px;text-align:center;">Uploading & converting slides...</p>
      </div>

      <div style="display:flex;gap:12px;justify-content:flex-end;">
        <button class="btn btn-secondary" onclick="document.getElementById('pptx-upload-modal').remove()">Cancel</button>
        <button class="btn btn-success" id="pptx-upload-btn" onclick="app.uploadPPTX()"><i class="fa-solid fa-upload"></i> Upload & Convert Slides</button>
      </div>
    </div>
  `;
  document.body.appendChild(modal);
  modal.addEventListener('click', (e) => { if (e.target === modal) modal.remove(); });
}

async function uploadPPTX() {
  const fileInput = document.getElementById('pptx-file-input');
  if (!fileInput || !fileInput.files.length) {
    alert('Please select .pptx or .pdf presentation file(s) first.');
    return;
  }

  const files = Array.from(fileInput.files).filter(f => {
    const fn = f.name.toLowerCase();
    return fn.endsWith('.pptx') || fn.endsWith('.pdf');
  });
  if (files.length === 0) {
    alert('Please select .pptx or .pdf presentation files.');
    return;
  }

  const grade = document.getElementById('pptx-grade').value;
  const quarter = document.getElementById('pptx-quarter').value;
  const topic = document.getElementById('pptx-topic').value;
  const username = state.currentUser || '';

  const progressDiv = document.getElementById('pptx-upload-progress');
  const progressBar = document.getElementById('pptx-progress-bar');
  const statusText = document.getElementById('pptx-upload-status');
  const uploadBtn = document.getElementById('pptx-upload-btn');

  if (progressDiv) progressDiv.classList.remove('hidden');
  if (uploadBtn) uploadBtn.disabled = true;

  let successCount = 0;
  let failCount = 0;

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    const pct = Math.round(((i) / files.length) * 100);
    if (progressBar) progressBar.style.width = pct + '%';
    if (statusText) statusText.textContent = `Uploading ${i + 1}/${files.length}: ${file.name}...`;

    const formData = new FormData();
    formData.append('pptx', file);
    if (grade) formData.append('grade', grade);
    if (quarter) formData.append('quarter', quarter);
    if (topic && files.length === 1) formData.append('topic', topic);
    formData.append('username', username);

    try {
      const response = await fetch('pptx_api.php', { method: 'POST', body: formData });
      const data = await response.json();
      if (data.status === 'success') successCount++;
      else failCount++;
    } catch (err) {
      console.error('PPTX upload error:', file.name, err);
      failCount++;
    }
  }

  if (progressBar) progressBar.style.width = '100%';
  if (statusText) statusText.textContent = `✅ ${successCount} uploaded` + (failCount ? `, ❌ ${failCount} failed` : '');

  setTimeout(() => {
    const modal = document.getElementById('pptx-upload-modal');
    if (modal) modal.remove();
    loadPPTXList();
    if (typeof refreshAllTopicSelectors === 'function') refreshAllTopicSelectors();
  }, 1500);
}

async function loadPPTXList() {
  const container = document.getElementById('pptx-list');
  if (!container) return;

  container.innerHTML = '<div style="text-align:center;padding:20px;color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Loading presentations...</div>';

  try {
    const response = await fetch('pptx_api.php?action=list');
    const data = await response.json();

    if (data.status === 'success' && data.uploads && data.uploads.length > 0) {
      let uploads = data.uploads;

      // Search filter
      const searchEl = document.getElementById('pptx-search');
      const searchQ = searchEl ? searchEl.value.toLowerCase().trim() : '';
      if (searchQ) {
        uploads = uploads.filter(u =>
          (u.topic || '').toLowerCase().includes(searchQ) ||
          (u.original_name || '').toLowerCase().includes(searchQ) ||
          (u.grade || '').toString().includes(searchQ)
        );
      }

      // Format filter (pptx, pdf, all)
      const formatFilter = (document.getElementById('pptx-format') || {}).value || 'all';
      if (formatFilter !== 'all') {
        uploads = uploads.filter(u => {
          const isPdf = u.file_type === 'pdf' || (u.original_name && u.original_name.toLowerCase().endsWith('.pdf'));
          return formatFilter === 'pdf' ? isPdf : !isPdf;
        });
      }

      // Sort
      const sortBy = (document.getElementById('pptx-sort') || {}).value || 'date';
      const sortDir = typeof getPPTXSortDir === 'function' ? getPPTXSortDir() : 'desc';
      uploads.sort((a, b) => {
        let va, vb;
        switch (sortBy) {
          case 'name': va = (a.topic || a.original_name || '').toLowerCase(); vb = (b.topic || b.original_name || '').toLowerCase(); break;
          case 'grade': va = parseInt(a.grade) || 0; vb = parseInt(b.grade) || 0; break;
          case 'slides': va = parseInt(a.slide_count) || 0; vb = parseInt(b.slide_count) || 0; break;
          default: va = new Date(a.created_at).getTime(); vb = new Date(b.created_at).getTime(); break;
        }
        const cmp = va < vb ? -1 : va > vb ? 1 : 0;
        return sortDir === 'asc' ? cmp : -cmp;
      });

      if (uploads.length === 0) {
        container.innerHTML = `<div style="text-align:center;padding:20px;color:var(--text-muted);">No presentations match current filters.</div>`;
        return;
      }

      container.innerHTML = uploads.map(upload => {
        const gradeLabel = upload.grade ? `Grade ${upload.grade}` : 'No grade';
        const quarterLabel = upload.quarter ? `Term ${upload.quarter}` : '';
        const date = new Date(upload.created_at).toLocaleDateString();
        const slideLabel = upload.slide_count === 1 ? '1 slide' : `${upload.slide_count} slides`;

        const isPdf = upload.file_type === 'pdf' || (upload.original_name && upload.original_name.toLowerCase().endsWith('.pdf'));
        const typeBadge = isPdf
          ? '<span style="font-size:0.7rem;background:rgba(239,68,68,0.2);color:#ef4444;border:1px solid rgba(239,68,68,0.3);padding:2px 6px;border-radius:4px;margin-left:4px;font-weight:700;"><i class="fa-solid fa-file-pdf"></i> PDF</span>'
          : '<span style="font-size:0.7rem;background:rgba(249,115,22,0.2);color:#f97316;border:1px solid rgba(249,115,22,0.3);padding:2px 6px;border-radius:4px;margin-left:4px;font-weight:700;"><i class="fa-solid fa-file-powerpoint"></i> PPTX</span>';

        const iconBg = isPdf ? 'linear-gradient(135deg,#e11d48,#be123c)' : 'linear-gradient(135deg,#d35230,#e8734a)';
        const iconClass = isPdf ? 'fa-solid fa-file-pdf' : 'fa-solid fa-file-powerpoint';

        const modeLabel = upload.has_images == 1 ? '<span style="font-size:0.7rem;background:rgba(6,214,160,0.2);color:#06d6a0;padding:2px 6px;border-radius:4px;margin-left:4px;">Visual</span>' : '<span style="font-size:0.7rem;background:rgba(255,209,102,0.2);color:#ffd166;padding:2px 6px;border-radius:4px;margin-left:4px;">Text</span>';

        return `
          <div class="lesson-item-card" style="margin-bottom:10px;">
            <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
              <div style="width:44px;height:44px;border-radius:10px;background:${iconBg};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="${iconClass}" style="color:#fff;font-size:1.2rem;"></i>
              </div>
              <div style="min-width:0;">
                <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${upload.original_name}">${upload.topic || upload.original_name}${typeBadge}${modeLabel}</div>
                <div style="font-size:0.8rem;color:var(--text-muted);">${gradeLabel} ${quarterLabel} · ${slideLabel} · ${date}</div>
              </div>
            </div>
            <div style="display:flex;gap:8px;flex-shrink:0;">
              <button class="btn btn-primary btn-sm" onclick="window.location.href='presenter.html?pptx=${upload.id}'" title="Present">
                <i class="fa-solid fa-play"></i> Play
              </button>
              <button class="btn btn-secondary btn-sm" onclick="app.deletePPTX(${upload.id}, '${(upload.original_name || '').replace(/'/g, "\\'")}')" title="Delete" style="border-color:rgba(255,80,80,0.3);color:#ff6b6b;">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </div>
        `;
      }).join('');
    } else {
      container.innerHTML = `
        <div style="text-align:center;padding:40px 20px;color:var(--text-muted);">
          <i class="fa-solid fa-file-powerpoint" style="font-size:48px;opacity:0.2;margin-bottom:12px;display:block;"></i>
          <p>No presentations uploaded yet</p>
          <p style="font-size:0.85rem;margin-top:6px;">Click <strong>Import Presentation</strong> or <strong>Sync All PDFs</strong> to get started</p>
        </div>
      `;
    }
  } catch (err) {
    console.error('Failed to load presentations list:', err);
    container.innerHTML = '<div style="text-align:center;padding:20px;color:#ff6b6b;"><i class="fa-solid fa-triangle-exclamation"></i> Could not load presentations. Is XAMPP running?</div>';
  }
}

async function deletePPTX(id, name) {
  if (!confirm(`Delete presentation "${name}"?\n\nThis will also remove the extracted slides from the database.`)) return;

  try {
    const response = await fetch('pptx_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete', id: id })
    });
    const data = await response.json();

    if (data.status === 'success') {
      alert('✅ Presentation deleted.');
      loadPPTXList();
    } else {
      alert('Delete failed: ' + (data.message || 'Unknown error'));
    }
  } catch (err) {
    console.error('Presentation delete error:', err);
    alert('Delete failed: Network error. Is XAMPP running?');
  }
}

async function syncAllCurriculumPDFs() {
  if (!confirm('Scan and sync all DepEd curriculum PDF presentations from the pdfs folder?\n\nThis will ensure all 20 DepEd science slide decks are imported and ready for classroom presentation.')) return;

  const notif = document.createElement('div');
  notif.style = 'position:fixed;bottom:24px;right:24px;background:#1e293b;border:1px solid var(--primary);color:#fff;padding:16px 20px;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;gap:12px;';
  notif.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-primary"></i> Syncing curriculum PDF presentations...';
  document.body.appendChild(notif);

  try {
    const res = await fetch('import_all_curriculum_pdfs.php');
    notif.innerHTML = '<i class="fa-solid fa-circle-check text-success"></i> PDF presentations synchronized successfully!';
    setTimeout(() => notif.remove(), 2500);
    loadPPTXList();
    if (typeof refreshAllTopicSelectors === 'function') refreshAllTopicSelectors();
  } catch (e) {
    notif.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-danger"></i> Sync error: ' + e.message;
    setTimeout(() => notif.remove(), 4000);
  }
}

// ============================================================
// AI INTEGRATION — All functions with offline fallback
// ============================================================

// Check if AI is available (called on page load)
async function checkAIStatus() {
  try {
    const res = await fetch('ai_api.php?action=status');
    const data = await res.json();
    state.aiAvailable = data.ai_enabled === true;
    state.aiProvider = data.provider || '';
    state.aiModel = data.model || '';
    saveState();
    return data;
  } catch (e) {
    state.aiAvailable = false;
    saveState();
    return { ai_enabled: false };
  }
}

// AI Question Generator — falls back to existing DB questions
async function aiGenerateQuestions(grade, topic, count, types) {
  count = count || 5;
  types = types || 'mixed';
  try {
    const res = await fetch('ai_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'generate_questions', grade, topic, count, types })
    });
    const data = await res.json();
    if (data.status === 'success' && Array.isArray(data.questions)) {
      // Cache in localStorage for offline
      const cacheKey = `ai_questions_${grade}_${topic}`;
      localStorage.setItem(cacheKey, JSON.stringify(data.questions));
      return { questions: data.questions, source: data.source || 'ai' };
    }
    throw new Error(data.message || 'AI error');
  } catch (e) {
    console.warn('AI Question Gen fallback:', e.message);
    // Fallback: try localStorage cache
    const cacheKey = `ai_questions_${grade}_${topic}`;
    const cached = localStorage.getItem(cacheKey);
    if (cached) {
      try { return { questions: JSON.parse(cached), source: 'cache' }; } catch(x) {}
    }
    // Fallback: existing DB questions
    await fetchQuestionsFromDB();
    const filtered = dbQuestions.filter(q => q.grade === grade && q.topic === topic);
    return { questions: filtered.map(q => ({ type: 'mc', text: q.text, difficulty: q.difficulty })), source: 'db_fallback' };
  }
}

// AI Topic Explainer — falls back to raw slide text
async function aiExplainTopic(topic, grade, slideText) {
  grade = grade || '4';
  try {
    const res = await fetch('ai_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'explain_topic', topic, grade, slide_text: slideText })
    });
    const data = await res.json();
    if (data.status === 'success') {
      return { explanation: data.explanation, source: data.source || 'ai' };
    }
    throw new Error(data.message || 'AI error');
  } catch (e) {
    console.warn('AI Explain fallback:', e.message);
    return { explanation: slideText || `Review the topic: ${topic}`, source: 'fallback' };
  }
}

// AI Study Hint — falls back to generic hint
async function aiStudyHint(question, topic, wrongAnswer) {
  try {
    const res = await fetch('ai_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'study_hint', question, topic, wrong_answer: wrongAnswer })
    });
    const data = await res.json();
    if (data.status === 'success') {
      return { hint: data.hint, source: data.source || 'ai' };
    }
    throw new Error(data.message || 'AI error');
  } catch (e) {
    console.warn('AI Hint fallback:', e.message);
    return { hint: `💡 Review the topic "${topic || 'this lesson'}" and try again. You can do it!`, source: 'fallback' };
  }
}

// AI Performance Insight — falls back to computed stats
async function aiPerformanceInsight(studentName, scores, quarter, gradeLevel) {
  try {
    const res = await fetch('ai_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'performance_insight', student_name: studentName, scores, quarter, grade_level: gradeLevel })
    });
    const data = await res.json();
    if (data.status === 'success') {
      return { insight: data.insight, source: data.source || 'ai' };
    }
    throw new Error(data.message || 'AI error');
  } catch (e) {
    console.warn('AI Insight fallback:', e.message);
    return { insight: `Performance data available for ${studentName}. View the E-Class Record for detailed scores.`, source: 'fallback' };
  }
}

// AI Question Rephraser — falls back to original question
async function aiRephraseQuestion(question, grade) {
  grade = grade || '4';
  try {
    const res = await fetch('ai_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'rephrase_question', question, grade })
    });
    const data = await res.json();
    if (data.status === 'success') {
      return { rephrased: data.rephrased, source: 'ai' };
    }
    throw new Error(data.message || 'AI error');
  } catch (e) {
    console.warn('AI Rephrase fallback:', e.message);
    return { rephrased: question, source: 'fallback' };
  }
}

// Show AI Question Generator Modal (used on materials.html)
function showAIGenerateModal() {
  const existing = document.getElementById('ai-generate-modal');
  if (existing) existing.remove();

  const gradeVal = document.getElementById('mat-grade') ? document.getElementById('mat-grade').value : '4';
  const topicVal = document.getElementById('mat-topic') ? document.getElementById('mat-topic').value : '';

  const modal = document.createElement('div');
  modal.id = 'ai-generate-modal';
  modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.85);display:flex;align-items:center;justify-content:center;z-index:99999;animation:fadeIn 0.2s ease;';
  modal.innerHTML = `
    <div style="background:var(--bg-card,#1e293b);border:1px solid var(--glass-border,rgba(255,255,255,0.1));border-radius:20px;padding:32px;max-width:520px;width:95%;max-height:85vh;overflow-y:auto;box-shadow:0 25px 60px rgba(0,0,0,0.5);">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="color:var(--text-main,#e2e8f0);font-size:20px;margin:0;"><i class="fa-solid fa-robot" style="color:var(--primary);margin-right:8px;"></i>AI Question Generator</h2>
        <button onclick="document.getElementById('ai-generate-modal').remove()" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div style="display:flex;flex-direction:column;gap:14px;">
        <div>
          <label style="color:var(--text-muted);font-size:13px;margin-bottom:4px;display:block;">Grade Level</label>
          <select id="ai-gen-grade" class="form-control" style="width:100%;">
            <option value="3" ${gradeVal==='3'?'selected':''}>Grade 3</option>
            <option value="4" ${gradeVal==='4'?'selected':''}>Grade 4</option>
            <option value="5" ${gradeVal==='5'?'selected':''}>Grade 5</option>
            <option value="6" ${gradeVal==='6'?'selected':''}>Grade 6</option>
          </select>
        </div>
        <div>
          <label style="color:var(--text-muted);font-size:13px;margin-bottom:4px;display:block;">Topic</label>
          <input id="ai-gen-topic" class="form-control" value="${topicVal}" placeholder="e.g. Matter, Living Things, Weather" style="width:100%;">
        </div>
        <div style="display:flex;gap:10px;">
          <div style="flex:1;">
            <label style="color:var(--text-muted);font-size:13px;margin-bottom:4px;display:block;">Count</label>
            <select id="ai-gen-count" class="form-control" style="width:100%;"><option value="3">3</option><option value="5" selected>5</option><option value="8">8</option><option value="10">10</option></select>
          </div>
          <div style="flex:1;">
            <label style="color:var(--text-muted);font-size:13px;margin-bottom:4px;display:block;">Type</label>
            <select id="ai-gen-type" class="form-control" style="width:100%;"><option value="mixed">Mixed</option><option value="mc">Multiple Choice</option><option value="identification">Identification</option><option value="enumeration">Enumeration</option></select>
          </div>
        </div>
        <button id="ai-gen-btn" class="btn btn-primary" onclick="executeAIGenerate()" style="width:100%;padding:14px;font-size:16px;margin-top:6px;">
          <i class="fa-solid fa-wand-magic-sparkles"></i> Generate Questions
        </button>
        <div id="ai-gen-status" style="display:none;text-align:center;padding:12px;color:var(--text-muted);font-size:14px;"></div>
        <div id="ai-gen-results" style="display:none;"></div>
      </div>
    </div>
  `;
  document.body.appendChild(modal);
  modal.addEventListener('click', (e) => { if (e.target === modal) modal.remove(); });
}

// Execute AI question generation
async function executeAIGenerate() {
  const grade = document.getElementById('ai-gen-grade').value;
  const topic = document.getElementById('ai-gen-topic').value.trim();
  const count = document.getElementById('ai-gen-count').value;
  const types = document.getElementById('ai-gen-type').value;
  const btn = document.getElementById('ai-gen-btn');
  const status = document.getElementById('ai-gen-status');
  const results = document.getElementById('ai-gen-results');

  if (!topic) { alert('Please enter a topic.'); return; }

  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';
  status.style.display = 'block';
  status.innerHTML = '<i class="fa-solid fa-robot"></i> AI is creating questions...';
  results.style.display = 'none';

  const data = await aiGenerateQuestions(grade, topic, parseInt(count), types);
  btn.disabled = false;
  btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Generate Questions';

  if (!data.questions || data.questions.length === 0) {
    status.innerHTML = '⚠️ No questions generated. Try a different topic.';
    return;
  }

  const srcLabel = data.source === 'ai' ? '🤖 AI Generated' : data.source === 'cache' ? '💾 From Cache' : '📦 From Question Bank';
  status.innerHTML = `✅ ${data.questions.length} questions ready — ${srcLabel}`;
  results.style.display = 'block';

  let html = '<div style="margin-top:10px;">';
  data.questions.forEach((q, i) => {
    const typeLabel = q.type === 'mc' ? 'Multiple Choice' : q.type === 'identification' ? 'Identification' : q.type === 'enumeration' ? 'Enumeration' : q.type || 'Question';
    html += `<div style="background:rgba(255,255,255,0.05);border-radius:10px;padding:12px;margin-bottom:8px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
        <span style="font-size:12px;color:var(--text-muted);">#${i+1} · ${typeLabel} · ${q.difficulty || 'Medium'}</span>
        <input type="checkbox" id="ai-q-${i}" checked style="width:18px;height:18px;">
      </div>
      <div style="font-weight:600;font-size:14px;">${q.text || ''}</div>`;
    if (q.options && Array.isArray(q.options)) {
      html += '<div style="margin-top:6px;font-size:13px;color:var(--text-muted);">';
      q.options.forEach((opt, j) => { html += `<div>${String.fromCharCode(65+j)}) ${opt}</div>`; });
      html += '</div>';
    }
    if (q.answer) html += `<div style="margin-top:4px;font-size:12px;color:var(--success,#10b981);">Answer: ${q.answer}</div>`;
    html += '</div>';
  });
  html += `<button class="btn btn-primary" onclick="saveAIQuestions()" style="width:100%;margin-top:10px;padding:12px;">
    <i class="fa-solid fa-floppy-disk"></i> Save Selected to Question Bank
  </button></div>`;
  results.innerHTML = html;

  // Store temp for saving
  window._aiGeneratedQuestions = data.questions;
  window._aiGenGrade = grade;
  window._aiGenTopic = topic;
}

// Save AI-generated questions to database
async function saveAIQuestions() {
  const questions = window._aiGeneratedQuestions || [];
  const grade = window._aiGenGrade || '4';
  const topic = window._aiGenTopic || '';
  let saved = 0;

  for (let i = 0; i < questions.length; i++) {
    const cb = document.getElementById(`ai-q-${i}`);
    if (!cb || !cb.checked) continue;

    const q = questions[i];
    try {
      await fetch('questions_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ grade, topic, difficulty: q.difficulty || 'Medium', text: q.text })
      });
      saved++;
    } catch (e) { console.error('Save question error:', e); }
  }

  alert(`✅ Saved ${saved} questions to the Question Bank!`);
  document.getElementById('ai-generate-modal').remove();
  if (typeof renderQuestionBank === 'function') renderQuestionBank();
}

// Show AI Hint in assessment (after incorrect answer)
async function showAIHint(questionText, topic) {
  const existing = document.getElementById('ai-hint-box');
  if (existing) existing.remove();

  const container = document.getElementById('assessment-flash') || document.querySelector('.flash-controls');
  if (!container) return;

  const hintBox = document.createElement('div');
  hintBox.id = 'ai-hint-box';
  hintBox.style.cssText = 'background:rgba(59,130,246,0.15);border:1px solid rgba(59,130,246,0.3);border-radius:12px;padding:16px;margin-top:12px;animation:fadeIn 0.3s ease;';
  hintBox.innerHTML = '<div style="text-align:center;color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Getting AI hint...</div>';
  container.appendChild(hintBox);

  const data = await aiStudyHint(questionText, topic, '');
  const srcIcon = data.source === 'ai' ? '🤖' : data.source === 'cache' ? '💾' : '💡';
  hintBox.innerHTML = `<div style="font-size:13px;color:var(--text-muted);margin-bottom:6px;">${srcIcon} AI Study Hint</div>
    <div style="font-size:15px;line-height:1.5;color:var(--text-main,#e2e8f0);">${data.hint}</div>`;
}

// Show AI Explain on presenter
async function showAIExplain(topic, grade, slideText) {
  const existing = document.getElementById('ai-explain-panel');
  if (existing) { existing.remove(); return; }

  const panel = document.createElement('div');
  panel.id = 'ai-explain-panel';
  panel.style.cssText = 'position:fixed;bottom:0;right:0;width:380px;max-height:60vh;background:var(--bg-card,#1e293b);border:1px solid var(--glass-border,rgba(255,255,255,0.15));border-radius:16px 0 0 0;padding:20px;overflow-y:auto;z-index:9999;box-shadow:-4px -4px 20px rgba(0,0,0,0.3);animation:fadeIn 0.3s ease;';
  panel.innerHTML = '<div style="text-align:center;color:var(--text-muted);padding:20px;"><i class="fa-solid fa-spinner fa-spin"></i> AI is generating explanation...</div>';
  document.body.appendChild(panel);

  const data = await aiExplainTopic(topic, grade, slideText);
  const srcIcon = data.source === 'ai' ? '🤖' : data.source === 'cache' ? '💾' : '📄';
  panel.innerHTML = `
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
      <span style="font-size:13px;color:var(--text-muted);">${srcIcon} AI Explanation</span>
      <button onclick="document.getElementById('ai-explain-panel').remove()" style="background:none;border:none;color:var(--text-muted);font-size:16px;cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div style="font-size:15px;line-height:1.7;color:var(--text-main,#e2e8f0);white-space:pre-wrap;">${data.explanation}</div>`;
}

// AI Insights for records page
async function showAIInsights() {
  const existing = document.getElementById('ai-insights-modal');
  if (existing) existing.remove();

  const modal = document.createElement('div');
  modal.id = 'ai-insights-modal';
  modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.85);display:flex;align-items:center;justify-content:center;z-index:99999;';
  modal.innerHTML = `<div style="background:var(--bg-card,#1e293b);border-radius:20px;padding:32px;max-width:550px;width:95%;max-height:80vh;overflow-y:auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
      <h2 style="margin:0;font-size:20px;"><i class="fa-solid fa-brain" style="color:var(--primary);"></i> AI Performance Insights</h2>
      <button onclick="document.getElementById('ai-insights-modal').remove()" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div id="ai-insights-content" style="color:var(--text-muted);text-align:center;padding:20px;"><i class="fa-solid fa-spinner fa-spin"></i> Analyzing student data...</div>
  </div>`;
  document.body.appendChild(modal);
  modal.addEventListener('click', (e) => { if (e.target === modal) modal.remove(); });

  // Gather student data
  const scores = state.students.map(s => ({
    name: s.name, grade: s.grade, section: s.section || 'A',
    totalScore: s.totalScore || 0, recitations: s.recitations || 0
  }));

  const data = await aiPerformanceInsight('Class Overview', scores, '', '');
  const srcIcon = data.source === 'ai' ? '🤖' : '📊';
  document.getElementById('ai-insights-content').innerHTML = `
    <div style="font-size:13px;color:var(--text-muted);margin-bottom:10px;">${srcIcon} ${data.source === 'ai' ? 'AI Analysis' : 'Basic Summary'}</div>
    <div style="text-align:left;font-size:15px;line-height:1.7;color:var(--text-main,#e2e8f0);white-space:pre-wrap;">${data.insight}</div>`;
}

// --- Resource Hub: Drag-and-Drop Auto-Classifier & Classroom Materials ---
let localResources = [];

async function loadResourcesList() {
  const container = document.getElementById('resource-hub-list');
  if (!container) return;
  container.innerHTML = '<p style="color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Loading resources...</p>';

  try {
    const res = await fetch('file_manager.php?action=list');
    const data = await res.json();
    if (data.status === 'success') {
      localResources = data.files || [];
      renderResourcesList('all');
    } else {
      container.innerHTML = '<p style="color:var(--danger);">Error loading resource list.</p>';
    }
  } catch (e) {
    container.innerHTML = '<p style="color:var(--danger);">Failed to load resources. Is XAMPP running?</p>';
  }
}

function renderResourcesList(filterCat = 'all') {
  const container = document.getElementById('resource-hub-list');
  if (!container) return;
  container.innerHTML = '';

  if (localResources.length === 0) {
    container.innerHTML = '<p style="text-align:center;color:var(--text-muted);padding:20px;">No uploaded resources yet. Drag and drop files above to start!</p>';
    return;
  }

  let filtered = [...localResources];
  if (filterCat === 'lessons') {
    filtered = filtered.filter(f => {
      const ext = f.name.split('.').pop().toLowerCase();
      return ['pdf', 'docx', 'doc'].includes(ext);
    });
  } else if (filterCat === 'media') {
    filtered = filtered.filter(f => {
      const ext = f.name.split('.').pop().toLowerCase();
      return ['png', 'jpg', 'jpeg', 'gif', 'mp4', 'webm', 'avi'].includes(ext);
    });
  }

  if (filtered.length === 0) {
    container.innerHTML = `<p style="text-align:center;color:var(--text-muted);padding:20px;">No files found in category: "${filterCat}".</p>`;
    return;
  }

  filtered.forEach(f => {
    const ext = f.name.split('.').pop().toLowerCase();
    const sizeKB = (f.size / 1024).toFixed(1);
    
    // Determine category and icon
    let iconClass = 'fa-file';
    let color = 'var(--text-muted)';
    let catLabel = 'Unknown';
    let actionHTML = '';

    if (['pdf', 'docx', 'doc'].includes(ext)) {
      iconClass = ext === 'pdf' ? 'fa-file-pdf' : 'fa-file-word';
      color = ext === 'pdf' ? '#ef4444' : '#3b82f6';
      catLabel = 'Lesson Document';
      actionHTML = `<button class="btn btn-primary btn-sm" onclick="window.open('savestates/${f.name}', '_blank')"><i class="fa-solid fa-eye"></i> View</button>`;
    } else if (['png', 'jpg', 'jpeg', 'gif'].includes(ext)) {
      iconClass = 'fa-file-image';
      color = '#10b981';
      catLabel = 'Image Media';
      actionHTML = `
        <button class="btn btn-primary btn-sm" onclick="window.open('savestates/${f.name}', '_blank')"><i class="fa-solid fa-eye"></i> View</button>
        <button class="btn btn-secondary btn-sm" onclick="app.showMediaOnTVDirect('${f.name}')"><i class="fa-solid fa-tv"></i> Cast TV</button>
      `;
    } else if (['mp4', 'webm', 'avi'].includes(ext)) {
      iconClass = 'fa-file-video';
      color = '#8b5cf6';
      catLabel = 'Video Media';
      actionHTML = `
        <button class="btn btn-primary btn-sm" onclick="window.open('savestates/${f.name}', '_blank')"><i class="fa-solid fa-play"></i> Play</button>
        <button class="btn btn-secondary btn-sm" onclick="app.showVideoOnTVDirect('${f.name}')"><i class="fa-solid fa-tv"></i> Cast TV</button>
      `;
    } else if (['pptx'].includes(ext)) {
      iconClass = 'fa-file-powerpoint';
      color = '#f97316';
      catLabel = 'Presentation';
      actionHTML = `<span class="text-muted" style="font-size:12px;">Auto-imported to Presentations</span>`;
    } else if (['json', 'csv', 'txt'].includes(ext)) {
      iconClass = 'fa-file-code';
      color = '#eab308';
      catLabel = 'Question Bank';
      actionHTML = `<button class="btn btn-success btn-sm" onclick="app.reparseQuestionFile('${f.name}')"><i class="fa-solid fa-file-import"></i> Parse Questions</button>`;
    }

    const item = document.createElement('div');
    item.className = 'glass-card mb-10';
    item.style.padding = '14px';
    item.style.display = 'flex';
    item.style.justifyContent = 'space-between';
    item.style.alignItems = 'center';
    item.style.flexWrap = 'wrap';
    item.style.gap = '10px';

    item.innerHTML = `
      <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:200px;">
        <i class="fa-solid ${iconClass}" style="font-size:28px;color:${color};"></i>
        <div>
          <strong style="word-break:break-all;">${f.name.replace(/^import_\d+_\d+_|^import_\d+_/,'')}</strong>
          <div style="font-size:12px;color:var(--text-muted);">
            Category: <span style="color:var(--primary);font-weight:600;">${catLabel}</span> · ${sizeKB} KB · ${f.date}
          </div>
        </div>
      </div>
      <div style="display:flex;gap:8px;align-items:center;">
        ${actionHTML}
        <button class="btn btn-danger btn-sm" onclick="app.deleteResource('${f.name}')"><i class="fa-solid fa-trash"></i></button>
      </div>
    `;
    container.appendChild(item);
  });
}

function showMediaOnTVDirect(filename) {
  if (!tvWindow || tvWindow.closed) {
    alert("Please open the TV Mode display first using the 'Open TV Mode' button!");
    return;
  }
  sendToTV('showGame', {
    title: 'Visual Aid',
    content: `<div style="text-align:center;"><img src="savestates/${filename}" style="max-width:95%; max-height:85vh; object-fit:contain; border-radius:16px; box-shadow:0 20px 50px rgba(0,0,0,0.5);"></div>`
  });
  alert(`Casting image "${filename}" to TV Screen!`);
}

function showVideoOnTVDirect(filename) {
  if (!tvWindow || tvWindow.closed) {
    alert("Please open the TV Mode display first using the 'Open TV Mode' button!");
    return;
  }
  const videoUrl = 'savestates/' + filename;
  showVideoOnTV(videoUrl);
  alert(`Casting video to TV Screen!`);
}

async function deleteResource(filename) {
  if (!confirm("Are you sure you want to delete this resource?")) return;
  try {
    const res = await fetch('file_manager.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ file: filename })
    });
    const data = await res.json();
    if (data.status === 'success') {
      alert("Resource deleted successfully!");
      loadResourcesList();
    } else {
      alert("Delete failed: " + data.message);
    }
  } catch (e) {
    alert("Error deleting file.");
  }
}

async function reparseQuestionFile(filename) {
  try {
    const res = await fetch(`savestates/${filename}`);
    const blob = await res.blob();
    const file = new File([blob], filename, { type: "text/plain" });
    
    // Switch to materials and parse
    switchView('materials');
    handleImportFile(file);
    alert(`Loaded ${filename} into import preview. Check the Question Bank page!`);
  } catch (e) {
    alert("Failed to read the file for question parsing.");
  }
}

async function handleResourceDrop(files) {
  const statusEl = document.getElementById('resource-upload-status');
  if (!files || files.length === 0) return;

  statusEl.textContent = `⏳ Uploading and classifying ${files.length} file(s)...`;

  const grade = document.getElementById('resource-grade-assign').value;

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    const ext = file.name.split('.').pop().toLowerCase();
    
    statusEl.innerHTML = `⏳ Processing: <strong>${file.name}</strong>...`;

    // 1. Classification & Upload
    if (ext === 'pptx' || ext === 'pdf') {
      // Presentation classification -> Upload to pptx_api.php
      const formData = new FormData();
      formData.append('pptx', file);
      formData.append('grade', grade);
      formData.append('username', state.currentUser || '');
      
      try {
        const res = await fetch('pptx_api.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.status === 'success') {
          statusEl.innerHTML = `✅ Successfully converted <strong>${file.name}</strong> to presentation slides!`;
          if (typeof refreshAllTopicSelectors === 'function') await refreshAllTopicSelectors();
          alert(`Presentation converted successfully!\nWe created a lesson and generated ${data.slide_count || ''} slides.`);
        } else {
          statusEl.innerHTML = `❌ Conversion failed for <strong>${file.name}</strong>: ${data.message}`;
        }
      } catch (e) {
        statusEl.innerHTML = `❌ Error converting <strong>${file.name}</strong>.`;
      }
    } 
    else if (['json', 'csv', 'txt'].includes(ext)) {
      // Question bank classification -> Parse and show import preview
      const formData = new FormData();
      formData.append('action', 'import');
      formData.append('file', file);
      
      try {
        const res = await fetch('file_manager.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.status === 'success') {
          statusEl.innerHTML = `✅ Question source <strong>${file.name}</strong> uploaded. Launching question parser...`;
          switchView('materials');
          handleImportFile(file);
        } else {
          statusEl.innerHTML = `❌ Upload failed for <strong>${file.name}</strong>: ${data.message}`;
        }
      } catch(e) {
        statusEl.innerHTML = `❌ Connection error during upload.`;
      }
    } 
    else {
      // Document or Media classification -> Save to file_manager
      const formData = new FormData();
      formData.append('action', 'import');
      formData.append('file', file);
      
      try {
        const res = await fetch('file_manager.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.status === 'success') {
          let category = 'lesson document';
          if (['png', 'jpg', 'jpeg', 'gif', 'mp4', 'webm', 'avi'].includes(ext)) {
            category = 'multimedia asset';
          }
          statusEl.innerHTML = `✅ File <strong>${file.name}</strong> classified as <strong>${category}</strong> and uploaded!`;
        } else {
          statusEl.innerHTML = `❌ Upload failed: ${data.message}`;
        }
      } catch (e) {
        statusEl.innerHTML = `❌ Connection error.`;
      }
    }
  }

  setTimeout(() => {
    statusEl.textContent = '';
    loadResourcesList();
  }, 2000);
}

// --- Interactive Student Picker Spin Wheel ---
let wheelRotation = 0;

const wheelSpinnerState = {
  grade: '4',
  section: 'all',
  filterMode: 'all', // 'all', 'batch_10', 'batch_15', 'uncalled', 'low_rec'
  batchSeed: 1,
  excludedIds: new Set(),
  candidates: [],
  isSpinning: false,
  winner: null
};

function formatWheelStudentName(name, sliceCount = 30) {
  if (!name) return 'Student';
  name = name.trim();
  
  if (name.includes(',')) {
    const parts = name.split(',').map(p => p.trim()).filter(Boolean);
    const lastName = parts[0] || '';
    const firstName = parts[1] || '';
    const firstWord = firstName.split(' ')[0] || firstName;
    const lastInitial = lastName ? lastName.charAt(0) + '.' : '';
    
    if (sliceCount <= 10) {
      const display = `${firstName} ${lastName.charAt(0)}.`;
      return display.length > 20 ? display.substring(0, 19) + '…' : display;
    } else if (sliceCount <= 16) {
      const display = `${firstWord} ${lastInitial}`;
      return display.length > 15 ? display.substring(0, 14) + '…' : display;
    } else {
      const display = `${firstWord} ${lastInitial}`;
      return display.length > 13 ? display.substring(0, 12) + '…' : display;
    }
  }
  
  const words = name.split(' ').filter(Boolean);
  if (words.length > 1) {
    if (sliceCount <= 10) return name.length > 20 ? name.substring(0, 19) + '…' : name;
    return `${words[0]} ${words[words.length - 1].charAt(0)}.`;
  }
  return name.length > 12 ? name.substring(0, 11) + '…' : name;
}

function getWheelFontSize(sliceCount) {
  if (sliceCount <= 8) return 'bold 15px Outfit, sans-serif';
  if (sliceCount <= 12) return 'bold 13.5px Outfit, sans-serif';
  if (sliceCount <= 18) return 'bold 12px Outfit, sans-serif';
  if (sliceCount <= 26) return 'bold 10.5px Outfit, sans-serif';
  if (sliceCount <= 36) return 'bold 9.5px Outfit, sans-serif';
  return 'bold 8.5px Outfit, sans-serif';
}

function shuffleArrayWithSeed(arr, seed = 1) {
  const copy = [...arr];
  let m = copy.length, t, i;
  let s = Math.abs(seed * 9301 + 49297) % 233280;
  while (m) {
    s = (s * 9301 + 49297) % 233280;
    i = Math.floor((s / 233280) * m--);
    t = copy[m];
    copy[m] = copy[i];
    copy[i] = t;
  }
  return copy;
}

function computeWheelCandidates() {
  const grade = wheelSpinnerState.grade;
  const section = wheelSpinnerState.section;
  const filterMode = wheelSpinnerState.filterMode;

  let pool = (state.students || []).filter(s => String(s.grade) === String(grade));
  if (section && section !== 'all') {
    pool = pool.filter(s => (s.section || '').trim().toLowerCase() === section.trim().toLowerCase());
  }

  // Filter out any manually excluded learners
  pool = pool.filter(s => !wheelSpinnerState.excludedIds.has(Number(s.id)));

  if (filterMode === 'uncalled') {
    const uncalled = pool.filter(s => (Number(s.recitations) || 0) === 0);
    pool = uncalled.length > 0 ? uncalled : pool;
  } else if (filterMode === 'low_rec') {
    const low = pool.filter(s => (Number(s.recitations) || 0) < 3);
    pool = low.length > 0 ? low : pool;
  } else if (filterMode === 'batch_10') {
    if (pool.length > 10) {
      pool = shuffleArrayWithSeed(pool, wheelSpinnerState.batchSeed).slice(0, 10);
    }
  } else if (filterMode === 'batch_15') {
    if (pool.length > 15) {
      pool = shuffleArrayWithSeed(pool, wheelSpinnerState.batchSeed).slice(0, 15);
    }
  }

  wheelSpinnerState.candidates = pool;
  return pool;
}

function openStudentSpinner() {
  const gradeEl = document.getElementById('assess-grade');
  const sectionEl = document.getElementById('assess-section');

  const currentGrade = String(gradeEl ? gradeEl.value : (state.assessment.grade || '4'));
  state.assessment.grade = currentGrade;

  let currentSection = sectionEl ? sectionEl.value : 'all';

  const availableSections = getAvailableSections(currentGrade);

  wheelSpinnerState.grade = currentGrade;
  wheelSpinnerState.section = currentSection;
  wheelSpinnerState.filterMode = 'all';
  wheelSpinnerState.batchSeed = Math.floor(Math.random() * 10000) + 1;
  wheelSpinnerState.excludedIds.clear();
  wheelSpinnerState.wheelRotation = 0;
  wheelSpinnerState.isSpinning = false;
  wheelSpinnerState.winner = null;

  const candidates = computeWheelCandidates();

  if (candidates.length === 0) {
    alert(`No students found for Grade ${currentGrade} ${currentSection !== 'all' ? 'Section ' + currentSection : ''}. Please add students or select a different section.`);
    return;
  }

  const existing = document.getElementById('spinner-modal');
  if (existing) existing.remove();

  const modal = document.createElement('div');
  modal.id = 'spinner-modal';
  modal.style.position = 'fixed';
  modal.style.top = '0';
  modal.style.left = '0';
  modal.style.width = '100vw';
  modal.style.height = '100vh';
  modal.style.background = 'rgba(15, 23, 42, 0.88)';
  modal.style.backdropFilter = 'blur(12px)';
  modal.style.display = 'flex';
  modal.style.flexDirection = 'column';
  modal.style.alignItems = 'center';
  modal.style.justifyContent = 'center';
  modal.style.zIndex = '99999';
  modal.style.color = '#fff';
  modal.style.padding = '16px';

  const sectionOptionsHTML = `
    <option value="all" ${currentSection === 'all' ? 'selected' : ''}>All Sections</option>
    ${availableSections.map(sec => {
      const count = (state.students || []).filter(s => String(s.grade) === currentGrade && (s.section || '').trim().toLowerCase() === sec.trim().toLowerCase()).length;
      const isSel = currentSection.trim().toLowerCase() === sec.trim().toLowerCase() ? 'selected' : '';
      return `<option value="${sec}" ${isSel}>Section ${sec} (${count})</option>`;
    }).join('')}
  `;

  modal.innerHTML = `
    <div class="glass-card" style="width:100%; max-width:540px; max-height:94vh; overflow-y:auto; padding:22px 20px; text-align:center; position:relative; box-shadow:0 25px 60px rgba(0,0,0,0.65); border:1px solid rgba(255,255,255,0.18); background:rgba(15, 23, 42, 0.96); border-radius:20px;">
      <button onclick="document.getElementById('spinner-modal').remove()" style="position:absolute; top:16px; right:16px; background:rgba(255,255,255,0.1); border:none; color:#fff; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:18px; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.4)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
        <i class="fa-solid fa-xmark"></i>
      </button>

      <h3 style="margin:0 0 4px 0; font-size:20px; font-weight:800; display:flex; align-items:center; justify-content:center; gap:8px;">
        <i class="fa-solid fa-dharmachakra text-primary pulse"></i> Recitation Wheel Spinner
      </h3>
      <p style="margin:0 0 12px 0; font-size:12px; color:#94a3b8;">Grade ${currentGrade} Science — DepEd Classroom Randomizer</p>
      
      <!-- Interactive Section & Cramming Prevention Filter Bar -->
      <div style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); border-radius:14px; padding:12px 14px; margin-bottom:14px;">
        <div style="display:flex; gap:10px; flex-wrap:wrap; justify-content:center; align-items:center;">
          <div style="flex:1; min-width:140px; text-align:left;">
            <label style="font-size:11px; font-weight:700; color:#94a3b8; display:block; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.5px;">
              <i class="fa-solid fa-users text-primary"></i> Section
            </label>
            <select id="wheel-section-filter" class="form-control" style="width:100%; background:#1e293b; color:#fff; border:1px solid rgba(255,255,255,0.25); font-size:13px; font-weight:600; padding:6px 10px; border-radius:8px;" onchange="app.onWheelSectionChange(this.value)">
              ${sectionOptionsHTML}
            </select>
          </div>

          <div style="flex:1.4; min-width:170px; text-align:left;">
            <label style="font-size:11px; font-weight:700; color:#94a3b8; display:block; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.5px;">
              <i class="fa-solid fa-filter text-primary"></i> Wheel Scope (Uncram)
            </label>
            <select id="wheel-candidate-filter" class="form-control" style="width:100%; background:#1e293b; color:#fff; border:1px solid rgba(255,255,255,0.25); font-size:13px; font-weight:600; padding:6px 10px; border-radius:8px;" onchange="app.onWheelFilterModeChange(this.value)">
              <option value="all">Full Section (All Learners)</option>
              <option value="batch_10">✨ Spacious 10 Candidates (No Cramming!)</option>
              <option value="batch_15">✨ Spacious 15 Candidates</option>
              <option value="uncalled">🎯 Fairness: 0 Recitations Only</option>
              <option value="low_rec">📈 Needs Practice (&lt; 3 Recitations)</option>
            </select>
          </div>

          <button id="wheel-shuffle-btn" class="btn btn-secondary btn-sm" onclick="app.shuffleWheelBatch()" title="Shuffle random batch candidates" style="display:none; height:34px; margin-top:18px; padding:0 12px; font-size:12px; font-weight:600;">
            <i class="fa-solid fa-shuffle"></i> Reshuffle
          </button>
        </div>

        <div id="wheel-candidate-badge" style="font-size:12px; font-weight:700; color:#38bdf8; margin-top:10px; display:flex; align-items:center; justify-content:center; gap:6px;">
          <i class="fa-solid fa-user-check"></i> Showing ${candidates.length} Candidate${candidates.length === 1 ? '' : 's'} (${currentSection === 'all' ? 'All Sections' : 'Section ' + currentSection})
        </div>
      </div>

      <!-- Wheel Canvas Area -->
      <div style="position:relative; width:380px; max-width:88vw; height:380px; max-height:88vw; margin:0 auto 16px auto;">
        <!-- Pointer Needle -->
        <div style="position:absolute; top:-14px; left:50%; transform:translateX(-50%); width:0; height:0; border-left:14px solid transparent; border-right:14px solid transparent; border-top:28px solid #ef4444; filter:drop-shadow(0 4px 6px rgba(0,0,0,0.7)); z-index:20;"></div>
        <canvas id="wheel-canvas" width="420" height="420" style="width:100%; height:100%; border-radius:50%; box-shadow:0 14px 40px rgba(0,0,0,0.65); display:block;"></canvas>
      </div>

      <div style="display:flex; justify-content:center; gap:10px; align-items:center;">
        <button id="spin-btn" class="btn btn-success btn-lg" style="padding:12px 42px; font-size:18px; font-weight:800; border-radius:30px; letter-spacing:1px; box-shadow:0 8px 24px rgba(16,185,129,0.45); cursor:pointer;" onclick="app.spinTheStudentWheel()">
          <i class="fa-solid fa-play"></i> SPIN THE WHEEL
        </button>
      </div>

      <!-- Winner Display Banner -->
      <div id="winner-display" style="margin-top:12px; min-height:44px;"></div>
    </div>
  `;

  document.body.appendChild(modal);

  wheelRotation = 0;
  drawWheel(candidates);
}

function drawWheel(candidates) {
  const canvas = document.getElementById('wheel-canvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const size = (candidates || []).length;

  ctx.clearRect(0, 0, 420, 420);

  if (size === 0) {
    ctx.save();
    ctx.beginPath();
    ctx.arc(210, 210, 205, 0, 2 * Math.PI);
    ctx.fillStyle = '#1e293b';
    ctx.fill();
    ctx.strokeStyle = 'rgba(255,255,255,0.2)';
    ctx.lineWidth = 3;
    ctx.stroke();
    ctx.fillStyle = '#94a3b8';
    ctx.font = 'bold 14px Outfit, sans-serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('No learners match this filter', 210, 210);
    ctx.restore();
    return;
  }

  const arcSize = (2 * Math.PI) / size;

  ctx.save();
  ctx.translate(210, 210);
  ctx.rotate(wheelRotation);

  const colors = [
    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', 
    '#8b5cf6', '#06b6d4', '#ec4899', '#14b8a6',
    '#f97316', '#6366f1', '#84cc16', '#a855f7'
  ];

  const fontSize = getWheelFontSize(size);

  for (let i = 0; i < size; i++) {
    const startAngle = i * arcSize;
    const endAngle = startAngle + arcSize;

    ctx.beginPath();
    ctx.moveTo(0, 0);
    ctx.arc(0, 0, 206, startAngle, endAngle);
    ctx.fillStyle = colors[i % colors.length];
    ctx.fill();
    ctx.lineWidth = size > 25 ? 1.5 : 2.5;
    ctx.strokeStyle = 'rgba(255,255,255,0.5)';
    ctx.stroke();

    ctx.save();
    ctx.rotate(startAngle + arcSize / 2);
    ctx.textAlign = 'right';
    ctx.textBaseline = 'middle';
    ctx.fillStyle = '#ffffff';
    ctx.font = fontSize;
    ctx.shadowColor = 'rgba(0,0,0,0.5)';
    ctx.shadowBlur = 4;

    const displayName = formatWheelStudentName(candidates[i].name, size);
    ctx.fillText(displayName, 192, 0, 142);
    ctx.restore();
  }

  ctx.restore();

  // Draw inner hub/pin
  ctx.beginPath();
  ctx.arc(210, 210, 32, 0, 2 * Math.PI);
  const hubGrad = ctx.createRadialGradient(210, 210, 5, 210, 210, 32);
  hubGrad.addColorStop(0, '#334155');
  hubGrad.addColorStop(1, '#0f172a');
  ctx.fillStyle = hubGrad;
  ctx.fill();
  ctx.lineWidth = 3;
  ctx.strokeStyle = '#38bdf8';
  ctx.stroke();

  // Hub label
  ctx.fillStyle = '#ffffff';
  ctx.font = 'bold 11px Outfit, sans-serif';
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  ctx.fillText('🎯 SPIN', 210, 210);
}

function onWheelSectionChange(sectionVal) {
  wheelSpinnerState.section = sectionVal;
  
  const assessSec = document.getElementById('assess-section');
  if (assessSec) {
    assessSec.value = sectionVal;
    renderAssessmentStudents();
  }

  const candidates = computeWheelCandidates();
  updateWheelBadgeAndControls(candidates);
  wheelRotation = 0;
  drawWheel(candidates);
}

function onWheelFilterModeChange(modeVal) {
  wheelSpinnerState.filterMode = modeVal;
  
  const shuffleBtn = document.getElementById('wheel-shuffle-btn');
  if (shuffleBtn) {
    shuffleBtn.style.display = (modeVal === 'batch_10' || modeVal === 'batch_15') ? 'inline-block' : 'none';
  }

  const candidates = computeWheelCandidates();
  updateWheelBadgeAndControls(candidates);
  wheelRotation = 0;
  drawWheel(candidates);
}

function shuffleWheelBatch() {
  wheelSpinnerState.batchSeed = Math.floor(Math.random() * 10000) + 1;
  const candidates = computeWheelCandidates();
  updateWheelBadgeAndControls(candidates);
  wheelRotation = 0;
  drawWheel(candidates);
  if (typeof playChime === 'function') playChime('coin');
}

function updateWheelBadgeAndControls(candidates) {
  const badge = document.getElementById('wheel-candidate-badge');
  const spinBtn = document.getElementById('spin-btn');
  const winnerDisplay = document.getElementById('winner-display');
  if (winnerDisplay) winnerDisplay.innerHTML = '';

  const secText = wheelSpinnerState.section === 'all' ? 'All Sections' : `Section ${wheelSpinnerState.section}`;
  let modeText = '';
  if (wheelSpinnerState.filterMode === 'batch_10') modeText = ' — 10 Spacious Candidates';
  if (wheelSpinnerState.filterMode === 'batch_15') modeText = ' — 15 Spacious Candidates';
  if (wheelSpinnerState.filterMode === 'uncalled') modeText = ' — 0 Recitations Only';
  if (wheelSpinnerState.filterMode === 'low_rec') modeText = ' — <3 Recitations Only';

  if (badge) {
    badge.innerHTML = `<i class="fa-solid fa-user-check"></i> Showing ${candidates.length} Candidate${candidates.length === 1 ? '' : 's'} (${secText}${modeText})`;
  }

  if (spinBtn) {
    spinBtn.disabled = candidates.length === 0;
  }
}

function spinTheStudentWheel() {
  if (wheelSpinnerState.isSpinning) return;

  const candidates = wheelSpinnerState.candidates;
  const size = (candidates || []).length;
  if (size === 0) {
    alert('No candidates available to spin!');
    return;
  }

  const spinBtn = document.getElementById('spin-btn');
  if (spinBtn) {
    spinBtn.disabled = true;
    spinBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> SPINNING...';
  }

  wheelSpinnerState.isSpinning = true;

  const winnerIndex = Math.floor(Math.random() * size);
  const winner = candidates[winnerIndex];
  wheelSpinnerState.winner = winner;

  const arcSize = (2 * Math.PI) / size;
  const targetSegmentAngle = (winnerIndex + 0.5) * arcSize;
  const finalAngleOffset = (1.5 * Math.PI) - targetSegmentAngle;

  const baseSpins = (6 + Math.floor(Math.random() * 3)) * 2 * Math.PI;
  const totalRotationGoal = baseSpins + finalAngleOffset;

  if (tvWindow && !tvWindow.closed) {
    sendToTV('spinStudentWheel', { 
      students: candidates.map(s => s.name), 
      winnerName: winner.name, 
      duration: 4000 
    });
  }

  const duration = 4000;
  const start = performance.now();
  let lastSoundTickIndex = -1;

  function animateSpin(timestamp) {
    const elapsed = timestamp - start;
    const progress = Math.min(elapsed / duration, 1);

    const easedProgress = 1 - Math.pow(1 - progress, 3);
    wheelRotation = easedProgress * totalRotationGoal;

    const currentRot = wheelRotation % (2 * Math.PI);
    const tickIndex = Math.floor(currentRot / arcSize);
    if (tickIndex !== lastSoundTickIndex && progress < 0.95) {
      lastSoundTickIndex = tickIndex;
      if (typeof playChime === 'function') playChime('tick');
    }

    drawWheel(candidates);

    if (progress < 1) {
      requestAnimationFrame(animateSpin);
    } else {
      wheelSpinnerState.isSpinning = false;
      if (typeof playChime === 'function') playChime('fanfare');

      const select = document.getElementById('assess-student');
      if (select) {
        select.value = String(winner.id);
        state.assessment.studentId = Number(winner.id);
        const insightText = document.getElementById('participation-text');
        if (insightText) {
          const recs = Number(winner.recitations) || 0;
          const secTag = winner.section ? ` [${winner.section}]` : '';
          insightText.innerHTML = `Selected: <strong>${winner.name}${secTag}</strong> (${recs} participations so far)`;
        }
      }

      if (spinBtn) {
        spinBtn.disabled = false;
        spinBtn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> SPIN AGAIN';
      }

      const winnerDisplay = document.getElementById('winner-display');
      if (winnerDisplay) {
        const secDisplay = winner.section ? `Section ${winner.section}` : '';
        const recs = Number(winner.recitations) || 0;
        winnerDisplay.innerHTML = `
          <div style="background:rgba(59,130,246,0.18); border:1px solid rgba(59,130,246,0.4); border-radius:14px; padding:12px 14px; margin-top:8px; animation:pulse 0.6s ease;">
            <div style="font-size:11px; font-weight:800; color:#38bdf8; text-transform:uppercase; letter-spacing:0.8px;">
              🎯 Selected Learner
            </div>
            <div style="font-size:20px; font-weight:800; color:#ffd166; margin:4px 0;">
              ${winner.name}
            </div>
            <div style="font-size:12px; color:rgba(255,255,255,0.85); margin-bottom:10px;">
              Grade ${winner.grade} — ${secDisplay} | Recitations: <strong>${recs}</strong> (${winner.totalScore || 0} pts)
            </div>
            <div style="display:flex; gap:8px; justify-content:center; flex-wrap:wrap;">
              <button class="btn btn-primary btn-sm" onclick="app.selectWinnerForAssessment(${winner.id})">
                <i class="fa-solid fa-play"></i> Start Flash Quiz
              </button>
              <button class="btn btn-secondary btn-sm" onclick="app.spinTheStudentWheel()">
                <i class="fa-solid fa-rotate-right"></i> Spin Again
              </button>
              <button class="btn btn-secondary btn-sm" onclick="app.excludeWinnerAndSpinAgain(${winner.id})" title="Exclude this student from subsequent spins this session">
                <i class="fa-solid fa-user-minus"></i> Exclude for Next Spin
              </button>
            </div>
          </div>
        `;
      }
    }
  }

  requestAnimationFrame(animateSpin);
}

function selectWinnerForAssessment(studentId) {
  state.assessment.studentId = Number(studentId);
  const select = document.getElementById('assess-student');
  if (select) select.value = String(studentId);

  const modal = document.getElementById('spinner-modal');
  if (modal) modal.remove();

  if (typeof startQuizFlash === 'function') {
    startQuizFlash();
  }
}

function excludeWinnerAndSpinAgain(studentId) {
  wheelSpinnerState.excludedIds.add(Number(studentId));
  const candidates = computeWheelCandidates();
  updateWheelBadgeAndControls(candidates);
  wheelRotation = 0;
  drawWheel(candidates);

  if (candidates.length > 0) {
    spinTheStudentWheel();
  } else {
    alert('All candidates in this group have participated!');
  }
}

window.app = {
  updateDashboardTopics,
  renderDashboardLessons,
  switchView,
  renderStudents,
  renderRecords,
  toggleAddStudent,
  addStudent,
  removeStudent,
  updateMaterialTopics,
  promptNewTopic,
  addMaterialQuestion,
  renderQuestionBank,
  removeQuestion: deleteQuestion,
  updateAssessmentTopics,
  setDifficulty,
  startQuizFlash,
  cancelQuiz,
  recordQuizResult,
  handleLogin,
  handleLogout,
  syncToCloud,
  restoreFromCloud,
  syncStudentsFromDB,
  suggestStudent,
  openCamera,
  closeCamera,
  capturePhoto,
  handleImageUpload,
  setBorder,
  updateProfile,
  saveProfile,
  launchGame,
  closeGame,
  awardGamePoints,
  renderScoreboard,
  refreshScoreboard,
  toggleFullscreen,
  suggestNextStudent,
  updateLessonTopics,
  loadLessonContent,
  displayLessonOnTV,
  nextSlide,
  previousSlide,
  closeLesson,
  startLessonRecitation,
  toggleLessonFullscreen,
  toggleGameFullscreen,
  renderAdminUsers,
  addTeacherAccount,
  removeTeacherAccount,
  generateReport,
  openTVDisplay,
  closeTVDisplay,
  showQuestionOnTV,
  showTimerOnTV,
  updateTimerOnTV,
  showGroupingOnTV,
  showGameOnTV,
  showWelcomeOnTV,
  toggleTVFullscreen,
  showVideoOnTV,
  setupGlobalEscapeHandler,
  saveSystemSettings,
  openEditQuestionModal,
  closeEditQuestionModal,
  saveEditedQuestion,
  editQuestion,
  deleteQuestion,
  exportQuestionsCSV,
  exportData,
  importData,
  handleImportFile,
  toggleImportQ,
  setImportDiff,
  toggleAllImport,
  importSelectedQuestions,
  clearImportPreview,
  toggleTheme,
  toggleSidebar,
  toggleOptionsMenu,
  closeOptionsMenu,
  toggleSound,
  toggleMusic,
  togglePerformance,
  oyoEasterEgg,
  restartApp,
  quitApp,
  applyProfileUI,
  promptNewTopic,
  addMaterialQuestion,
  addStudent,
  startStudentPhoto,
  openStudentPhotoModal,
  closeStudentPhotoModal,
  openStudentCamera,
  stopStudentCamera,
  captureStudentPhoto,
  handleStudentPhotoUpload,
  removeStudentPhoto,
  capturePhoto,
  openCamera,
  closeCamera,
  recordRubricResult,
  showRubricOnTV: function() {
    if (tvWindow && !tvWindow.closed) {
      tvWindow.postMessage({ type: 'showRubric' }, '*');
    } else {
      alert('Open TV Display first');
    }
  },
  addBuilderSlide,
  autoFillTestLesson,
  saveCustomLesson,
  renderCustomLessonsList,
  presentCurriculumLessonDirect,
  editCurriculumLessonDirect,
  deleteCurriculumLessonDirect,
  // New functions
  toggleAssessmentFullscreen,
  showExportModal,
  showImportModal,
  showLogoutModal,
  transmute,
  getDepEdLevel,
  // Admin Panel (DB-driven)
  loadAdminStats,
  loadAdminUsers,
  adminCreateUser,
  adminCreateStudent,
  // PPTX & PDF Presentation Upload & Playback
  showPPTXUploadModal,
  uploadPPTX,
  loadPPTXList,
  deletePPTX,
  syncAllCurriculumPDFs,
  openSlidePreview,
  renderCurrentPreviewSlide,
  closeSlidePreview,
  previewNextSlide,
  previewPrevSlide,
  previewGoToSlide,
  previewCastCurrentToTV,
  castSlideToTV,
  filterDashboardView,
  autoGenerateDemoQuestions,
  adminEditUser,
  adminSaveUser,
  adminDeleteUser,
  downloadEClassRecord,
  previewEClassRecord,
  // AI Integration
  checkAIStatus,
  aiGenerateQuestions,
  aiExplainTopic,
  aiStudyHint,
  aiPerformanceInsight,
  aiRephraseQuestion,
  showAIGenerateModal,
  executeAIGenerate,
  saveAIQuestions,
  showAIHint,
  showAIExplain,
  showAIInsights,
  loadResourcesList,
  renderResourcesList,
  handleResourceDrop,
  deleteResource,
  reparseQuestionFile,
  showMediaOnTVDirect,
  showVideoOnTVDirect,
  generateDynamicGroupings,
  openStudentSpinner,
  spinTheStudentWheel,
  onWheelSectionChange,
  onWheelFilterModeChange,
  shuffleWheelBatch,
  selectWinnerForAssessment,
  excludeWinnerAndSpinAgain,
  quickModifyPoints,
  playChime,
  speakText,
  playCurriculumMidi,
  stopCurriculumMidi,
  bulkAddStudents,
  resetQuarterRecitations,
  seedScienceQuestions,
  updateSectionDropdown,
  getAvailableSections,
  updateAssessmentGrade,
  renderAssessmentStudents,
  getAssessmentStudentPool,
  onAdminEClassGradeChange
};
