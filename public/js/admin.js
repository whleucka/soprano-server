/**
 * Sidebar filter
 */

// Vim type search binding
const searchHotKey = "Slash";
const searchInput = document.getElementById("sidebar-filter");
const sidebarFilter = (term) => {
    const links = document.getElementsByClassName("sidebar-link");
    let count = 0;
    let sidebar_links = [];
    for (let link of links) {
        const title = link.dataset.title;
        const match = title.match(term.trim().toLowerCase());
        link.style.display = match ? "block" : "none";
        link.tabIndex = match ? 0 : -1;
        count += match ? 1 : 0;
        if (match) {
            sidebar_links.push(link);
        }
    }
    return sidebar_links;
};
searchInput.addEventListener("input", function (e) {
    const value = e.target.value;
    sidebarFilter(value);
});
searchInput.addEventListener("keyup", function (e) {
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
addEventListener("keyup", function (e) {
    if (e.code == searchHotKey) {
        return document.getElementById("sidebar-filter").focus();
    }
});
