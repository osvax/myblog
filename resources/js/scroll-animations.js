// ============================================================
// Scroll animations with IntersectionObserver
// ============================================================

class ScrollAnimations {
  constructor(options = {}) {
    this.selector = options.selector || '[data-aos]';
    this.rootMargin = options.rootMargin || '0px 0px -50px 0px';
    this.threshold = options.threshold || 0.1;
    this.once = options.once ?? true;
    this.duration = options.duration || 600;
    this.easing = options.easing || 'ease-out';
    
    this.observer = null;
    this.animatedElements = new WeakSet();
    this.prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    
    this.init();
  }
  
  init() {
    if (this.prefersReducedMotion) {
      this.showAllImmediately();
      return;
    }
    
    if (!('IntersectionObserver' in window)) {
      this.showAllImmediately();
      return;
    }
    
    this.observer = new IntersectionObserver(this.onIntersect.bind(this), {
      rootMargin: this.rootMargin,
      threshold: this.threshold,
    });
    
    this.observeElements();
    this.setupMutationObserver();
  }
  
  observeElements() {
    document.querySelectorAll(this.selector).forEach(el => {
      if (!this.animatedElements.has(el)) {
        this.prepareElement(el);
        this.observer.observe(el);
      }
    });
  }
  
  setupMutationObserver() {
    const mutObserver = new MutationObserver(mutations => {
      mutations.forEach(mutation => {
        mutation.addedNodes.forEach(node => {
          if (node.nodeType === 1) {
            if (node.matches && node.matches(this.selector)) {
              this.prepareElement(node);
              this.observer.observe(node);
            }
            node.querySelectorAll?.(this.selector).forEach(el => {
              this.prepareElement(el);
              this.observer.observe(el);
            });
          }
        });
      });
    });
    
    mutObserver.observe(document.body, { childList: true, subtree: true });
  }
  
  prepareElement(el) {
    // Set initial state based on animation type
    const animation = el.dataset.aos || 'fade-up';
    const delay = parseInt(el.dataset.aosDelay) || 0;
    const duration = parseInt(el.dataset.aosDuration) || this.duration;
    
    el.style.opacity = '0';
    el.style.transform = this.getInitialTransform(animation);
    el.style.transition = `opacity ${duration}ms ${this.easing}, transform ${duration}ms ${this.easing}`;
    el.style.transitionDelay = `${delay}ms`;
    el.style.willChange = 'opacity, transform';
  }
  
  getInitialTransform(animation) {
    const transforms = {
      'fade-up': 'translateY(30px)',
      'fade-down': 'translateY(-30px)',
      'fade-left': 'translateX(30px)',
      'fade-right': 'translateX(-30px)',
      'fade': 'none',
      'zoom-in': 'scale(0.9)',
      'zoom-out': 'scale(1.1)',
      'flip-up': 'rotateX(-90deg)',
      'flip-down': 'rotateX(90deg)',
      'flip-left': 'rotateY(90deg)',
      'flip-right': 'rotateY(-90deg)',
    };
    return transforms[animation] || transforms['fade-up'];
  }
  
  onIntersect(entries) {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        this.animateElement(entry.target);
        if (this.once) {
          this.observer.unobserve(entry.target);
          this.animatedElements.add(entry.target);
        }
      } else if (!this.once) {
        // Reset for re-animation
        this.resetElement(entry.target);
      }
    });
  }
  
  animateElement(el) {
    el.style.opacity = '1';
    el.style.transform = 'none';
    el.dataset.aosAnimated = 'true';
    
    // Clean up after animation
    setTimeout(() => {
      el.style.willChange = 'auto';
    }, parseInt(el.dataset.aosDuration) || this.duration);
  }
  
  resetElement(el) {
    const animation = el.dataset.aos || 'fade-up';
    el.style.opacity = '0';
    el.style.transform = this.getInitialTransform(animation);
    el.dataset.aosAnimated = 'false';
  }
  
  showAllImmediately() {
    document.querySelectorAll(this.selector).forEach(el => {
      el.style.opacity = '1';
      el.style.transform = 'none';
      el.style.transition = 'none';
    });
  }
  
  refresh() {
    this.observeElements();
  }
  
  destroy() {
    this.observer?.disconnect();
  }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
  window.scrollAnimations = new ScrollAnimations({
    selector: '[data-aos], .animate-on-scroll',
    rootMargin: '0px 0px -10% 0px',
  });
});

// Staggered animations for lists
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-aos-stagger]').forEach(container => {
    const items = container.querySelectorAll('[data-aos]');
    const baseDelay = parseInt(container.dataset.aosStaggerDelay) || 100;
    
    items.forEach((item, index) => {
      const existingDelay = parseInt(item.dataset.aosDelay) || 0;
      item.dataset.aosDelay = existingDelay + (index * baseDelay);
    });
  });
});

// Parallax effect for hero sections
const parallaxElements = document.querySelectorAll('[data-parallax]');
if (parallaxElements.length && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
  let ticking = false;
  
  window.addEventListener('scroll', () => {
    if (!ticking) {
      requestAnimationFrame(updateParallax);
      ticking = true;
    }
  }, { passive: true });
  
  function updateParallax() {
    const scrollY = window.pageYOffset;
    
    parallaxElements.forEach(el => {
      const speed = parseFloat(el.dataset.parallax) || 0.5;
      const offset = scrollY * speed;
      el.style.transform = `translateY(${offset}px)`;
    });
    
    ticking = false;
  }
}

// Counter animation
const counters = document.querySelectorAll('[data-counter]');
if (counters.length && 'IntersectionObserver' in window) {
  const counterObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        counterObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });
  
  counters.forEach(counter => counterObserver.observe(counter));
  
  function animateCounter(el) {
    const target = parseInt(el.dataset.counter) || 0;
    const duration = parseInt(el.dataset.counterDuration) || 2000;
    const start = performance.now();
    
    function update(currentTime) {
      const elapsed = currentTime - start;
      const progress = Math.min(elapsed / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
      const current = Math.floor(target * eased);
      
      el.textContent = current.toLocaleString('ru-RU');
      
      if (progress < 1) {
        requestAnimationFrame(update);
      } else {
        el.textContent = target.toLocaleString('ru-RU');
      }
    }
    
    requestAnimationFrame(update);
  }
}

// Typing animation for text elements
const typingElements = document.querySelectorAll('[data-typing]');
typingElements.forEach(el => {
  const text = el.dataset.typing;
  const speed = parseInt(el.dataset.typingSpeed) || 50;
  const delay = parseInt(el.dataset.typingDelay) || 0;
  
  el.textContent = '';
  el.style.borderRight = '2px solid';
  el.style.animation = 'blink 1s infinite';
  
  setTimeout(() => {
    let i = 0;
    function type() {
      if (i < text.length) {
        el.textContent += text.charAt(i);
        i++;
        setTimeout(type, speed);
      } else {
        el.style.borderRight = 'none';
        el.style.animation = 'none';
      }
    }
    type();
  }, delay);
});

// Add blink animation for typing cursor
const style = document.createElement('style');
style.textContent = `
  @keyframes blink {
    0%, 50% { border-color: currentColor; }
    51%, 100% { border-color: transparent; }
  }
`;
document.head.appendChild(style);

export { ScrollAnimations };