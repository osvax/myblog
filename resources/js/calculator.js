// ============================================================
// Landing page calculator
// ============================================================

class Calculator {
  constructor(formSelector) {
    this.form = document.querySelector(formSelector);
    if (!this.form) return;
    
    this.prices = JSON.parse(this.form.dataset.prices || '{}');
    this.resultContainer = document.querySelector(this.form.dataset.resultTarget || '#calculator-result');
    this.totalEl = document.querySelector(this.form.dataset.totalTarget || '#calculator-total');
    this.breakdownEl = document.querySelector(this.form.dataset.breakdownTarget || '#calculator-breakdown');
    
    this.init();
  }
  
  init() {
    this.bindEvents();
    this.calculate(); // Initial calculation
  }
  
  bindEvents() {
    // Recalculate on any input change
    this.form.querySelectorAll('input, select').forEach(input => {
      input.addEventListener('change', () => this.calculate());
      input.addEventListener('input', () => this.calculate());
    });
    
    // Range slider updates
    this.form.querySelectorAll('input[type="range"]').forEach(range => {
      const output = document.querySelector(`[data-for="${range.id}"]`);
      range.addEventListener('input', () => {
        if (output) output.textContent = range.value;
        this.calculate();
      });
    });
    
    // Checkbox groups (services)
    this.form.querySelectorAll('[data-service-group]').forEach(group => {
      group.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', () => this.updateGroupTotal(group));
      });
    });
    
    // Copy result button
    const copyBtn = document.querySelector('[data-action="copy-calculation"]');
    copyBtn?.addEventListener('click', () => this.copyResult());
  }
  
  calculate() {
    let total = 0;
    const breakdown = [];
    
    // Base price
    const basePrice = parseFloat(this.form.dataset.basePrice) || 0;
    if (basePrice > 0) {
      total += basePrice;
      breakdown.push({ name: 'Базовая стоимость', price: basePrice });
    }
    
    // Services checkboxes
    this.form.querySelectorAll('[data-service-group] input[type="checkbox"]:checked').forEach(cb => {
      const price = parseFloat(cb.dataset.price) || 0;
      const name = cb.dataset.name || cb.value;
      if (price > 0) {
        total += price;
        breakdown.push({ name, price });
      }
    });
    
    // Quantity inputs
    this.form.querySelectorAll('[data-quantity]').forEach(input => {
      const qty = parseInt(input.value) || 0;
      const price = parseFloat(input.dataset.price) || 0;
      const name = input.dataset.name || input.name;
      if (qty > 0 && price > 0) {
        const subtotal = qty * price;
        total += subtotal;
        breakdown.push({ name: `${name} (×${qty})`, price: subtotal });
      }
    });
    
    // Select options
    this.form.querySelectorAll('select[data-price]').forEach(select => {
      const option = select.options[select.selectedIndex];
      const price = parseFloat(option.dataset.price) || 0;
      const name = option.dataset.name || option.text;
      if (price > 0) {
        total += price;
        breakdown.push({ name, price });
      }
    });
    
    // Range sliders with price
    this.form.querySelectorAll('input[type="range"][data-price]').forEach(range => {
      const multiplier = parseFloat(range.value) || 0;
      const price = parseFloat(range.dataset.price) || 0;
      const name = range.dataset.name || range.id;
      if (multiplier > 0 && price > 0) {
        const subtotal = multiplier * price;
        total += subtotal;
        breakdown.push({ name: `${name} (${multiplier})`, price: subtotal });
      }
    });
    
    // Discount
    const discountInput = this.form.querySelector('[data-discount]');
    if (discountInput) {
      const discount = parseFloat(discountInput.value) || 0;
      if (discount > 0) {
        const discountAmount = total * (discount / 100);
        total -= discountAmount;
        breakdown.push({ name: `Скидка ${discount}%`, price: -discountAmount });
      }
    }
    
    // Tax
    const taxInput = this.form.querySelector('[data-tax]');
    if (taxInput) {
      const tax = parseFloat(taxInput.value) || 0;
      if (tax > 0) {
        const taxAmount = total * (tax / 100);
        total += taxAmount;
        breakdown.push({ name: `Налог ${tax}%`, price: taxAmount });
      }
    }
    
    this.render(total, breakdown);
    return { total, breakdown };
  }
  
  render(total, breakdown) {
    // Update total
    if (this.totalEl) {
      this.totalEl.textContent = this.formatPrice(total);
      this.totalEl.dataset.value = total;
    }
    
    // Update breakdown
    if (this.breakdownEl) {
      this.breakdownEl.innerHTML = breakdown.map(item => `
        <tr class="${item.price < 0 ? 'text-danger' : ''}">
          <td>${this.escapeHtml(item.name)}</td>
          <td class="text-end">${this.formatPrice(item.price)}</td>
        </tr>
      `).join('');
    }
    
    // Show/hide result container
    if (this.resultContainer) {
      this.resultContainer.classList.remove('d-none');
    }
    
    // Trigger custom event
    this.form.dispatchEvent(new CustomEvent('calculated', { 
      detail: { total, breakdown } 
    }));
  }
  
  updateGroupTotal(group) {
    const totalEl = group.querySelector('[data-group-total]');
    if (!totalEl) return;
    
    let groupTotal = 0;
    group.querySelectorAll('input[type="checkbox"]:checked').forEach(cb => {
      groupTotal += parseFloat(cb.dataset.price) || 0;
    });
    
    totalEl.textContent = this.formatPrice(groupTotal);
  }
  
  copyResult() {
    const { total, breakdown } = this.calculate();
    const text = `Расчет стоимости:\n${breakdown.map(b => `${b.name}: ${this.formatPrice(b.price)}`).join('\n')}\n\nИтого: ${this.formatPrice(total)}`;
    
    navigator.clipboard.writeText(text).then(() => {
      Admin.toast('Расчет скопирован в буфер обмена', 'success');
    });
  }
  
  formatPrice(price) {
    return new Intl.NumberFormat('ru-RU', {
      style: 'currency',
      currency: 'RUB',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(price);
  }
  
  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
}

// Initialize calculator on landing pages
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.calculator-form, [data-calculator]').forEach(form => {
    new Calculator(form);
  });
});

// Order form integration
document.addEventListener('DOMContentLoaded', () => {
  const orderForm = document.querySelector('#order-form');
  const calculatorForm = document.querySelector('.calculator-form');
  
  if (orderForm && calculatorForm) {
    // Sync calculator total to order form hidden field
    calculatorForm.addEventListener('calculated', e => {
      const totalInput = orderForm.querySelector('input[name="calculated_total"]');
      if (totalInput) totalInput.value = e.detail.total;
      
      const breakdownInput = orderForm.querySelector('input[name="calculation_breakdown"]');
      if (breakdownInput) breakdownInput.value = JSON.stringify(e.detail.breakdown);
    });
  }
});

export { Calculator };