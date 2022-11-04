/**
 * Sidebar filter
 */
// Vim type search binding
const searchHotKey = ["/", "Slash"];
const searchInput = document.getElementById("sidebar-filter");
searchInput.addEventListener("input", function (e) {
  const term = e.target.value;
  if (term == searchHotKey[0]) {
    this.value = "";
    return;
  }
  const links = document.getElementsByClassName("sidebar-link");
  for (let link of links) {
    const title = link.dataset.title;
    const match = title.match(term.trim().toLowerCase());
    link.style.display = match ? "block" : "none";
    link.tabIndex = match ? 0 : -1;
  }
});
// Focus on keypress binding
addEventListener("keydown", function (e) {
    if (e.code == searchHotKey[1]) {
        return document.getElementById("sidebar-filter").focus();
    }
});
