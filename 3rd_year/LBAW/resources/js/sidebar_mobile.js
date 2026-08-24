export function initSidebarMobile(options = {}) {
  const {
    sidebarId = "sidebar",
    openBtnId = "openSidebar",     
    overlayId = "sidebarOverlay", 
    closedClass = "-translate-x-full",
    closeOnEsc = true,
    closeOnLinkClick = false,
    durationMs = 300,       
  } = options;

  const sidebar = document.getElementById(sidebarId);
  const openBtn = document.getElementById(openBtnId);
  const overlay = document.getElementById(overlayId);

  if (!sidebar || !openBtn || !overlay) return;

  if (sidebar.dataset.sidebarMobileInit === "1") return;
  sidebar.dataset.sidebarMobileInit = "1";

  const open = () => {
    sidebar.classList.remove(closedClass);

    overlay.classList.remove("hidden");
    requestAnimationFrame(() => overlay.classList.remove("opacity-0"));
  };

  const close = () => {
    sidebar.classList.add(closedClass);

    overlay.classList.add("opacity-0");
    window.setTimeout(() => overlay.classList.add("hidden"), durationMs);
  };

  openBtn.addEventListener("click", open);
  overlay.addEventListener("click", close);

  if (closeOnEsc) {
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") close();
    });
  }

  if (closeOnLinkClick) {
    sidebar.addEventListener("click", (e) => {
      const a = e.target.closest("a");
      if (!a) return;
      if (window.matchMedia("(max-width: 767px)").matches) close();
    });
  }

  return { open, close };
}
