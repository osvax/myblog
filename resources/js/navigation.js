// ============================================================
// Navigation & Mobile Menu
// ============================================================

// Mobile sidebar toggle for admin
const sidebarToggle = document.querySelector('[data-bs-toggle="sidebar"]');
const sidebar = document.querySelector('.admin-sidebar');
const sidebarOverlay = document.querySelector('.admin-sidebar-overlay');

if (sidebarToggle && sidebar) {
  sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('show');
    if (sidebarOverlay) sidebarOverlay.classList.toggle('show');
  });
  
  if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', () => {
      sidebar.classList.remove('show');
      sidebarOverlay.classList.remove('show');
    });
  }
  
  // Close on escape
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && sidebar.classList.contains('show')) {
      sidebar.classList.remove('show');
      if (sidebarOverlay) sidebarOverlay.classList.remove('show');
    }
  });
}

// Dropdown keyboard navigation
document.addEventListener('keydown', e => {
  if (e.key === 'Tab') {
    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
      const items = menu.querySelectorAll('.dropdown-item:not(.disabled)');
      const firstItem = items[0];
      const lastItem = items[items.length - 1];
      
      if (e.shiftKey && document.activeElement === firstItem) {
        e.preventDefault();
        lastItem.focus();
      } else if (!e.shiftKey && document.activeElement === lastItem) {
        e.preventDefault();
        firstItem.focus();
      }
    });
  }
});

// Search form toggle (mobile)
const searchToggle = document.querySelector('[data-bs-toggle="search"]');
const searchForm = document.querySelector('.search-form');
if (searchToggle && searchForm) {
  searchToggle.addEventListener('click', () => {
    searchForm.classList.toggle('show');
    if (searchForm.classList.contains('show')) {
      searchForm.querySelector('input').focus();
    }
  });
  
  document.addEventListener('click', e => {
    if (!searchForm.contains(e.target) && !searchToggle.contains(e.target)) {
      searchForm.classList.remove('show');
    }
  });
}

// Keyboard accessible dropdowns
document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
  toggle.addEventListener('keydown', e => {
    const menu = toggle.nextElementSibling;
    if (!menu || !menu.classList.contains('dropdown-menu')) return;
    
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      toggle.click();
    } else if (e.key === 'Escape') {
      const instance = bootstrap.Dropdown.getInstance(toggle);
      if (instance) instance.hide();
    }
  });
});

// Sticky sidebar on scroll (for blog/article pages)
const stickySidebar = document.querySelector('.sticky-sidebar');
if (stickySidebar && window.innerWidth > 991) {
  const sidebarTop = stickySidebar.offsetTop;
  const footer = document.querySelector('footer');
  
  window.addEventListener('scroll', () => {
    const scrollTop = window.pageYOffset;
    const footerTop = footer ? footer.offsetTop : document.body.scrollHeight;
    const sidebarHeight = stickySidebar.offsetHeight;
    
    if (scrollTop > sidebarTop) {
      const maxTop = footerTop - sidebarHeight - 20;
      const newTop = Math.min(scrollTop - sidebarTop + 20, maxTop);
      stickySidebar.style.transform = `translateY(${Math.max(0, newTop)}px)`;
    } else {
      stickySidebar.style.transform = 'translateY(0)';
    }
  }, { passive: true });
}