const http = require('http');

// http est un module présent nativement dans node. HTTP le protocol le plus utilisé sur le WEB.
require('dotenv').config(); // permet de charger le fichier .env
const PORT = process.env.PORT || 5000;

// proccess.env, fichier .Env.
// Ce fichier contient des variables, les variables d'environeemnt, ces variables sont globales à notre projet.
// Exemple : on peut y stocker les informations de connexion à la BDD.

let users = [
    { id: 1, email: 'John@gmail.com', password: "123soleil" }
];

// Ce tableau remplace la base de données

const serv = http.createServer(async (req, res) => {
    // res.writeHead(200, { 'Content-Type': 'application/json' });
    // Cette méthode permet d'ajouter des entête de la réponse
    // res.write('Le serveur a l\'air de fonctionner');

    if (req.method === "GET" && req.url === "/users") {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.write(JSON.stringify(users));
        res.end();
    } else if (req.method === "POST" && req.url === "/users") {
        let body = '';
        // On y stockera le corps de la requête

        req.on('data', (chunk) => {
            body += chunk.toString();
        });

        req.on('end', () => {
            // let data = body.split('&');

            // let finalData = data.map((e) => {
            //     let keyValue = e.split('='); // on sépare les données
            //     return keyValue;
            // })

            // .map() permet de créer un tableau à partir du résultat d'une fonction récursive

            // console.log(finalData);

            users.push(JSON.parse(body));

            res.end('Récupération des données');
        });
    } else {
        res.writeHead(404, { 'Content-Type': 'application/json' });
        res.write(JSON.stringify({ message: "le serveur fonctionne" }));
        res.end();
    }

    res.end();

});

// la fonction fléchee ici est asynchrone, elle peut s'éxcuter en même temps que d'autres fonctions
// req = request. Exemple mon navigateur demande une page, une requpête est envoyée via le protocole HTTP.
// res = response. Exemple : le serveur accepte d'afficher la page, il renvoie la page en réponse


serv.listen(PORT, () => {
    console.log(`Le serveur écoute sur le port ${PORT}`);
});

