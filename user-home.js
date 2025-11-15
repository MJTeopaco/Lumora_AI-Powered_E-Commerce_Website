// ========== NAV: ACTIVE LINKS & MOBILE TOGGLE ==========
const navLinks  = document.querySelectorAll(".nav-links a");
const navList   = document.querySelector(".nav-links");
const navToggle = document.querySelector(".nav-toggle");

// active link highlight
navLinks.forEach(link => {
  link.addEventListener("click", () => {
    navLinks.forEach(l => l.classList.remove("active"));
    link.classList.add("active");
    if (navList) navList.classList.remove("open");
  });
});

// mobile toggle
if (navToggle && navList) {
  navToggle.addEventListener("click", () => {
    navList.classList.toggle("open");
  });
}

// ========== PROFILE DROPDOWN ==========
const profileDropdown = document.querySelector(".profile-dropdown");
const profileTrigger  = document.querySelector(".profile-trigger");

if (profileDropdown && profileTrigger) {
  profileTrigger.addEventListener("click", (e) => {
    e.stopPropagation();
    const isOpen = profileDropdown.classList.toggle("open");
    profileTrigger.setAttribute("aria-expanded", isOpen ? "true" : "false");
  });

  // close on click outside
  document.addEventListener("click", (e) => {
    if (!profileDropdown.contains(e.target)) {
      profileDropdown.classList.remove("open");
      profileTrigger.setAttribute("aria-expanded", "false");
    }
  });

  // close on Escape
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      profileDropdown.classList.remove("open");
      profileTrigger.setAttribute("aria-expanded", "false");
    }
  });
}

// ========== RECOMMENDED PRODUCT BUTTONS ==========
document.querySelectorAll(".user-product-btn").forEach(btn => {
  btn.addEventListener("click", () => {
    const card = btn.closest(".user-product-card");
    const name = card?.querySelector("h3")?.textContent ?? "this item";
    alert(`Product detail page coming soon for: ${name}`);
  });
});
