// nav + mobile toggle
const navLinks  = document.querySelectorAll(".nav-links a");
const navList   = document.querySelector(".nav-links");
const navToggle = document.querySelector(".nav-toggle");

navLinks.forEach(link => {
  link.addEventListener("click", () => {
    navLinks.forEach(l => l.classList.remove("active"));
    link.classList.add("active");
    if (navList) navList.classList.remove("open");
  });
});

if (navToggle && navList) {
  navToggle.addEventListener("click", () => {
    navList.classList.toggle("open");
  });
}

// profile dropdown
const profileDropdown = document.querySelector(".profile-dropdown");
const profileTrigger  = document.querySelector(".profile-trigger");

if (profileDropdown && profileTrigger) {
  profileTrigger.addEventListener("click", (e) => {
    e.stopPropagation();
    const isOpen = profileDropdown.classList.toggle("open");
    profileTrigger.setAttribute("aria-expanded", isOpen ? "true" : "false");
  });

  document.addEventListener("click", (e) => {
    if (!profileDropdown.contains(e.target)) {
      profileDropdown.classList.remove("open");
      profileTrigger.setAttribute("aria-expanded", "false");
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      profileDropdown.classList.remove("open");
      profileTrigger.setAttribute("aria-expanded", "false");
    }
  });
}
