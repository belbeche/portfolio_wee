const btnRegister = document.querySelector('#btn-register');
const userLastName = document.querySelector('#user_nom');
const userFirstName = document.querySelector('#user_prenom');
const userCivility0 = document.querySelector('#user_civility_0'); // M.
const userCivility1 = document.querySelector('#user_civility_1'); // MMe

const userEmail = document.querySelector('#user_email');
const userFirstPassword = document.querySelector('#user_password_first');
const userSecondPassword = document.querySelector('#user_password_second');
const userChecked = document.querySelector('#user_checked');

btnRegister.addEventListener('click', function(e) {
    e.preventDefault()

    console.log(userCivility0.checked)
    console.log(userCivility1.checked)
    let userCivility = null;

    if (userCivility0.checked === true){
        userCivility = 'M.'
    } else if (userCivility1.checked === true){
        userCivility = 'Mme'
    } else {
        userCivility = null
    }

    const formData = {
        userLastName: userLastName.value,
        userFirstName: userFirstName.value,
        userEmail: userEmail.value,
        userCivility: userCivility,
        userFirstPassword: userFirstPassword.value,
        userSecondPassword: userSecondPassword.value,
        userChecked: userChecked.value
    }
    console.log(formData);

    axios.post(btnRegister.dataset.registerUrl, formData)
        .then(response => {
            // envoie à la base de données du site support de scriptzenit
            const url2 = document.querySelector('.register-url2')    
            // console.log(url2)
            // console.log(url2.dataset.registerUrl2)
            axios.post(url2.dataset.registerUrl2, formData)
                .then(response => {
                    location.href=url2.dataset.redirectToLogin
                })
                .catch(error => console.log(error))
        })
        .catch(error => console.log(error))
})
