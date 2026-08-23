/**
 * ILikeSci - Mobile Optimizations
 * Touch handling, responsive behavior, and mobile-specific features
 */

// Mobile Detection
const isMobileDevice = () => {
  return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
         (window.innerWidth <= 768);
};

const isTouchDevice = () => {
  return (('ontouchstart' in window) ||
          (navigator.maxTouchPoints > 0) ||
          (navigator.msMaxTouchPoints > 0));
};

// Initialize mobile optimizations
function initializeMobileOptimizations() {
  if (!isMobileDevice() && !isTouchDevice()) {
    return; // Skip if not mobile
  }

  // 1. Add touch-friendly viewport settings
  updateViewportMeta();

  // 2. Setup touch event handlers
  setupTouchHandlers();

  // 3. Optimize for keyboard in mobile
  optimizeMobileKeyboard();

  // 4. Handle orientation changes
  handleOrientationChanges();

  // 5. Optimize modals for mobile
  optimizeModalsForMobile();

  // 6. Improve form inputs on mobile
  optimizeMobileForms();

  // 7. Add safe area support
  applySafeAreaSupport();

  // 8. Setup swipe handlers
  setupSwipeHandlers();

  console.log('Mobile optimizations initialized');
}

// 1. Update Viewport Meta
function updateViewportMeta() {
  let viewport = document.querySelector('meta[name="viewport"]');
  
  if (!viewport) {
    viewport = document.createElement('meta');
    viewport.name = 'viewport';
    document.head.appendChild(viewport);
  }

  viewport.setAttribute('content',
    'width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover'
  );

  // Add apple-specific meta tags
  const webApp = document.createElement('meta');
  webApp.name = 'apple-mobile-web-app-capable';
  webApp.content = 'yes';
  document.head.appendChild(webApp);

  const statusBar = document.createElement('meta');
  statusBar.name = 'apple-mobile-web-app-status-bar-style';
  statusBar.content = 'black-translucent';
  document.head.appendChild(statusBar);

  const appName = document.createElement('meta');
  appName.name = 'apple-mobile-web-app-title';
  appName.content = 'ILikeSci';
  document.head.appendChild(appName);

  // Add theme color
  const themeColor = document.querySelector('meta[name="theme-color"]');
  if (!themeColor) {
    const theme = document.createElement('meta');
    theme.name = 'theme-color';
    theme.content = '#0f172a';
    document.head.appendChild(theme);
  }
}

// 2. Touch Event Handlers
function setupTouchHandlers() {
  // Add touch feedback to buttons
  document.addEventListener('touchstart', function(e) {
    const target = e.target.closest('button, [role="button"], a, input[type="button"]');
    if (target) {
      target.style.opacity = '0.8';
      target.style.transform = 'scale(0.98)';
    }
  }, true);

  document.addEventListener('touchend', function(e) {
    const target = e.target.closest('button, [role="button"], a, input[type="button"]');
    if (target) {
      target.style.opacity = '1';
      target.style.transform = 'scale(1)';
    }
  }, true);

  // Prevent double-tap zoom on buttons
  let lastTap = 0;
  document.addEventListener('touchend', function(e) {
    const now = Date.now();
    const timesince = now - lastTap;
    if (timesince < 500 && timesince > 0) {
      e.preventDefault();
    }
    lastTap = now;
  }, false);

  // Prevent scroll while modal is open
  document.addEventListener('touchmove', function(e) {
    const modal = e.target.closest('[class*="modal"], [class*="overlay"]');
    if (modal && modal.getAttribute('style') !== 'display: none') {
      e.preventDefault();
    }
  }, { passive: false });
}

// 3. Mobile Keyboard Optimization
function optimizeMobileKeyboard() {
  // Prevent zoom on input focus
  document.addEventListener('touchstart', function(e) {
    if (e.target.matches('input, textarea, select')) {
      e.target.style.fontSize = '16px';
    }
  });

  // Blur keyboard when clicking outside form
  document.addEventListener('click', function(e) {
    if (!e.target.matches('input, textarea, select')) {
      document.activeElement.blur();
    }
  });

  // Handle Enter key on mobile for form submission
  document.addEventListener('keypress', function(e) {
    if (e.target.matches('textarea') && e.key === 'Enter' && !e.shiftKey) {
      const form = e.target.closest('form');
      if (form) {
        e.preventDefault();
        form.dispatchEvent(new Event('submit'));
      }
    }
  });
}

