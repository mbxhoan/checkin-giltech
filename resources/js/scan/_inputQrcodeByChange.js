"use-strict";

import { checkinOffline } from "./_checkinOffline";
import { checkinOnline } from "./_checkinOnline";

const MODE_KEYBOARD = 'keyboard';
const MODE_CLIPBOARD = 'clipboard';
const STORAGE_KEY = 'scan_input_mode';
const KEYBOARD_TIMEOUT = 220;

let inputEl = null;
let keyboardBuffer = '';
let keyboardTimer = null;
let currentMode = localStorage.getItem(STORAGE_KEY) || MODE_KEYBOARD;
let keyboardListenersBound = false;
let clipboardListenersBound = false;

export const inputQrcodeByChange = () => {
  inputEl = document.getElementById('qrcode');
  if (!inputEl) return;

  prepareInput();
  bindModeButtons();
  bindKeyboardCapture();
  bindClipboardCapture();
  applyMode(currentMode, false);
};

const prepareInput = () => {
  inputEl.setAttribute('autocomplete', 'off');
  inputEl.setAttribute('autocorrect', 'off');
  inputEl.setAttribute('autocapitalize', 'off');
  inputEl.setAttribute('spellcheck', 'false');
  inputEl.setAttribute('inputmode', 'none');
  inputEl.setAttribute('virtualkeyboardpolicy', 'manual');
  inputEl.setAttribute('readonly', 'readonly');
};

const bindModeButtons = () => {
  $('#btn-scan-mode-keyboard').on('click', function (e) {
    e.preventDefault();
    applyMode(MODE_KEYBOARD);
  });

  $('#btn-scan-mode-clipboard').on('click', function (e) {
    e.preventDefault();
    applyMode(MODE_CLIPBOARD);
  });

  $('#btn-read-clipboard').on('click', function (e) {
    e.preventDefault();
    if (currentMode !== MODE_CLIPBOARD) {
      applyMode(MODE_CLIPBOARD);
    }
    readClipboardOnce();
  });
};

const applyMode = (mode, save = true) => {
  currentMode = [MODE_KEYBOARD, MODE_CLIPBOARD].includes(mode) ? mode : MODE_KEYBOARD;

  if (save) {
    localStorage.setItem(STORAGE_KEY, currentMode);
  }

  updateModeUi();

  if (currentMode === MODE_KEYBOARD) {
    startKeyboardMode();
  } else {
    stopKeyboardMode();
    hideVirtualKeyboard();
    if (inputEl) {
      inputEl.blur();
    }
  }
};

const updateModeUi = () => {
  const keyboardBtn = $('#btn-scan-mode-keyboard');
  const clipboardBtn = $('#btn-scan-mode-clipboard');
  const indicator = $('#scan-input-mode-indicator');

  if (keyboardBtn.length) {
    keyboardBtn
      .toggleClass('btn-warning text-dark', currentMode === MODE_KEYBOARD)
      .toggleClass('btn-outline-secondary', currentMode !== MODE_KEYBOARD)
      .attr('aria-pressed', currentMode === MODE_KEYBOARD ? 'true' : 'false');
  }

  if (clipboardBtn.length) {
    clipboardBtn
      .toggleClass('btn-info text-dark', currentMode === MODE_CLIPBOARD)
      .toggleClass('btn-outline-secondary', currentMode !== MODE_CLIPBOARD)
      .attr('aria-pressed', currentMode === MODE_CLIPBOARD ? 'true' : 'false');
  }

  if (indicator.length) {
    indicator.text(currentMode === MODE_KEYBOARD ? 'KB' : 'CLIP');
  }
};

const startKeyboardMode = () => {
  if (!inputEl) return;

  inputEl.setAttribute('readonly', 'readonly');
  focusScannerInput();
};

const stopKeyboardMode = () => {
  keyboardBuffer = '';

  if (keyboardTimer) {
    clearTimeout(keyboardTimer);
    keyboardTimer = null;
  }
};

