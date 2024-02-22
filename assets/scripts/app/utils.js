/**
 * 
 * @param {string} text 
 * @returns string
 */
export const slugify = text =>
text
  .toString()
  .normalize('NFD')
  .replace(/[\u0300-\u036f]/g, '')
  .toLowerCase()
  .trim()
  .replace(/\s+/g, '-')
  .replace(/[^\w-]+/g, '')
  .replace(/--+/g, '-');

/**
 * 
 * @param {object} oSelect 
 * @param {string} valueToRemove 
 */
export function removeOptionByValue(oSelect, valueToRemove) {
  let options = oSelect.options;

  for(var i=0; i<options.length; i++) {
      if (options[i].value == valueToRemove) {
          oSelect.remove(i);
          break;
      }
  }
}

/**
 * 
 * @param {object} input 
 * @param {string|null} valueToShow 
 * @param {string|null} valueToSend 
 */
export function disableInput(input, valueToShow, valueToSend) {
  if (valueToShow) {
    input.value = valueToShow;
  }
  input.disabled = true;

  if (valueToSend) {
    let hiddenStatus = document.createElement('input');
    hiddenStatus.type = 'hidden';
    hiddenStatus.name = input.name;
    hiddenStatus.value = valueToSend;
    input.parentNode.appendChild(hiddenStatus);
  }
}

/**
 * Format bytes as human-readable text.
 * 
 * @param bytes Number of bytes.
 * @param si True to use metric (SI) units, aka powers of 1000. False to use 
 *           binary (IEC), aka powers of 1024.
 * @param dp Number of decimal places to display.
 * 
 * @return Formatted string.
 */
export function humanFileSize(bytes, si=false, dp=1) {
  const thresh = si ? 1000 : 1024;

  if (Math.abs(bytes) < thresh) {
    return bytes + ' B';
  }

  const units = si 
    ? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'] 
    : ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
  let u = -1;
  const r = 10**dp;

  do {
    bytes /= thresh;
    ++u;
  } while (Math.round(Math.abs(bytes) * r) / r >= thresh && u < units.length - 1);


  return bytes.toFixed(dp) + ' ' + units[u];
}