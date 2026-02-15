/* ===== 0.  Helpers ===== */
const $ = sel => document.querySelector(sel);
const $$ = sel => [...document.querySelectorAll(sel)];

/* ===== 1.  Mobile nav toggle ===== */
const topNav   = $('#top');
const navLinks = $('#nav');
const hamburger = document.createElement('button');
hamburger.className = 'hamburger';
hamburger.innerHTML = '☰';
topNav.prepend(hamburger);

const mq = window.matchMedia('(max-width: 600px)');
function toggleHamburger(force) {
  navLinks.classList.toggle('show', force);
  hamburger.classList.toggle('active', force);
}
hamburger.addEventListener('click', () => toggleHamburger());
mq.addEventListener('change', e => !e.matches && toggleHamburger(false));

/* ===== 2.  Smooth scroll ===== */
$$('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    e.preventDefault();
    const target = document.getElementById(a.getAttribute('href').slice(1));
    target?.scrollIntoView({ behavior: 'smooth' });
    toggleHamburger(false);               // close mobile nav
  });
});

/* ===== 3.  Product swap on small-cup click ===== */
const cups   = $$('#selection .outer-circle');
const bigImg = $('#greenBg img');
const nameEl = $('#CoffeeName');
const descEl = $('#description');
const priceEl = $('#ratingVal');

const products = [
  {
    name: 'Classic Ice Coffee',
    desc: 'Indulge in the bold, smooth taste of Starbucks Iced Coffee—perfectly chilled, refreshingly energizing.',
    price: '$8.6',
    img:  'assets/ClassicCup.png'
  },
  {
    name: 'Caramel Latte',
    desc: 'Rich espresso balanced with velvety caramel and steamed milk, finished with whipped cream.',
    price: '$9.2',
    img:  'assets/ClassicCup.png'
  },
  {
    name: 'Mocha Frappé',
    desc: 'Coffee, milk and ice blended with decadent mocha sauce and topped with whipped cream.',
    price: '$9.8',
    img:  'assets/ClassicCup.png'
  }
];

function setProduct(idx) {
  const p = products[idx];
  nameEl.textContent   = p.name;
  descEl.textContent   = p.desc;
  priceEl.textContent  = p.price;
  bigImg.src           = p.img;
  cups.forEach((c, i) => c.classList.toggle('active', i === idx));
}
cups.forEach((cup, idx) => cup.addEventListener('click', () => setProduct(idx)));

/* ===== 4.  Auto carousel for small cups (pause on hover) ===== */
let current = 0;
let timer   = setInterval(() => { current = (current + 1) % 3; setProduct(current); }, 4000);
const sel   = $('#selection');
sel.addEventListener('mouseenter', () => clearInterval(timer));
sel.addEventListener('mouseleave', () => timer = setInterval(() => { current = (current + 1) % 3; setProduct(current); }, 4000));

/* ===== 5.  Initial state ===== */
setProduct(0);