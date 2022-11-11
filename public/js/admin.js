// Vim type search binding
const searchHotKey = "Slash";
const searchInput = document.getElementById("sidebar-filter");
const sidebarFilter = (term) => {
  const sidebarLinks = document.getElementsByClassName("sidebar-link");
  let links = [];
  for (let link of sidebarLinks) {
    const title = link.dataset.title;
    const match = title.match(term.trim().toLowerCase());
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
    return document.getElementById("sidebar-filter").focus();
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

// Utility
const sleep = ms => new Promise(r => setTimeout(r, ms));

// Fancy confirmation dialogs
const listDeleteConfirm = (form) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'royalblue',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire(
                'Deleted!',
                'Your file has been deleted.',
                'success'
            ).then((ok) => {
                form.submit();
            });
        }
    });
}
const editSaveConfirm = (form) => {
    Swal.fire({
        title: 'Do you want to save the changes?',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Save',
        confirmButtonColor: 'royalblue',
        cancelButtonColor: '#d33',
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}
