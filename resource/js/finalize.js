document.addEventListener('DOMContentLoaded', function () {
  // helper to find element by several possible ids/names
  function findEl(...ids) {
    for (const id of ids) {
      if (!id) continue;
      let el = document.getElementById(id);
      if (el) return el;
      el = document.querySelector(`[name="${id}"]`);
      if (el) return el;
      const alt = id.includes('_') ? id.replace(/_/g, '-') : id.replace(/-/g, '_');
      el = document.getElementById(alt) || document.querySelector(`[name="${alt}"]`);
      if (el) return el;
    }
    return null;
  }

  const dateFrom = findEl('date_from', 'date-from');
  const dateTo = findEl('date_to', 'date-to');
  const timeFrom = findEl('time_from', 'time-from');
  const timeTo = findEl('time_to', 'time-to');

  if (!dateFrom || !dateTo) {
    console.warn('Date inputs not found.');
  } else {
    // set min date to today
    const today = new Date().toISOString().split('T')[0];
    dateFrom.min = today;
    dateTo.min = today;

    dateFrom.setAttribute('onkeydown', 'return false;');
    dateTo.setAttribute('onkeydown', 'return false;');

    // --- HOLIDAY CHECK ---
    function isHoliday(dateStr) {
      if (!dateStr) return false;
      const d = new Date(dateStr);
      if (isNaN(d)) return false;

      const ymd = dateStr; // yyyy-mm-dd
      const md = ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2);

      for (const block of blockedDates) {
        if (block.type === 'once') {
          if (ymd >= block.from && ymd <= block.to) return true;
        } else {
          if (md >= block.from && md <= block.to) return true;
        }
      }
      return false;
    }

    function checkDate(input) {
      if (!input.value) return;
      const parts = input.value.split('-');
      if (parts.length !== 3) return;
      const d = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
      if (d.getDay() === 0) {
        alert('Sundays are not allowed. Please pick another date.');
        input.value = '';
        return;
      }
      if (isHoliday(input.value)) {
        alert('That date is a holiday and cannot be selected.');
        input.value = '';
      }
    }

    // ✅ use change instead of input
    dateFrom.addEventListener('change', () => {
      checkDate(dateFrom);
      if (dateFrom.value) {
        dateTo.min = dateFrom.value;
        if (dateTo.value && dateTo.value < dateFrom.value) {
          dateTo.value = '';
          alert('Date To cannot be earlier than Date From.');
        }
      }
    });

    dateTo.addEventListener('change', () => {
      checkDate(dateTo);
      if (dateTo.value && dateFrom.value && dateTo.value < dateFrom.value) {
        alert('Date To cannot be earlier than Date From.');
        dateTo.value = '';
      }
    });
  }

  // --- TIME VALIDATION ---
  if (!timeFrom || !timeTo) {
    console.warn('Time inputs not found.');
    return;
  }

  const MIN_MINUTES = 7 * 60;   // 07:00
  const MAX_MINUTES = 19 * 60;  // 19:00

  function timeStrToMinutes(t) {
    if (!t || typeof t !== 'string') return null;
    const m = t.match(/^(\d{1,2}):(\d{2})$/);
    if (!m) return null;
    const hh = parseInt(m[1], 10);
    const mm = parseInt(m[2], 10);
    if (hh < 0 || hh > 23 || mm < 0 || mm > 59) return null;
    return hh * 60 + mm;
  }

  function validateSingleTimeField(el) {
    el.addEventListener('input', () => {
      if (!el.value) return;
      const mins = timeStrToMinutes(el.value);
      if (mins === null) {
        alert('Invalid time format. Use HH:MM.');
        el.value = '';
        return;
      }
      if (mins < MIN_MINUTES || mins > MAX_MINUTES) {
        alert('Time must be between 07:00 AM and 7:00 PM.');
        el.value = '';
      }
    });
    el.addEventListener('blur', () => {
      if (!el.value) return;
      const mins = timeStrToMinutes(el.value);
      if (mins === null || mins < MIN_MINUTES || mins > MAX_MINUTES) {
        el.value = '';
      }
    });
  }

  validateSingleTimeField(timeFrom);
  validateSingleTimeField(timeTo);

  function checkTimeOrder() {
    if (!timeFrom.value || !timeTo.value) return;
    const a = timeStrToMinutes(timeFrom.value);
    const b = timeStrToMinutes(timeTo.value);
    if (a === null || b === null) return;
    if (b < a) {
      alert('Time To cannot be earlier than Time From.');
      timeTo.value = '';
    }
  }

  timeFrom.addEventListener('change', checkTimeOrder);
  timeTo.addEventListener('change', checkTimeOrder);
  timeFrom.addEventListener('blur', checkTimeOrder);
  timeTo.addEventListener('blur', checkTimeOrder);
});
