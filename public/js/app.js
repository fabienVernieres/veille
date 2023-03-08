const news = document.querySelectorAll(".news");
const favorites = document.querySelectorAll(".favorites");
const origin = document.location.origin;

// On boucle sur les news et on écoute les événements click
for (let post of news) {
  const postDate = post.querySelector(".news-date").innerHTML;
  const postTitle = post.querySelector(".news-title").innerHTML;
  const postDescription = post.querySelector(".news-description").innerHTML;
  const postLink = post.querySelector(".news-link").href;

  const postSaveButton = post.querySelector(".fa-floppy-disk").parentElement;

  // Exécute la fonction copyPost au clique sur le bouton de sauvegarde du post
  postSaveButton.addEventListener("click", (e) =>
    copyPost(postSaveButton, postDate, postTitle, postDescription, postLink)
  );
}

// Envoie le post au contrôleur et récupère le post au format JSON
function copyPost(
  postSaveButton,
  postDate,
  postTitle,
  postDescription,
  postLink
) {
  // On supprime le bouton
  postSaveButton.remove();

  // On crée le post
  let post = {
    title: postTitle,
    description: postDescription,
    link: postLink,
    createdAt: postDate,
  };

  // On envoie le post au contrôleur et on récupère le code de la réponse
  async function postData(url = "", post = "") {
    const response = await fetch(url, {
      method: "POST",
      body: JSON.stringify(post),
      headers: {
        "Content-Type": "application/json",
      },
    });
    return response.status;
  }

  postData(origin + "/post/new", post).then((data) => {
    if (data === 201) {
      alert("Article sauvegardé dans vos favoris");
    } else {
      alert("Vous avez déjà sauvegardé cet article");
    }
  });
}

// On ajoute un événement sur le bouton de suppression de favori
for (let favorite of favorites) {
  const favoriteButton = favorite.querySelector(".fa-trash").parentElement;

  // Exécute la fonction deletePost au clique sur le bouton de suppression du post
  favoriteButton.addEventListener("click", (e) =>
    deletePost(favorite, favorite.id)
  );
}

function deletePost(favorite, postId) {
  favorite.remove();

  // On envoie le post au contrôleur et on récupère le code de la réponse
  let post = {
    id: postId,
  };

  async function postData(url = "", post = "") {
    const response = await fetch(url, {
      method: "POST",
      body: JSON.stringify(post),
      headers: {
        "Content-Type": "application/json",
      },
    });
    return response.status;
  }

  postData(origin + "/post/delete", post).then((data) => {
    if (data === 204) {
      alert("Article supprimé de vos favoris");
    }
  });
}
