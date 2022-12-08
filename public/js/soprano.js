// Admin
// Tracks
const tracksPlayer = document.querySelector("#tracks-player audio");
const playButtons = document.getElementsByClassName("play-track");
for (button of playButtons) {
    button.addEventListener("click", (e) => {
        const md5 = e.currentTarget.dataset.md5;
        tracksPlayer.src = `http://hleucka.ddns.net/api/v1/music/play/${md5}`;
    });
}
