/**
 * Sidebar filter
 */
// Vim type search binding
const searchHotKey = "Slash";
const searchInput = document.getElementById("sidebar-filter");
searchInput.addEventListener("input", function (e) {
  const term = e.target.value;
  const links = document.getElementsByClassName("sidebar-link");
  for (let link of links) {
    const title = link.dataset.title;
    const match = title.match(term.trim().toLowerCase());
    link.style.display = match ? "block" : "none";
    link.tabIndex = match ? 0 : -1;
  }
});
// Focus on keyup binding
addEventListener("keyup", function (e) {
  if (e.code == searchHotKey) {
    return document.getElementById("sidebar-filter").focus();
  }
});
