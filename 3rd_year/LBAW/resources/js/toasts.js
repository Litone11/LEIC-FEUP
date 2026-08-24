export function mountToasts() {
  const root = document.getElementById("toast-root");
  if (!root) return;

  const notif = document.getElementById("notification-toast");
  if (notif && !root.contains(notif)) root.appendChild(notif);

  const flash = document.getElementById("flash-toast");
  if (flash && !root.contains(flash)) root.appendChild(flash);
}
