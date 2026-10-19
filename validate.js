(() => {
  const form = document.querySelector('form');

  const rules = {
    studentID:        v => /^VUG\/SEN\/(24|25)\/\d{5}$/.test(v.trim()),
    firstName:        v => /^[A-Za-z\s\-']{2,}$/.test(v.trim()),
    lastName:         v => /^[A-Za-z\s\-']{2,}$/.test(v.trim()),
    middleName:       v => v.trim() === '' || /^[A-Za-z\s\-']{2,}$/.test(v.trim()),
    dob:              v => { const d = new Date(v), n = new Date(); return v && d < n && (n - d) / 31557600000 >= 15; },
    gender:           v => v !== '',
    email:            v => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim()),
    phone:            v => /^\+?[\d\s\-()]{7,15}$/.test(v.trim()),
    address:          v => v.trim().length >= 10,
    state:            v => /^[A-Za-z\s]{2,}$/.test(v.trim()),
    nationality:      v => /^[A-Za-z\s]{2,}$/.test(v.trim()),
    department:       v => v.trim().length >= 2,
    faculty:          v => v.trim().length >= 2,
    course:           v => v.trim().length >= 2,
    level:            v => v !== '',
    admissionYear:    v => { const y = +v; return y >= 1990 && y <= new Date().getFullYear() + 1; },
    registrationDate: v => { const d = new Date(v), a = new Date(+document.getElementById('admissionYear').value, 0, 1); return v && d >= a; },
    guardianName:     v => /^[A-Za-z\s\-']{2,}$/.test(v.trim()),
    guardianPhone:    v => /^\+?[\d\s\-()]{7,15}$/.test(v.trim()),
    emergencyContact: v => /^\+?[\d\s\-()]{7,15}$/.test(v.trim()),
  };

  const msgs = {
    studentID:        'Format: VUG/SEN/24/##### or VUG/SEN/25/#####',
    firstName:        'At least 2 letters required',
    lastName:         'At least 2 letters required',
    middleName:       'At least 2 letters if provided',
    dob:              'Must be a real past date — student must be 15+',
    gender:           'Please select a gender',
    email:            'Enter a valid email address',
    phone:            'Enter a valid phone number',
    address:          'Please enter a full address (min 10 characters)',
    state:            'Enter a valid state name',
    nationality:      'Enter a valid nationality',
    department:       'Enter your department name',
    faculty:          'Enter your faculty name',
    course:           'Enter your course of study',
    level:            'Please select your level',
    admissionYear:    'Enter a valid admission year',
    registrationDate: 'Must be on or after your admission year',
    guardianName:     'Enter guardian\'s full name',
    guardianPhone:    'Enter a valid phone number',
    emergencyContact: 'Enter a valid emergency contact number',
  };

  const icons = { valid: '✓', invalid: '✕' };

  function wrap(field) {
    if (field.parentElement.classList.contains('field-wrap')) return field.parentElement;
    const w = document.createElement('div');
    w.className = 'field-wrap';
    field.parentNode.insertBefore(w, field);
    w.appendChild(field);
    return w;
  }

  function getIcon(wrap) {
    return wrap.querySelector('.field-icon') || (() => {
      const i = document.createElement('span');
      i.className = 'field-icon';
      wrap.appendChild(i);
      return i;
    })();
  }

  function getErr(wrap) {
    return wrap.querySelector('.err') || (() => {
      const s = document.createElement('span');
      s.className = 'err';
      wrap.appendChild(s);
      return s;
    })();
  }

  function validate(id) {
    const field = document.getElementById(id);
    const ok = rules[id](field.value);
    const w = wrap(field);
    field.classList.toggle('invalid', !ok);
    field.classList.toggle('valid', ok);
    getIcon(w).textContent = ok ? icons.valid : icons.invalid;
    getIcon(w).className = 'field-icon ' + (ok ? 'vi' : 'xi');
    getErr(w).textContent = ok ? '' : msgs[id];
    return ok;
  }

  function setupField(id) {
    const field = document.getElementById(id);
    if (!field) return;
    const w = wrap(field);
    getErr(w);
    field.addEventListener('blur', () => validate(id));
    field.addEventListener('input', () => { if (field.classList.contains('invalid') || field.classList.contains('valid')) validate(id); });
    field.addEventListener('focus', () => w.classList.add('focused'));
    field.addEventListener('blur',  () => w.classList.remove('focused'));
  }

  Object.keys(rules).forEach(setupField);

  const sections = form.querySelectorAll('h2');
  const progress = document.createElement('div');
  progress.id = 'prog';
  const bar = document.createElement('div');
  bar.id = 'prog-bar';
  progress.appendChild(bar);
  form.prepend(progress);

  function updateProgress() {
    const ids = Object.keys(rules);
    const done = ids.filter(id => { const f = document.getElementById(id); return f && rules[id](f.value); }).length;
    bar.style.width = (done / ids.length * 100) + '%';
    bar.setAttribute('data-pct', Math.round(done / ids.length * 100) + '%');
  }

  form.addEventListener('input', updateProgress);
  form.addEventListener('change', updateProgress);

  Object.keys(rules).forEach(id => {
    const f = document.getElementById(id);
    if (!f) return;
    f.addEventListener('focus', () => f.closest('.field-wrap')?.classList.add('lift'));
    f.addEventListener('blur',  () => f.closest('.field-wrap')?.classList.remove('lift'));
  });

  form.addEventListener('submit', e => {
    e.preventDefault();
    const all = Object.keys(rules).map(id => validate(id));
    updateProgress();
    if (all.every(Boolean)) {
      const eb = document.getElementById('err-banner');
      if (eb) eb.textContent = '';
      const b = document.getElementById('form-banner') || (() => {
        const d = document.createElement('div'); d.id = 'form-banner'; form.prepend(d); return d;
      })();
      b.innerHTML = '<span class="ck">✓</span> Form submitted — we\'ll be in touch!';
      b.classList.add('show');
      b.scrollIntoView({ behavior: 'smooth', block: 'center' });
      ripple(form.querySelector('input[type="submit"]'));
    } else {
      const fb = document.getElementById('form-banner');
      if (fb) { fb.textContent = ''; fb.classList.remove('show'); }
      const first = Object.keys(rules).find(id => !rules[id](document.getElementById(id).value));
      if (first) document.getElementById(first).scrollIntoView({ behavior: 'smooth', block: 'center' });
      shakeBanner();
    }
  });

  function ripple(btn) {
    const r = document.createElement('span');
    r.className = 'ripple';
    btn.style.position = 'relative';
    btn.style.overflow = 'hidden';
    btn.appendChild(r);
    setTimeout(() => r.remove(), 600);
  }

  function shakeBanner() {
    const b = document.getElementById('err-banner') || (() => {
      const d = document.createElement('div'); d.id = 'err-banner'; form.prepend(d); return d;
    })();
    b.textContent = 'Please fix the highlighted fields before submitting.';
    b.classList.remove('shake');
    void b.offsetWidth;
    b.classList.add('shake');
  }

  form.addEventListener('reset', () => {
    document.querySelectorAll('.invalid, .valid').forEach(el => el.classList.remove('invalid', 'valid'));
    document.querySelectorAll('.err').forEach(el => el.textContent = '');
    document.querySelectorAll('.field-icon').forEach(el => { el.textContent = ''; el.className = 'field-icon'; });
    const fb = document.getElementById('form-banner');
    if (fb) { fb.textContent = ''; fb.classList.remove('show'); }
    const eb = document.getElementById('err-banner');
    if (eb) eb.textContent = '';
    bar.style.width = '0%';
    bar.removeAttribute('data-pct');
  });

  const style = document.createElement('style');
  style.textContent = `
    .field-wrap {
      position: relative;
      margin-bottom: 0;
    }
    .field-wrap.lift input, .field-wrap.lift select, .field-wrap.lift textarea {
      transform: translateY(-1px);
      box-shadow: 0 4px 14px rgba(90, 39, 32, .13);
    }
    .field-icon {
      position: absolute;
      right: 10px;
      top: 8px;
      font-size: 13px;
      font-weight: 700;
      pointer-events: none;
      opacity: 0;
      transition: opacity .2s, transform .25s;
    }
    .field-icon.vi { color: #2d7a3a; opacity: 1; transform: scale(1.15); }
    .field-icon.xi { color: #c0392b; opacity: 1; transform: scale(1.1); }
    textarea ~ .field-icon { top: 10px; }
    select ~ .field-icon { top: 9px; }
    input.valid, select.valid, textarea.valid { border-color: #2d7a3a; box-shadow: 0 0 0 3px rgba(45, 122, 58, .1); padding-right: 32px; }
    input.invalid, select.invalid, textarea.invalid { border-color: #c0392b; box-shadow: 0 0 0 3px rgba(192, 57, 43, .1); padding-right: 32px; }
    .err {
      display: block;
      font-size: 11.5px;
      color: #c0392b;
      min-height: 18px;
      margin-bottom: 10px;
      padding-left: 2px;
      opacity: 0;
      transform: translateY(-4px);
      transition: opacity .2s, transform .2s;
    }
    .err:not(:empty) { opacity: 1; transform: translateY(0); }
    #prog {
      height: 3px;
      background: var(--color-9);
      border-radius: 3px;
      margin-bottom: 20px;
      overflow: hidden;
    }
    #prog-bar {
      height: 100%;
      width: 0%;
      background: linear-gradient(90deg, var(--color-3), var(--color-6));
      border-radius: 3px;
      transition: width .4s cubic-bezier(.4, 0, .2, 1);
      position: relative;
    }
    #prog-bar::after { content: attr(data-pct); position: absolute; right: 0; top: -18px; font-size: 11px; color: var(--color-4); font-weight: 600; white-space: nowrap; }
    #form-banner {
      font-size: 14px;
      font-weight: 600;
      color: #2d7a3a;
      background: #eafaf1;
      border: 1px solid #a9dfbf;
      border-radius: 6px;
      padding: 12px 16px;
      margin-bottom: 18px;
      display: flex;
      align-items: center;
      gap: 8px;
      opacity: 0;
      transform: translateY(-6px);
      transition: opacity .3s, transform .3s;
      pointer-events: none;
    }
    #form-banner.show { opacity: 1; transform: translateY(0); pointer-events: auto; }
    .ck { font-size: 16px; }
    #err-banner {
      font-size: 13px;
      color: #a93226;
      background: #fdf2f2;
      border: 1px solid #f5c6c6;
      border-radius: 6px;
      padding: 10px 14px;
      margin-bottom: 16px;
    }
    #err-banner:empty { display: none; }
    @keyframes shake { 0%, 100% { transform: translateX(0); } 20%, 60% { transform: translateX(-5px); } 40%, 80% { transform: translateX(5px); } }
    .shake { animation: shake .35s ease; }
    .ripple { position: absolute; inset: 0; background: rgba(255, 255, 255, .35); border-radius: inherit; animation: rpl .55s ease forwards; }
    @keyframes rpl { from { opacity: 1; } to { opacity: 0; transform: scale(1.6); } }
    input[type="text"], input[type="email"], input[type="tel"], input[type="number"], input[type="date"], select, textarea { transition: border-color .18s, box-shadow .18s, transform .18s; }
  `;
  document.head.appendChild(style);
})();