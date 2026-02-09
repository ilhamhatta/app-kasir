import { ref } from "vue";

export const isDark = ref(false);

export function initDarkMode() {
  const apply = (theme) => {
    document.documentElement.classList.toggle("dark", theme === "dark");
    isDark.value = theme === "dark";
    localStorage.setItem("theme", theme);
  };

  const saved = localStorage.getItem("theme");
  if (saved) {
    apply(saved);
  } else {
    const prefersDark = window.matchMedia(
      "(prefers-color-scheme: dark)",
    ).matches;
    apply(prefersDark ? "dark" : "light");
  }
}

export function toggleDark() {
  const next = isDark.value ? "light" : "dark";
  document.documentElement.classList.toggle("dark", next === "dark");
  isDark.value = next === "dark";
  localStorage.setItem("theme", next);
}
