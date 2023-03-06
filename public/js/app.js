const news = document.querySelectorAll(".news");

for (let post of news) {
  post
    .querySelector(".fa-floppy-disk")
    .addEventListener("click", (e) => this.message);
}

function message(e) {
  alert("ok");
}
