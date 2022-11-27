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
  const sidebar = document.getElementById("sidebar");
  if (e.code == searchHotKey) {
    if (sidebar.offsetWidth < 1) {
      sidebar.style.display = "block";
    }
    return document.getElementById("sidebar-filter").focus();
  } else if (e.code == "Escape") {
    sidebar.style.display = sidebar.style.display == "block"
      ? "none"
      : "block";
    fetch(`?a=sidebar`, { credentials: "include" });
  }
});

// Filter counts
const filterLinks = document.getElementsByClassName("filter-link-count");
for (let link of filterLinks) {
  const title = link.dataset.title;
  fetch(`?a=filter_count&filter_count=${title}`)
    .then((res) => res.json())
    .then((res) => {
      link.innerHTML = res.total;
    });
}

// Menu button (toggle sidebar)
const menuButton = document.getElementById("menu-button");
menuButton.addEventListener("click", (e) => {
  const sidebar = document.getElementById("sidebar");
  if (sidebar.style.display == "none") {
      sidebar.style.display = "block";
  } else {
      sidebar.style.display = "none";
  }
  fetch(`?a=sidebar`, { credentials: "include" });
});

const profiler = document.getElementById("profiler-extra-info");
profiler.addEventListener("click", (e) => {
  const extra_info = document.getElementById("extra-info");
  if (extra_info.style.display == "block") {
    extra_info.style.display = "none";
  } else {
    extra_info.style.display = "block";
  }
});

// Utility
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
