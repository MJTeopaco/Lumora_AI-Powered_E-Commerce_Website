/* =========================================
   SELLER GUIDELINES - JavaScript Module
   
   Features:
   - Reading progress bar
   - Back to top button
   - Section reveal animations
   - Active TOC highlighting
   - Smooth scroll with header offset
========================================= */

(function() {
  'use strict';

  // ========== READING PROGRESS BAR ==========
  const progressBar = document.getElementById("progressBar");

  if (progressBar) {
    const updateProgressBar = () => {
      const scrollTop = window.scrollY;
      const docHeight = document.body.scrollHeight - window.innerHeight;
      const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
      progressBar.style.width = progress + "%";
    };

    window.addEventListener("scroll", updateProgressBar);
    // Initialize on load
    updateProgressBar();
  }

  // ========== BACK TO TOP BUTTON ==========
  const backToTop = document.getElementById("backToTop");

  if (backToTop) {
    const toggleBackToTop = () => {
      const shouldShow = window.scrollY > 300;
      backToTop.classList.toggle("back-to-top-visible", shouldShow);
    };

    window.addEventListener("scroll", toggleBackToTop);
    // Initialize on load
    toggleBackToTop();

    backToTop.addEventListener("click", () => {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  // ========== SECTION REVEAL ON SCROLL ==========
  const guidelineSections = document.querySelectorAll(".guidelines-section");

  if (guidelineSections.length > 0 && 'IntersectionObserver' in window) {
    const revealObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('reveal-visible');
          }
        });
      },
      { threshold: 0.2 }
    );

    guidelineSections.forEach(section => revealObserver.observe(section));
  }

  // ========== ACTIVE TOC HIGHLIGHTING ==========
  const tocLinks = document.querySelectorAll(".guidelines-toc a");

  if (tocLinks.length > 0 && guidelineSections.length > 0 && 'IntersectionObserver' in window) {
    const highlightObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting && entry.intersectionRatio >= 0.5) {
            // Remove active class from all links
            tocLinks.forEach(link => link.classList.remove("active-section"));
            
            // Add active class to the corresponding TOC link
            const activeLink = document.querySelector(
              `.guidelines-toc a[href="#${entry.target.id}"]`
            );
            if (activeLink) {
              activeLink.classList.add("active-section");
            }
          }
        });
      },
      { 
        threshold: 0.5,
        rootMargin: "-100px 0px -50% 0px"
      }
    );

    guidelineSections.forEach(section => {
      if (section.id) {
        highlightObserver.observe(section);
      }
    });
  }

  // ========== SMOOTH SCROLL WITH HEADER OFFSET ==========
  const header = document.querySelector('.header') || document.querySelector('.site-header');

  tocLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      
      const targetId = link.getAttribute('href').substring(1);
      const target = document.getElementById(targetId);
      
      if (!target) return;

      const headerHeight = header ? header.offsetHeight : 0;
      const offset = 20; // Additional offset for breathing room
      const top = target.getBoundingClientRect().top + window.pageYOffset - (headerHeight + offset);

      window.scrollTo({ 
        top, 
        behavior: 'smooth' 
      });

      // Update active state immediately
      tocLinks.forEach(l => l.classList.remove('active-section'));
      link.classList.add('active-section');
    });
  });

  // ========== KEYBOARD NAVIGATION ==========
  document.addEventListener('keydown', (e) => {
    // ESC key - scroll to top
    if (e.key === 'Escape' && window.scrollY > 300) {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  });

  // ========== PRINT STYLES ==========
  // Hide progress bar and back-to-top button when printing
  if (window.matchMedia) {
    const mediaQueryList = window.matchMedia('print');
    
    const handlePrint = (mql) => {
      if (mql.matches) {
        if (progressBar) progressBar.style.display = 'none';
        if (backToTop) backToTop.style.display = 'none';
      }
    };
    
    mediaQueryList.addListener(handlePrint);
  }

  // ========== INITIALIZATION LOG ==========
  console.log('✓ Seller Guidelines module loaded');

})();