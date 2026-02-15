// utils.js
export async function loadComponent(path, containerId, clear = false) {
  const res = await fetch(path);
  const html = await res.text();
  const container = document.getElementById(containerId);
  if (clear) container.innerHTML = ""; // remove previous content
  container.insertAdjacentHTML("beforeend", html);
}
