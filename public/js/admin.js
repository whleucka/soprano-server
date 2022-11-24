// Vim type search binding
const searchHotKey = "Slash";
const searchInput = document.getElementById("sidebar-filter");
const sidebarFilter = (term) => {
  const sidebarLinks = document.getElementsByClassName("sidebar-link");
  let links = [];
  for (let link of sidebarLinks) {
    const module_name = link.dataset.module;
    const match = module_name.match(term.trim().toLowerCase());
    link.style.display = match ? "block" : "none";
    link.tabIndex = match ? 0 : -1;
    if (match) {
      links.push(link);
    }
  }
  return links;
};
searchInput.addEventListener("input", (e) => {
  const value = e.target.value;
  sidebarFilter(value);
});
searchInput.addEventListener("keyup", (e) => {
  if (e.code == "Enter") {
    const value = e.target.value;
    const sidebar_links = sidebarFilter(value);
    // Click the filtered link
    if (sidebar_links.length == 1) {
      sidebar_links[0].click();
    }
  }
});
// Focus on keyup binding
addEventListener("keyup", (e) => {
  if (e.code == searchHotKey) {
    const sidebar = document.getElementById("sidebar");
    if (sidebar.offsetWidth < 1) {
      sidebar.style.display = "block";
    }
    return document.getElementById("sidebar-filter").focus();
  }
});

// Filter counts
const filterLinks = document.getElementsByClassName("filter-link-count");
for (let link of filterLinks) {
  const title = link.dataset.title;
  fetch(`?a=filter_count&filter_count=${title}`, { credentials: "include" })
    .then((res) => res.json())
    .then((res) => {
      link.innerHTML = res.total;
    });
}

// Menu button (toggle sidebar)
const menuButton = document.getElementById("menu-button");
menuButton.addEventListener("click", (e) => {
  const sidebar = document.getElementById("sidebar");
  fetch(`?a=sidebar`)
    .then((res) => res.json())
    .then((res) => {
      sidebar.style.display = res.setting == "1" ? "none" : "block";
    });
});

// Utility
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