const bindKeyboardCapture = () => {
  if (keyboardListenersBound) return;

  if (inputEl) {
    inputEl.addEventListener('touchstart', suppressTouchKeyboard, { passive: false });
    inputEl.addEventListener('mousedown', suppressTouchKeyboard);
    inputEl.addEventListener('focus', () => {
      if (currentMode === MODE_KEYBOARD) {
        hideVirtualKeyboard();
      }
    });
    inputEl.addEventListener('blur', () => {
      if (currentMode === MODE_KEYBOARD) {
        setTimeout(focusScannerInput, 50);
      }
    });
  }

  document.addEventListener('keydown', onKeyboardKeydown, true);

  keyboardListenersBound = true;
};

const onKeyboardKeydown = (e) => {
  if (currentMode !== MODE_KEYBOARD || !inputEl) return;
  if (document.activeElement !== inputEl) return;

  const key = e.key;

  if (key === 'Enter' || key === 'Tab') {
    e.preventDefault();
    finalizeKeyboardScan();
    return;
  }

  if (key === 'Backspace') {
    keyboardBuffer = keyboardBuffer.slice(0, -1);
    e.preventDefault();
    return;
  }

  if (key.length === 1) {
    keyboardBuffer += key;
    e.preventDefault();
    clearTimeout(keyboardTimer);
    keyboardTimer = setTimeout(finalizeKeyboardScan, KEYBOARD_TIMEOUT);
  }
};

const finalizeKeyboardScan = () => {
  clearTimeout(keyboardTimer);
  keyboardTimer = null;

  const qrcode = keyboardBuffer.trim();
  keyboardBuffer = '';

  if (!qrcode) return;

  submitScan(qrcode);
};

const bindClipboardCapture = () => {
  if (clipboardListenersBound) return;

  document.addEventListener('paste', (e) => {
    if (currentMode !== MODE_CLIPBOARD) return;

    const pastedData = (e.clipboardData || window.clipboardData)?.getData('text') || '';
    if (!pastedData) return;

    e.preventDefault();
    handleClipboardText(pastedData);
  });

  document.addEventListener('keydown', (e) => {
    if (currentMode !== MODE_CLIPBOARD) return;

    if (e.key === 'F9') {
      e.preventDefault();
      readClipboardOnce();
    }
  });

  clipboardListenersBound = true;
};

const readClipboardOnce = async () => {
  if (!navigator.clipboard || !navigator.clipboard.readText) {
    if (window.toastr) {
      toastr.error('Trình duyệt không hỗ trợ đọc clipboard.');
    }
    return;
  }

  try {
    const text = await navigator.clipboard.readText();
    handleClipboardText(text);
  } catch (error) {
    console.error(error);
    if (window.toastr) {
      toastr.error('Không thể đọc clipboard. Hãy kiểm tra quyền truy cập.');
    }
  }
};

const handleClipboardText = (text) => {
  const codes = splitCodes(text);
  if (!codes.length) return;

  codes.forEach((code) => {
    submitScan(code);
  });
};

const splitCodes = (text) => {
  const value = (text || '').trim();
  if (!value) return [];

  if (/\r?\n/.test(value)) {
    return value.split(/\r?\n+/).map((item) => item.trim()).filter(Boolean);
  }

  if (value.includes(';')) {
    return value.split(';').map((item) => item.trim()).filter(Boolean);
  }

  if (value.includes(',')) {
    return value.split(',').map((item) => item.trim()).filter(Boolean);
  }

  return [value];
};

const submitScan = (qrcode) => {
  if (!qrcode) return;

  if ($('#toggle_online').prop('checked')) {
    checkinOnline(qrcode, false);
    $('#offline-offcanvas').removeClass('text-danger');
  } else {
    checkinOffline(qrcode);
  }

  if (inputEl) {
    inputEl.value = '';
  }
};

const focusScannerInput = () => {
  if (currentMode !== MODE_KEYBOARD || !inputEl) return;

  try {
    inputEl.focus({ preventScroll: true });
  } catch (_) {
    inputEl.focus();
  }

  hideVirtualKeyboard();
};

const suppressTouchKeyboard = (e) => {
  if (currentMode !== MODE_KEYBOARD) return;

  e.preventDefault();
  focusScannerInput();
};

const hideVirtualKeyboard = () => {
  try {
    if (navigator.virtualKeyboard && typeof navigator.virtualKeyboard.hide === 'function') {
      navigator.virtualKeyboard.hide();
    }
  } catch (_) {}
};
