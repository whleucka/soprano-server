/**
 * Sidebar search
 */
const searchInput = document.getElementById("sidebar-search");
searchInput.addEventListener("input", function(e) {
    const term = e.target.value;
    const links = document.getElementsByClassName("sidebar-link");
    for (let link of links) {
        const title = link.dataset.title;
        const match = title.match(term.trim().toLowerCase())
        link.style.display = match
            ? 'block'
            : 'none';
        link.tabIndex = match
            ? 0
            : -1;
    }
});
