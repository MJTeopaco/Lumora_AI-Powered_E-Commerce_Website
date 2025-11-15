// ========== NAV: ACTIVE LINKS & MOBILE TOGGLE ==========
const navLinks = document.querySelectorAll('.nav-links a');
const navList = document.querySelector('.nav-links');
const navToggle = document.querySelector('.nav-toggle');
const searchInput = document.querySelector('.nav-search input');
const cartIcon = document.querySelector('.icon-btn[aria-label="Cart"]');
const userIcon = document.querySelector('.icon-btn[aria-label="Account"]');
const signInIcon = document.querySelector('.icon-btn[aria-label="Sign In"]');

// active link highlight
navLinks.forEach(link => {
  link.addEventListener('click', () => {
    navLinks.forEach(l => l.classList.remove('active'));
    link.classList.add('active');
    // close mobile menu on selection
    if (navList) navList.classList.remove('open');
  });
});

// mobile toggle
if (navToggle && navList) {
  navToggle.addEventListener('click', () => {
    navList.classList.toggle('open');
  });
}

// search input focus styling
if (searchInput) {
  searchInput.addEventListener('focus', () => {
    searchInput.style.borderColor = '#d4af37';
  });
  searchInput.addEventListener('blur', () => {
    searchInput.style.borderColor = '#ddd';
  });
}

// cart + account + sign in placeholders (only if present)
if (cartIcon) {
  cartIcon.addEventListener('click', () => {
    alert('Cart feature coming soon!');
  });
}

if (userIcon) {
  userIcon.addEventListener('click', () => {
    alert('Account area coming soon!');
  });
}

if (signInIcon) {
  signInIcon.addEventListener('click', () => {
    // this will still follow the link to login.php;
    // the alert is just a temporary UX placeholder
    // remove it once login is implemented
    // alert('Redirecting to sign in...');
  });
}


// ========== POPULAR CATEGORIES ==========
const categoryItems = document.querySelectorAll('.category-item');

categoryItems.forEach(item => {
  item.addEventListener('click', () => {
    categoryItems.forEach(i => i.classList.remove('is-active'));
    item.classList.add('is-active');

    const name = item.textContent.trim();
    alert(`Viewing ${name} category`);
  });
});


// ========== FEATURED PRODUCTS ==========
const featureButtons = document.querySelectorAll('.feature-btn');

featureButtons.forEach(btn => {
  btn.addEventListener('click', e => {
    e.preventDefault();
    const title = btn.parentElement.querySelector('h3')?.textContent ?? 'this item';
    alert(`Viewing: ${title}`);
  });
});


// ========== TRENDING PRODUCTS ==========
const productCards = document.querySelectorAll('.product-card');

productCards.forEach(card => {
  card.addEventListener('click', () => {
    const productName = card.querySelector('.product-name')?.textContent ?? 'this product';
    alert(`Viewing details for: ${productName}`);
  });
});


// ========== PROMO BANNER ==========
const promoBtn = document.querySelector('.promo-banner .btn');

if (promoBtn) {
  promoBtn.addEventListener('click', e => {
    // e.preventDefault();
    alert('Interested in selling on Lumora? We’ll guide you soon!');
  });
}


// ========== HERO CAROUSEL ==========

const track = document.querySelector('.carousel-track');
const slides = Array.from(document.querySelectorAll('.carousel-slide'));
const prevArrow = document.querySelector('.carousel-arrow.left');
const nextArrow = document.querySelector('.carousel-arrow.right');
const dots = Array.from(document.querySelectorAll('.carousel-dots .dot'));

let currentIndex = 0;
let autoSlideTimer = null;
const AUTO_SLIDE_DELAY = 2500; // 2.5 seconds

function goToSlide(index) {
  if (!track || slides.length === 0) return;

  const total = slides.length;
  const newIndex = (index + total) % total;

  track.style.transform = `translateX(-${newIndex * 100}%)`;

  slides.forEach(slide => slide.classList.remove('is-active'));
  dots.forEach(dot => dot.classList.remove('active'));

  slides[newIndex].classList.add('is-active');
  if (dots[newIndex]) dots[newIndex].classList.add('active');

  currentIndex = newIndex;
}

function nextSlide() {
  goToSlide(currentIndex + 1);
}