// 4. Handle Orientation Changes
function handleOrientationChanges() {
  window.addEventListener('orientationchange', function() {
    setTimeout(() => {
      const vh = window.innerHeight * 0.01;
      document.documentElement.style.setProperty('--vh', `${vh}px`);
      
      // Adjust layout if needed
      const modals = document.querySelectorAll('[class*="modal"], [class*="overlay"]');
      modals.forEach(modal => {
        if (!modal.classList.contains('hidden')) {
          // Reposition modals
          modal.style.height = '100vh';
          modal.style.height = '100dvh'; // Dynamic viewport height
        }
      });

      // Log orientation
      console.log(`Orientation: ${window.innerWidth > window.innerHeight ? 'landscape' : 'portrait'}`);
    }, 300);
  });

  // Initial setup
  const vh = window.innerHeight * 0.01;
  document.documentElement.style.setProperty('--vh', `${vh}px`);
}

// 5. Optimize Modals for Mobile
function optimizeModalsForMobile() {
  const modals = document.querySelectorAll('[class*="modal"], [class*="overlay"]');
  
  modals.forEach(modal => {
    // Ensure modals have proper mobile sizing
    modal.style.maxHeight = '100dvh';
    modal.style.width = '100%';
    
    // Make scrollable on mobile if content overflows
    const content = modal.querySelector('[class*="modal-content"], [class*="card"]');
    if (content) {
      content.style.maxHeight = 'calc(100dvh - 40px)';
      content.style.overflowY = 'auto';
      content.style.WebkitOverflowScrolling = 'touch'; // Smooth scrolling
    }

    // Close modal on outside click
    modal.addEventListener('click', function(e) {
      if (e.target === this && !this.classList.contains('hidden')) {
        this.classList.add('hidden');
      }
    });

    // Close with back button on Android
    if (navigator.userAgent.includes('Android')) {
      window.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && !modal.classList.contains('hidden')) {
          modal.classList.add('hidden');
          e.preventDefault();
        }
      });
    }
  });
}

// 6. Optimize Mobile Forms
function optimizeMobileForms() {
  const forms = document.querySelectorAll('form');
  
  forms.forEach(form => {
    const inputs = form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
      // Ensure proper font size to prevent zoom
      input.style.fontSize = '16px';
      
      // Add proper spacing
      const group = input.closest('[class*="form-group"]') || input.parentElement;
      if (group) {
        group.style.marginBottom = '16px';
      }

      // Add input-specific optimizations
      if (input.type === 'email') {
        input.setAttribute('inputmode', 'email');
      } else if (input.type === 'tel') {
        input.setAttribute('inputmode', 'tel');
      } else if (input.type === 'number') {
        input.setAttribute('inputmode', 'numeric');
      } else if (input.tagName === 'TEXTAREA') {
        input.setAttribute('spellcheck', 'true');
      }

      // Improve select dropdowns
      if (input.tagName === 'SELECT') {
        input.style.padding = '12px';
        input.style.fontSize = '16px';
        input.style.minHeight = '44px';
      }
    });

    // Improve form submission on mobile
    const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
    if (submitBtn) {
      submitBtn.style.minHeight = '44px';
      submitBtn.style.minWidth = '100%';
      submitBtn.addEventListener('click', function(e) {
        if (isMobileDevice()) {
          document.activeElement.blur(); // Hide keyboard
        }
      });
    }
  });
}

// 7. Apply Safe Area Support
function applySafeAreaSupport() {
  const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
  
  if (isIOS) {
    // Add padding to account for notch/safe areas
    const topNav = document.querySelector('.top-nav');
    if (topNav) {
      topNav.style.paddingTop = 'max(12px, env(safe-area-inset-top))';
      topNav.style.paddingLeft = 'max(12px, env(safe-area-inset-left))';
      topNav.style.paddingRight = 'max(12px, env(safe-area-inset-right))';
    }

    const content = document.querySelector('.content-area');
    if (content) {
      content.style.paddingLeft = 'max(12px, env(safe-area-inset-left))';
      content.style.paddingRight = 'max(12px, env(safe-area-inset-right))';
    }

    // Adjust for home indicator
    document.body.style.paddingBottom = 'env(safe-area-inset-bottom)';
  }
}

