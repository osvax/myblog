// ============================================================
// Lazy loading images with IntersectionObserver
// ============================================================

class LazyImageLoader {
  constructor(options = {}) {
    this.selector = options.selector || 'img[data-src], img.lazy';
    this.rootMargin = options.rootMargin || '100px 0px';
    this.threshold = options.threshold || 0.01;
    this.placeholder = options.placeholder || 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1"%3E%3C/svg%3E';
    
    this.observer = null;
    this.init();
  }
  
  init() {
    // Check for IntersectionObserver support
    if (!('IntersectionObserver' in window)) {
      this.loadAllImmediately();
      return;
    }
    
    this.observer = new IntersectionObserver(this.onIntersect.bind(this), {
      rootMargin: this.rootMargin,
      threshold: this.threshold,
    });
    
    this.observeImages();
    
    // Re-observe on DOM changes (for dynamic content)
    this.setupMutationObserver();
  }
  
  observeImages() {
    document.querySelectorAll(this.selector).forEach(img => {
      if (!img.dataset.lazyLoaded) {
        this.observer.observe(img);
      }
    });
  }
  
  setupMutationObserver() {
    const mutObserver = new MutationObserver(mutations => {
      mutations.forEach(mutation => {
        mutation.addedNodes.forEach(node => {
          if (node.nodeType === 1) { // Element node
            if (node.matches && node.matches(this.selector)) {
              this.observer.observe(node);
            }
            node.querySelectorAll?.(this.selector).forEach(img => this.observer.observe(img));
          }
        });
      });
    });
    
    mutObserver.observe(document.body, { childList: true, subtree: true });
  }
  
  onIntersect(entries) {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        this.loadImage(entry.target);
        this.observer.unobserve(entry.target);
      }
    });
  }
  
  loadImage(img) {
    const src = img.dataset.src || img.dataset.lazySrc;
    const srcset = img.dataset.srcset;
    const sizes = img.dataset.sizes;
    
    if (!src && !srcset) return;
    
    // Add loading state
    img.classList.add('lazy-loading');
    img.style.opacity = '0';
    img.style.transition = 'opacity 0.3s ease';
    
    // Create a temporary image to preload
    const tempImg = new Image();
    
    tempImg.onload = () => {
      if (src) img.src = src;
      if (srcset) img.srcset = srcset;
      if (sizes) img.sizes = sizes;
      
      img.classList.remove('lazy-loading');
      img.classList.add('lazy-loaded');
      img.style.opacity = '1';
      img.removeAttribute('data-src');
      img.removeAttribute('data-srcset');
      img.removeAttribute('data-sizes');
      img.dataset.lazyLoaded = 'true';
      
      // Trigger custom event
      img.dispatchEvent(new CustomEvent('lazyloaded', { detail: { src } }));
    };
    
    tempImg.onerror = () => {
      img.classList.remove('lazy-loading');
      img.classList.add('lazy-error');
      img.style.opacity = '1';
      // Fallback to placeholder or original src
      if (img.dataset.fallback) {
        img.src = img.dataset.fallback;
      }
      console.warn('Lazy load failed:', src);
    };
    
    // Set src to trigger load
    if (src) tempImg.src = src;
    else if (srcset) tempImg.srcset = srcset;
  }
  
  loadAllImmediately() {
    document.querySelectorAll(this.selector).forEach(img => this.loadImage(img));
  }
  
  // Manual trigger for newly added images
  refresh() {
    this.observeImages();
  }
  
  destroy() {
    this.observer?.disconnect();
  }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
  window.lazyImageLoader = new LazyImageLoader({
    selector: 'img[data-src], img.lazy, picture > img[data-src]',
    rootMargin: '200px 0px', // Start loading 200px before viewport
  });
});

// Handle picture elements with multiple sources
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('picture').forEach(picture => {
    const img = picture.querySelector('img');
    if (!img || !img.dataset.src) return;
    
    const sources = picture.querySelectorAll('source[data-srcset]');
    sources.forEach(source => {
      if (source.dataset.srcset) {
        source.srcset = source.dataset.srcset;
        source.removeAttribute('data-srcset');
      }
    });
  });
});

// Low Quality Image Placeholder (LQIP) support
// Usage: <img data-src="large.jpg" data-lqip="small.jpg" class="lazy">
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('img[data-lqip]').forEach(img => {
    const lqip = new Image();
    lqip.src = img.dataset.lqip;
    lqip.onload = () => {
      img.style.backgroundImage = `url(${img.dataset.lqip})`;
      img.style.backgroundSize = 'cover';
      img.style.backgroundPosition = 'center';
      img.style.filter = 'blur(20px)';
      img.style.transition = 'filter 0.5s ease';
    };
  });
  
  // When lazy loaded, remove blur
  document.addEventListener('lazyloaded', e => {
    const img = e.target;
    if (img.dataset.lqip) {
      img.style.filter = 'none';
      img.style.backgroundImage = 'none';
    }
  });
});

export { LazyImageLoader };