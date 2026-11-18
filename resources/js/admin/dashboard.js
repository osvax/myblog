// ============================================================
// Admin dashboard: stats, charts, real-time updates
// ============================================================

class Dashboard {
  constructor() {
    this.widgets = new Map();
    this.refreshInterval = null;
    this.init();
  }
  
  init() {
    this.setupStatCards();
    this.setupCharts();
    this.setupRealTimeUpdates();
    this.setupQuickActions();
  }
  
  setupStatCards() {
    // Animate counters on load
    document.querySelectorAll('.stat-card [data-counter]').forEach(el => {
      const target = parseInt(el.dataset.counter) || 0;
      this.animateCounter(el, target);
    });
    
    // Refresh stats periodically
    this.refreshInterval = setInterval(() => this.refreshStats(), 60000); // 1 min
  }
  
  animateCounter(el, target) {
    const duration = parseInt(el.dataset.counterDuration) || 1500;
    const start = performance.now();
    
    function update(currentTime) {
      const elapsed = currentTime - start;
      const progress = Math.min(elapsed / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
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
  
  async refreshStats() {
    try {
      const response = await fetch('/api/admin/dashboard/stats', {
        headers: { 'X-CSRF-Token': Admin.csrfToken },
        credentials: 'same-origin',
      });
      
      const data = await response.json();
      
      // Update stat cards
      Object.entries(data).forEach(([key, value]) => {
        const el = document.querySelector(`[data-stat="${key}"]`);
        if (el) {
          const isCounter = el.hasAttribute('data-counter');
          if (isCounter) {
            el.dataset.counter = value;
            this.animateCounter(el, value);
          } else {
            el.textContent = typeof value === 'number' ? value.toLocaleString('ru-RU') : value;
          }
        }
      });
      
      // Update charts if any
      this.updateCharts(data.charts);
      
    } catch (error) {
      console.error('Dashboard stats refresh failed:', error);
    }
  }
  
  setupCharts() {
    // Initialize Chart.js charts if present
    if (typeof Chart === 'undefined') return;
    
    // Traffic chart
    const trafficCtx = document.getElementById('trafficChart');
    if (trafficCtx) {
      this.widgets.set('traffic', new Chart(trafficCtx, {
        type: 'line',
        data: {
          labels: [],
          datasets: [{
            label: 'Посетители',
            data: [],
            borderColor: '#ff8c1a',
            backgroundColor: 'rgba(255, 140, 26, 0.1)',
            fill: true,
            tension: 0.4,
            pointRadius: 0,
            pointHoverRadius: 6,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, grid: { color: 'rgba(53,53,53,0.05)' } },
          },
          interaction: { intersect: false, mode: 'index' },
        },
      }));
    }
    
    // Content types chart
    const contentCtx = document.getElementById('contentChart');
    if (contentCtx) {
      this.widgets.set('content', new Chart(contentCtx, {
        type: 'doughnut',
        data: {
          labels: ['Статьи', 'Проекты', 'Страницы', 'Комментарии'],
          datasets: [{
            data: [0, 0, 0, 0],
            backgroundColor: ['#ff8c1a', '#353535', '#198754', '#0dcaf0'],
            borderWidth: 0,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'bottom', labels: { padding: 15 } } },
          cutout: '65%',
        },
      }));
    }
    
    // Load initial chart data
    this.loadChartData();
  }
  
  async loadChartData() {
    try {
      const response = await fetch('/api/admin/dashboard/charts', {
        headers: { 'X-CSRF-Token': Admin.csrfToken },
        credentials: 'same-origin',
      });
      
      const data = await response.json();
      this.updateCharts(data);
    } catch (error) {
      console.error('Chart data load failed:', error);
    }
  }
  
  updateCharts(data) {
    if (!data) return;
    
    // Traffic chart
    const trafficChart = this.widgets.get('traffic');
    if (trafficChart && data.traffic) {
      trafficChart.data.labels = data.traffic.labels;
      trafficChart.data.datasets[0].data = data.traffic.values;
      trafficChart.update('none');
    }
    
    // Content chart
    const contentChart = this.widgets.get('content');
    if (contentChart && data.content) {
      contentChart.data.datasets[0].data = [
        data.content.articles || 0,
        data.content.projects || 0,
        data.content.pages || 0,
        data.content.comments || 0,
      ];
      contentChart.update('none');
    }
  }
  
  setupRealTimeUpdates() {
    // WebSocket for real-time notifications (optional)
    if (window.WebSocket && this.options?.wsUrl) {
      this.connectWebSocket();
    }
    
    // Poll for notifications
    setInterval(() => this.checkNotifications(), 30000);
  }
  
  connectWebSocket() {
    this.ws = new WebSocket(this.options.wsUrl);
    
    this.ws.onopen = () => console.log('Dashboard WS connected');
    this.ws.onmessage = e => {
      const data = JSON.parse(e.data);
      this.handleWSMessage(data);
    };
    this.ws.onclose = () => {
      console.log('Dashboard WS disconnected, reconnecting...');
      setTimeout(() => this.connectWebSocket(), 5000);
    };
  }
  
  handleWSMessage(data) {
    switch (data.type) {
      case 'notification':
        Admin.toast(data.message, data.level || 'info');
        this.updateNotificationBadge(data.unread_count);
        break;
      case 'stat_update':
        this.updateStat(data.key, data.value);
        break;
      case 'new_order':
        this.showNewOrderAlert(data.order);
        break;
    }
  }
  
  async checkNotifications() {
    try {
      const response = await fetch('/api/admin/notifications/unread-count', {
        headers: { 'X-CSRF-Token': Admin.csrfToken },
        credentials: 'same-origin',
      });
      const data = await response.json();
      this.updateNotificationBadge(data.count);
    } catch (error) {
      console.error('Notification check failed:', error);
    }
  }
  
  updateNotificationBadge(count) {
    const badge = document.querySelector('#notification-badge');
    if (badge) {
      badge.textContent = count;
      badge.style.display = count > 0 ? 'inline-flex' : 'none';
    }
  }
  
  updateStat(key, value) {
    const el = document.querySelector(`[data-stat="${key}"]`);
    if (el) {
      el.textContent = typeof value === 'number' ? value.toLocaleString('ru-RU') : value;
    }
  }
  
  showNewOrderAlert(order) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-info alert-dismissible fade show position-fixed top-0 end-0 m-3';
    alert.style.zIndex = '2000';
    alert.innerHTML = `
      <strong>Новый заказ!</strong> ${this.escapeHtml(order.name)} — ${this.escapeHtml(order.service)}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 10000);
  }
  
  setupQuickActions() {
    // Quick action buttons
    document.querySelectorAll('[data-quick-action]').forEach(btn => {
      btn.addEventListener('click', e => {
        e.preventDefault();
        const action = btn.dataset.quickAction;
        this.handleQuickAction(action, btn);
      });
    });
  }
  
  handleQuickAction(action, btn) {
    switch (action) {
      case 'new-article':
        window.location.href = '/admin/articles/create';
        break;
      case 'new-project':
        window.location.href = '/admin/projects/create';
        break;
      case 'new-page':
        window.location.href = '/admin/pages/create';
        break;
      case 'clear-cache':
        this.clearCache(btn);
        break;
      case 'backup':
        this.createBackup(btn);
        break;
    }
  }
  
  async clearCache(btn) {
    if (!await Admin.confirm('Очистить весь кэш?')) return;
    
    Admin.setButtonLoading(btn, true);
    try {
      await Admin.api('/api/admin/cache/clear', { method: 'POST' });
      Admin.toast('Кэш очищен', 'success');
    } catch (error) {
      Admin.toast(error.message, 'error');
    } finally {
      Admin.setButtonLoading(btn, false);
    }
  }
  
  async createBackup(btn) {
    Admin.setButtonLoading(btn, true);
    try {
      const data = await Admin.api('/api/admin/backup', { method: 'POST' });
      Admin.toast(`Резервная копия создана: ${data.filename}`, 'success');
    } catch (error) {
      Admin.toast(error.message, 'error');
    } finally {
      Admin.setButtonLoading(btn, false);
    }
  }
  
  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
  
  destroy() {
    this.refreshInterval && clearInterval(this.refreshInterval);
    this.widgets.forEach(chart => chart.destroy());
    this.ws?.close();
  }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
  if (document.querySelector('.admin-dashboard')) {
    window.dashboard = new Dashboard();
  }
});

export { Dashboard };