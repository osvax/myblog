// ============================================================
// Admin navigation & sidebar
// ============================================================

// Active menu item based on current URL
const currentPath = window.location.pathname;
document.querySelectorAll('.admin-sidebar .nav-link').forEach(link => {
  const href = link.getAttribute('href');
  if (href && href !== '/' && currentPath.startsWith(href)) {
    link.classList.add('active');
    // Open parent collapse if nested
    const collapse = link.closest('.collapse');
    if (collapse) {
      collapse.classList.add('show');
      const trigger = document.querySelector(`[data-bs-target="#${collapse.id}"]`);
      trigger?.classList.remove('collapsed');
      trigger?.setAttribute('aria-expanded', 'true');
    }
  } else if (href === '/admin' && currentPath === '/admin') {
    link.classList.add('active');
  }
});

// Keyboard navigation for sidebar
document.querySelectorAll('.admin-sidebar .nav-link').forEach(link => {
  link.addEventListener('keydown', e => {
    if (e.key === 'Enter' || e.key === ' ') {
      // Let default behavior handle it
    } else if (e.key === 'ArrowDown') {
      e.preventDefault();
      const next = link.parentElement.nextElementSibling?.querySelector('.nav-link');
      next?.focus();
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      const prev = link.parentElement.previousElementSibling?.querySelector('.nav-link');
      prev?.focus();
    }
  });
});

// Breadcrumb improvements
const breadcrumb = document.querySelector('.admin-topbar .breadcrumb');
if (breadcrumb) {
  // Make last item non-clickable if it's the current page
  const items = breadcrumb.querySelectorAll('.breadcrumb-item');
  const lastItem = items[items.length - 1];
  if (lastItem && !lastItem.querySelector('a')) {
    lastItem.setAttribute('aria-current', 'page');
  }
}

// Search in sidebar (filter menu items)
const sidebarSearch = document.querySelector('#sidebar-search');
if (sidebarSearch) {
  sidebarSearch.addEventListener('input', App.debounce(e => {
    const query = e.target.value.toLowerCase();
    document.querySelectorAll('.admin-sidebar .nav-link').forEach(link => {
      const text = link.textContent.toLowerCase();
      const parent = link.closest('.nav-item, .collapse');
      if (text.includes(query) || query === '') {
        link.style.display = '';
        parent?.classList.remove('d-none');
      } else {
        link.style.display = 'none';
      }
    });
  }, 150));
}

// Tooltip for collapsed sidebar
const collapsedSidebar = document.querySelector('.admin-sidebar.collapsed');
if (collapsedSidebar) {
  collapsedSidebar.querySelectorAll('.nav-link').forEach(link => {
    const tooltip = new bootstrap.Tooltip(link, {
      placement: 'right',
      trigger: 'hover focus',
    });
  });
}