function prevSlide() {
  goToSlide(currentIndex - 1);
}

function startAutoSlide() {
  clearInterval(autoSlideTimer);
  autoSlideTimer = setInterval(nextSlide, AUTO_SLIDE_DELAY);
}

// arrows
if (nextArrow) {
  nextArrow.addEventListener('click', () => {
    nextSlide();
    startAutoSlide();
  });
}
if (prevArrow) {
  prevArrow.addEventListener('click', () => {
    prevSlide();
    startAutoSlide();
  });
}

// dots
dots.forEach((dot, index) => {
  dot.addEventListener('click', () => {
    goToSlide(index);
    startAutoSlide();
  });
});

// keyboard support
document.addEventListener('keydown', event => {
  if (!slides.length) return;

  if (event.key === 'ArrowRight') {
    nextSlide();
    startAutoSlide();
  } else if (event.key === 'ArrowLeft') {
    prevSlide();
    startAutoSlide();
  }
});

// init
if (slides.length > 0) {
  goToSlide(0);
  startAutoSlide();
}


// ========== SELLER PROMO SCROLL ANIMATION ==========
const sellerPromo = document.querySelector('.promo-seller');

if (sellerPromo && 'IntersectionObserver' in window) {
  const observer = new IntersectionObserver(
    (entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          sellerPromo.classList.add('visible');
          obs.unobserve(sellerPromo);
        }
      });
    },
    { threshold: 0.3 }
  );

  observer.observe(sellerPromo);
} else if (sellerPromo) {
  // Fallback: just show it if IntersectionObserver isn't supported
  sellerPromo.classList.add('visible');
}

const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('reveal-visible');
    }
  });
}, { threshold: 0.2 });

document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
/* =========================================
   READING PROGRESS BAR
========================================= */
const progressBar = document.getElementById("progressBar");

if (progressBar) {
  window.addEventListener("scroll", () => {
    const scrollTop = window.scrollY;
    const docHeight = document.body.scrollHeight - window.innerHeight;
    const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
    progressBar.style.width = progress + "%";
  });
}

/* =========================================
   BACK TO TOP BUTTON
========================================= */
const backToTop = document.getElementById("backToTop");

if (backToTop) {
  window.addEventListener("scroll", () => {
    const shouldShow = window.scrollY > 300;
    backToTop.classList.toggle("back-to-top-visible", shouldShow);
  });

  backToTop.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });
}

/* =========================================
   SECTION REVEAL ON SCROLL
========================================= */
const guidelineSections = document.querySelectorAll(".guidelines-section");
guidelineSections.forEach(sec => observer.observe(sec));

/* =========================================
   ACTIVE TOC HIGHLIGHT
========================================= */
const tocLinks = document.querySelectorAll(".guidelines-toc a");

const highlightObserver = new IntersectionObserver(
  entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        tocLinks.forEach(link => link.classList.remove("active-section"));
        const activeLink = document.querySelector(
          `.guidelines-toc a[href="#${entry.target.id}"]`
        );
        if (activeLink) activeLink.classList.add("active-section");
      }
    });
  },
  { threshold: 0.6 }
);

guidelineSections.forEach(section => highlightObserver.observe(section));

/* =========================================
   TOC SEARCH FILTER
========================================= */
const tocSearch = document.getElementById("tocSearch");

if (tocSearch) {
  tocSearch.addEventListener("input", () => {
    const filter = tocSearch.value.toLowerCase();
    tocLinks.forEach(link => {
      const text = link.textContent.toLowerCase();
      link.parentElement.style.display = text.includes(filter)
        ? "block"
        : "none";
    });
  });
}


// smooth scroll with header offset for TOC links
const header = document.querySelector('.site-header');
const tocLinksLocal = document.querySelectorAll('.guidelines-toc a');

tocLinksLocal.forEach(link => {
  link.addEventListener('click', e => {
    e.preventDefault();
    const targetId = link.getAttribute('href').substring(1);
    const target = document.getElementById(targetId);
    if (!target) return;

    const headerHeight = header ? header.offsetHeight : 0;
    const top = target.getBoundingClientRect().top + window.pageYOffset - (headerHeight + 16);

    window.scrollTo({ top, behavior: 'smooth' });
  });
});