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

  // Values injected by PHP
  const jsBlockedDates = window.blockedDates || [];
  const jsEarliestAllowedDate = window.earliestAllowedDate || null;
  const jsLeadDays = typeof window.leadDays !== 'undefined' ? Number(window.leadDays) : null;
  const jsItemsInCart = window.itemsInCart || [];
  const jsAccountType = window.accountType || 'Student';

  // --- utility ---
  function isHoliday(dateStr) {
    if (!dateStr) return false;
    const d = new Date(dateStr);
    if (isNaN(d)) return false;
    const ymd = dateStr; // 'yyyy-mm-dd'
    const md = ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2);
    for (const block of jsBlockedDates) {
      if (block.type === 'once') {
        if (ymd >= block.from && ymd <= block.to) return true;
      } else {
        if (md >= block.from && md <= block.to) return true;
      }
    }
    return false;
  }

  // compute lead days client-side (robust matching) — fallback when server values not available
  function computeLeadDays(items, accountType) {
    let lead = 0;
    items.forEach(item => {
      const type = String(item.product_type || item.productType || '').toLowerCase();
      if (type.includes('equip')) {
        lead = Math.max(lead, 0);
      } else if (type.includes('chem') || type.includes('supply') || type.includes('model')) {
        lead = Math.max(lead, 2);
      } else if (type.includes('specimen')) {
        lead = Math.max(lead, (accountType.toLowerCase() === 'student' ? 60 : 30));
      } else {
        lead = Math.max(lead, 0);
      }
    });
    return lead;
  }

  function addBusinessDaysJS(startDate, daysToAdd) {
    let d = new Date(startDate);
    let added = 0;
    while (added < daysToAdd) {
      d.setDate(d.getDate() + 1);
      const dow = d.getDay(); // 0 = Sunday
      const ymd = d.toISOString().split('T')[0];
      const md  = ('0'+(d.getMonth()+1)).slice(-2) + '-' + ('0'+d.getDate()).slice(-2);

      let isH = jsBlockedDates.some(block => {
        if (block.type === 'once') return (ymd >= block.from && ymd <= block.to);
        return (md >= block.from && md <= block.to);
      });

      if (dow !== 0 && !isH) {
        added++;
      }
    }
    return d.toISOString().split('T')[0];
  }

  const today = new Date().toISOString().split('T')[0];
  const clientLeadDays = computeLeadDays(jsItemsInCart, jsAccountType);
  const earliestDateClient = jsEarliestAllowedDate || addBusinessDaysJS(today, clientLeadDays);
  const earliestAllowed = jsEarliestAllowedDate || earliestDateClient;

  // --- set min attributes ---
  if (dateFrom) dateFrom.min = earliestAllowed;
  if (dateTo) dateTo.min = earliestAllowed;

  // --- validation helpers ---
  function alertAndClear(el, message) {
    alert(message);
    if (el) el.value = '';
  }

  // ✅ fixed: only block Sunday if it's start or end, holidays checked across whole range
  function dateRangeHasInvalidDay(startStr, endStr) {
    const start = new Date(startStr);
    const end = new Date(endStr);

    if (start.getDay() === 0) return { invalid: true, reason: 'Sunday', date: startStr };
    if (end.getDay() === 0) return { invalid: true, reason: 'Sunday', date: endStr };

    for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
      const ymd = d.toISOString().split('T')[0];
      if (isHoliday(ymd)) return { invalid: true, reason: 'Holiday', date: ymd };
    }
    return { invalid: false };
  }

  // --- date field event handlers ---
  if (dateFrom && dateTo) {
    dateFrom.addEventListener('change', () => {
      if (!dateFrom.value) return;

      // enforce earliestAllowed
      if (dateFrom.value < earliestAllowed) {
        alertAndClear(dateFrom, `Date From must be at least ${earliestAllowed} based on your cart.`);
        return;
      }

      // block Sundays (only if directly selected as start)
      const d = new Date(dateFrom.value);
      if (d.getDay() === 0) {
        alertAndClear(dateFrom, 'Sundays are not allowed. Please pick another date.');
        return;
      }

      // block holidays
      if (isHoliday(dateFrom.value)) {
        alertAndClear(dateFrom, 'That date is a holiday and cannot be selected.');
        return;
      }

      // set dateTo.min
      dateTo.min = dateFrom.value;

      if (dateTo.value && dateTo.value < dateFrom.value) {
        alertAndClear(dateTo, 'Date To cannot be earlier than Date From.');
      }

      // same-day equipment time check
      const lead = typeof jsLeadDays === 'number' ? jsLeadDays : clientLeadDays;
      if (lead === 0 && dateFrom.value === today) {
        const now = new Date();
        const hhmm = ('0'+now.getHours()).slice(-2) + ':' + ('0'+now.getMinutes()).slice(-2);
        if (timeFrom && timeFrom.value && timeFrom.value < hhmm) {
          alertAndClear(timeFrom, 'Time From must be later than the current time for same-day equipment requests.');
        }
      }
    });

    dateTo.addEventListener('change', () => {
      if (!dateTo.value) return;
      if (!dateFrom.value) {
        alertAndClear(dateTo, 'Please select Date From first.');
        return;
      }
      if (dateTo.value < dateFrom.value) {
        alertAndClear(dateTo, 'Date To cannot be earlier than Date From.');
        return;
      }
      const invalid = dateRangeHasInvalidDay(dateFrom.value, dateTo.value);
      if (invalid.invalid) {
        alertAndClear(dateTo, `Selected range includes a ${invalid.reason} (${invalid.date}). Please choose another range.`);
      }
    });
  }

  // --- TIME VALIDATION ---
  if (!timeFrom || !timeTo) {
    console.warn('Time inputs not found.');
  } else {
    const MIN_MINUTES = 7 * 60;
    const MAX_MINUTES = 19 * 60;

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
          alertAndClear(el, 'Invalid time format. Use HH:MM.');
          return;
        }
        if (mins < MIN_MINUTES || mins > MAX_MINUTES) {
          alertAndClear(el, 'Time must be between 07:00 and 19:00.');
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
        alertAndClear(timeTo, 'Time To cannot be earlier than Time From.');
      }
    }

    timeFrom.addEventListener('change', checkTimeOrder);
    timeTo.addEventListener('change', checkTimeOrder);
    timeFrom.addEventListener('blur', checkTimeOrder);
    timeTo.addEventListener('blur', checkTimeOrder);
  }

  if (dateFrom) dateFrom.setAttribute('onkeydown', 'return false;');
  if (dateTo) dateTo.setAttribute('onkeydown', 'return false;');
});