// 8. Setup Swipe Handlers
function setupSwipeHandlers() {
  let touchStartX = 0;
  let touchEndX = 0;

  document.addEventListener('touchstart', function(e) {
    touchStartX = e.changedTouches[0].screenX;
  }, false);

  document.addEventListener('touchend', function(e) {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
  }, false);

  function handleSwipe() {
    const diff = touchStartX - touchEndX;
    const threshold = 50;

    // Swipe left (usually back/close)
    if (diff > threshold) {
      const modal = document.querySelector('[class*="modal"]:not(.hidden), [class*="overlay"]:not(.hidden)');
      if (modal) {
        modal.classList.add('hidden');
      }
    }
    // Swipe right (usually forward/open)
    if (diff < -threshold) {
      // Could open menu or navigate forward
    }
  }
}

// Additional Utilities

// Check if device is in fullscreen
function isFullscreen() {
  return document.fullscreenElement ||
         document.webkitFullscreenElement ||
         document.mozFullScreenElement ||
         document.msFullscreenElement;
}

// Request fullscreen
function requestFullscreen(element) {
  if (!isMobileDevice()) return;
  
  const el = element || document.documentElement;
  
  if (el.requestFullscreen) {
    el.requestFullscreen();
  } else if (el.webkitRequestFullscreen) {
    el.webkitRequestFullscreen();
  } else if (el.mozRequestFullScreen) {
    el.mozRequestFullScreen();
  } else if (el.msRequestFullscreen) {
    el.msRequestFullscreen();
  }
}

// Exit fullscreen
function exitFullscreen() {
  if (document.exitFullscreen) {
    document.exitFullscreen();
  } else if (document.webkitExitFullscreen) {
    document.webkitExitFullscreen();
  } else if (document.mozCancelFullScreen) {
    document.mozCancelFullScreen();
  } else if (document.msExitFullscreen) {
    document.msExitFullscreen();
  }
}

// Get viewport dimensions accounting for safe areas
function getViewportDimensions() {
  return {
    width: window.innerWidth,
    height: window.innerHeight,
    safeTop: parseInt(getComputedStyle(document.documentElement).getPropertyValue('env(safe-area-inset-top)')) || 0,
    safeBottom: parseInt(getComputedStyle(document.documentElement).getPropertyValue('env(safe-area-inset-bottom)')) || 0,
    safeLeft: parseInt(getComputedStyle(document.documentElement).getPropertyValue('env(safe-area-inset-left)')) || 0,
    safeRight: parseInt(getComputedStyle(document.documentElement).getPropertyValue('env(safe-area-inset-right)')) || 0
  };
}

// Prevent pinch zoom while allowing user zoom
function preventPinchZoom() {
  document.addEventListener('wheel', function(e) {
    if (e.ctrlKey || e.metaKey) {
      e.preventDefault();
    }
  }, { passive: false });

  document.addEventListener('gesturestart', function(e) {
    e.preventDefault();
  });
}

// Handle network status
function setupNetworkStatusHandler() {
  window.addEventListener('online', function() {
    console.log('Device is online');
    document.documentElement.setAttribute('data-online', 'true');
    // Trigger sync if needed
    if (typeof app !== 'undefined' && app.syncToCloud) {
      setTimeout(() => app.syncToCloud(), 1000);
    }
  });

  window.addEventListener('offline', function() {
    console.log('Device is offline');
    document.documentElement.setAttribute('data-online', 'false');
  });
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeMobileOptimizations);
} else {
  initializeMobileOptimizations();
}

// Also setup network handlers
setupNetworkStatusHandler();

// Expose utilities globally
window.mobileUtils = {
  isMobileDevice,
  isTouchDevice,
  isFullscreen,
  requestFullscreen,
  exitFullscreen,
  getViewportDimensions,
  preventPinchZoom
};

console.log('ILikeSci Mobile Optimizations loaded');
