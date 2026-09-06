// pricing.html exclusives: Pro period selector + ROI estimator.

(function () {
  const toggle = document.getElementById('period-toggle');
  if (!toggle) return;
  const btns = toggle.querySelectorAll('.period-btn');
  const priceEl = document.querySelector('[data-pro-price]');
  const noteEl = document.querySelector('[data-pro-note]');
  const tagEl = document.getElementById('pro-period-tag');
  const saveBadge = document.getElementById('save-badge');

  const data = {
    monthly: { price: '149', note: 'Cobrado mensalmente.', tag: 'Mensal', save: null },
    semestral: { price: '127', note: 'R$ 762 cobrados a cada 6 meses.', tag: 'Semestral', save: 'Economize 15%' },
    annual: { price: '119', note: 'R$ 1.428 cobrados por ano.', tag: 'Anual', save: 'Economize 20%' },
  };

  function select(period) {
    const d = data[period];
    if (!d) return;
    priceEl.textContent = d.price;
    noteEl.textContent = d.note;
    tagEl.textContent = d.tag;
    if (d.save) {
      saveBadge.textContent = d.save;
      saveBadge.classList.remove('invisible');
    } else {
      saveBadge.classList.add('invisible');
    }
    btns.forEach((b) => {
      const on = b.dataset.period === period;
      b.classList.toggle('bg-accent', on);
      b.classList.toggle('text-white', on);
      b.classList.toggle('text-muted', !on);
      b.setAttribute('aria-pressed', String(on));
    });
  }
  btns.forEach((b) => b.addEventListener('click', () => select(b.dataset.period)));
  select('annual');
})();

(function () {
  const input = document.getElementById('roi-docs');
  if (!input) return;
  const val = document.getElementById('roi-docs-val');
  const hoursEl = document.getElementById('roi-hours');
  const daysEl = document.getElementById('roi-days');
  const MIN_PER_DOC = 8,
    WORK_DAYS = 20,
    HOURS_PER_DAY = 8;
  function update() {
    const docs = parseInt(input.value, 10);
    const minutes = docs * WORK_DAYS * MIN_PER_DOC;
    const hours = Math.round(minutes / 60);
    const days = (hours / HOURS_PER_DAY).toFixed(1).replace('.', ',');
    val.textContent = docs;
    hoursEl.textContent = hours;
    daysEl.textContent = days;
  }
  input.addEventListener('input', update);
  update();
})();
