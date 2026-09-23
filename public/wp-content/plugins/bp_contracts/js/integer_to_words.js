

// Helper arrays for number words
const UNITS = {
  male: ["", "один", "два", "три", "четыре", "пять", "шесть", "семь", "восемь", "девять"],
  female: ["", "одна", "две", "три", "четыре", "пять", "шесть", "семь", "восемь", "девять"]
};
const TEENS = ["десять", "одиннадцать", "двенадцать", "тринадцать", "четырнадцать", "пятнадцать", "шестнадцать", "семнадцать", "восемнадцать", "девятнадцать"];
const TENS = ["", "", "двадцать", "тридцать", "сорок", "пятьдесят", "шестьдесят", "семьдесят", "восемьдесят", "девяносто"];
const HUNDREDS = ["", "сто", "двести", "триста", "четыреста", "пятьсот", "шестьсот", "семьсот", "восемьсот", "девятьсот"];

// Forms for scale units and currency
const SCALE_UNITS = [
  { one: "", few: "", many: "", gender: "male" },
  { one: "тысяча", few: "тысячи", many: "тысяч", gender: "female" },
  { one: "миллион", few: "миллиона", many: "миллионов", gender: "male" },
  { one: "миллиард", few: "миллиарда", many: "миллиардов", gender: "male" }
];

// Function to choose correct form from one/few/many based on number
function chooseForm(number, forms) {
  const abs = Math.abs(number) % 100;
  const lastDigit = abs % 10;
  if (abs > 10 && abs < 20) return forms.many;
  if (lastDigit === 1) return forms.one;
  if (lastDigit > 1 && lastDigit < 5) return forms.few;
  return forms.many;
}

// Convert trio (0-999) into words
function trioToWords(num, gender) {
  let words = [];
  const h = Math.floor(num / 100);
  const t = Math.floor((num % 100) / 10);
  const u = num % 10;

  if (h) words.push(HUNDREDS[h]);
  if (t > 1) {
    words.push(TENS[t]);
    if (u) words.push(UNITS[gender][u]);
  } else if (t === 1) {
    words.push(TEENS[u]);
  } else if (u) {
    words.push(UNITS[gender][u]);
  }
  return words.join(' ');
}

// Main conversion: integer part to words with scale units
function integerToWords(num) {
  if (num === 0) return 'ноль';
  let words = [];
  const parts = [];
  let n = num;
  while (n > 0) {
    parts.push(n % 1000);
    n = Math.floor(n / 1000);
  }
  for (let i = parts.length - 1; i >= 0; i--) {
    const current = parts[i];
    if (!current) continue;
    const unit = SCALE_UNITS[i];
    words.push(trioToWords(current, unit.gender));
    const form = chooseForm(current, unit);
    if (form) words.push(form);
  }
  return words.join(' ');
}

/**
 * amountToRussianWords
 * Full representation: rubles in words + kopecks in words
 */
function amountToRussianWords(amount, isVAT = false) {
  const [intPart, fracPart] = parseFloat(amount).toFixed(2).split('.');
  const rubles = parseInt(intPart, 10);
  const kopecks = parseInt(fracPart, 10);

  const rubleWord = chooseForm(rubles, { one: 'рубль', few: 'рубля', many: 'рублей' });
  const kopeckWord = chooseForm(kopecks, { one: 'копейка', few: 'копейки', many: 'копеек' });

  const rublesText = integerToWords(rubles) + ' ' + rubleWord;
  const kopecksNew = isVAT ? kopecks + ' ' + kopeckWord : integerToWords(kopecks) + ' ' + kopeckWord;
  // const kopecksText = kopecksNew + ' ' + kopeckWord;
  const result = `${rublesText} ${kopecksNew}`;

  return result.replace(/^(.)/, m => m.toUpperCase());
}

/**
 * amountToRussianWordsVAT
 * Special for VAT: rubles in words + kopecks as digits
 * Example: 24394.25 -> "Двадцать четыре тысячи триста девяноста четыре рубля 25 копеек"
 */
function amountToRussianWordsVAT(amount) {
  const [intPart, fracPart] = parseFloat(amount).toFixed(2).split('.');
  const rubles = parseInt(intPart, 10);
  const kopecks = fracPart; // string, e.g. '25'

  const rubleWord = chooseForm(rubles, { one: 'рубль', few: 'рубля', many: 'рублей' });
  const kopeckWord = chooseForm(parseInt(kopecks, 10), { one: 'копейка', few: 'копейки', many: 'копеек' });

  const rublesText = integerToWords(rubles) + ' ' + rubleWord;
  const kopecksText = kopecks + ' ' + kopeckWord;

  const result = `${rublesText} ${kopecksText}`;
  return result.replace(/^(.)/, m => m.toUpperCase());
}

