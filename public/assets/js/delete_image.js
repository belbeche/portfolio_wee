window.onload = () => {
    // Gestion des boutons "Supprimer"
    let links = document.querySelectorAll("[data-delete]");

    // Parcourir les liens
    for (link of links) {
        // Écouter le clic
        link.addEventListener("click", function (e) {
            // Empêcher la navigation
            e.preventDefault();

            // Demander une confirmation
            if (confirm("Voulez-vous supprimer cette image ?")) {
                // Envoyer une requête Ajax vers l'URL du lien avec la méthode DELETE
                fetch(this.getAttribute("href"), {
                    method: "DELETE",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({ _token: this.dataset.token }),
                })
                    .then((response) => {
                        // Vérifier la réponse HTTP
                        if (response.ok) {
                            return response.json();
                        } else {
                            throw new Error("Une erreur s'est produite lors de la suppression de l'image.");
                        }
                    })
                    .then((data) => {
                        if (data.success) {
                            this.parentElement.remove();
                        } else {
                            alert(data.error);
                        }
                    })
                    .catch((error) => {
                        alert(error.message);
                    });
            }
        });
    }
};
