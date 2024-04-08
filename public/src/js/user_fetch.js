/*
const btnRegister = document.querySelector('#btn-register');
const btnEditProfile = document.querySelector('#btnEditProfile');

const userLastName = document.querySelector('#user_nom');
const userFirstName = document.querySelector('#user_prenom');
const userCivility0 = document.querySelector('#user_civility_0'); // M.
const userCivility1 = document.querySelector('#user_civility_1'); // Mme
const userEmail = document.querySelector('#user_email');
const userFirstPassword = document.querySelector('#user_password_first');
const userSecondPassword = document.querySelector('#user_password_second');
const userChecked = document.querySelector('#user_checked');
const userAvatar = document.querySelector('#edit_profile_avatar');

btnRegister.addEventListener('click', function(e) {
    e.preventDefault();

    // Vérification de la civilité
    let userCivility = null;
    if (userCivility0.checked === true) {
        userCivility = 'M';
    } else if (userCivility1.checked === true) {
        userCivility = 'Mme';
    } else {
        alert('Veuillez sélectionner une civilité.');
        return; // Arrête la soumission si la civilité n'est pas sélectionnée
    }

    // Vérification du nom et prénom
    if (userLastName.value.trim() === '' || userFirstName.value.trim() === '') {
        alert('Veuillez saisir votre nom et prénom.');
        return; // Arrête la soumission si le nom ou prénom est vide
    }

    // Vérification de l'email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(userEmail.value)) {
        alert('Veuillez saisir une adresse email valide.');
        return; // Arrête la soumission si l'email est invalide
    }

    // Vérification du mot de passe
    if (userFirstPassword.value !== userSecondPassword.value) {
        alert('Les mots de passe ne correspondent pas.');
        return; // Arrête la soumission si les mots de passe ne correspondent pas
    }

    // Vérification de l'acceptation des conditions
    if (!userChecked.checked) {
        alert('Veuillez accepter les conditions.');
        return; // Arrête la soumission si les conditions ne sont pas acceptées
    }

    // Si toutes les vérifications sont réussies, créer l'objet formData et envoyer la requête
    const formData = {
        userLastName: userLastName.value,
        userFirstName: userFirstName.value,
        userEmail: userEmail.value,
        userCivility: userCivility,
        userFirstPassword: userFirstPassword.value,
        userSecondPassword: userSecondPassword.value,
        userChecked: userChecked.value
    };

    axios.post(btnRegister.dataset.registerUrl, formData)
        .then(response => {
            // Envoie à la base de données du site support de scriptzenit
            const url2 = document.querySelector('.register-url2');
            axios.post(url2.dataset.registerUrl2, formData)
                .then(response2 => {
                    location.href = url2.dataset.redirectToLogin;
                })
                .catch(error2 => console.log(error2));
        })
        .catch(error => console.log(error));
});

btnEditProfile.addEventListener('click', function(e) {
    e.preventDefault();

    // Vérification de la civilité
    let userCivility = null;
    if (userCivility0.checked === true) {
        userCivility = 'M';
    } else if (userCivility1.checked === true) {
        userCivility = 'Mme';
    } else {
        alert('Veuillez sélectionner une civilité.');
        return; // Arrête la soumission si la civilité n'est pas sélectionnée
    }

    // Vérification du nom et prénom
    if (userLastName.value.trim() === '' || userFirstName.value.trim() === '') {
        alert('Veuillez saisir votre nom et prénom.');
        return; // Arrête la soumission si le nom ou prénom est vide
    }

    // Vérification de l'email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(userEmail.value)) {
        alert('Veuillez saisir une adresse email valide.');
        return; // Arrête la soumission si l'email est invalide
    }

    // Vérification du mot de passe
    if (userFirstPassword.value !== userSecondPassword.value) {
        alert('Les mots de passe ne correspondent pas.');
        return; // Arrête la soumission si les mots de passe ne correspondent pas
    }

    // Vérification de l'acceptation des conditions
    if (!userChecked.checked) {
        alert('Veuillez accepter les conditions.');
        return; // Arrête la soumission si les conditions ne sont pas acceptées
    }

    // Si toutes les vérifications sont réussies, créer l'objet formData et envoyer la requête
    const formData = {
        userLastName: userLastName.value,
        userFirstName: userFirstName.value,
        userEmail: userEmail.value,
        userCivility: userCivility,
        userFirstPassword: userFirstPassword.value,
        userSecondPassword: userSecondPassword.value,
        userChecked: userChecked.value,
        userAvatar: userAvatar.value
    };

    // Envoie à la base de données du premier site
    axios.post(btnEditProfile.dataset.registerUrl, formData)
        .then(response => {
            // Envoie à la base de données du deuxième site
            const url2 = document.querySelector('.register-url2');
            axios.post(url2.dataset.registerUrl2, formData)
                .then(response2 => {
                    // Redirection vers la page de connexion sur le deuxième site
                    console.log(url2.dataset.value)
                    /!*location.href = url2.dataset.redirectToLogin;*!/
                })
                .catch(error2 => console.error(error2));
        })
        .catch(error => console.error(error));
});
*/
