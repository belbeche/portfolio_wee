(() => {
    let getResource = async () => {
        const resourcesDiv = document.querySelector('.await-resources')

        console.log(resourcesDiv)

        await axios.get('/api/resources')
            .then(response => {
                const resources = response.data.resources
                console.log(resources);

                for (let resource of resources) {
                    resourcesDiv.innerHTML += renderResources(resource)
                }
            })
            .catch(error => console.log(error))
    }

    let renderResources = (resources) => {
        return `
            <div>
              <div>
                    <ul class="list-unstyled">
                      <li>Nom du serveur : ${resources.name}</li>
                      <li>wordpress : ${resources.wordpress}</li>
                      <li>CPU : ${resources.limits.cpu}</li>
                      <li>Stockage : ${resources.limits.disk}</li>
                      <li>RAM : ${resources.limits.memory}</li>
                      <li>Date de création : ${resources.created_at}</li>
                      <li>Date de modification : ${resources.updated_at}</li>
                      <li>Statut : ${resources.status}</li>
                      <li>Service : ${resources.suspended} </li>
                      <!--<li>Sauvegarde : backups</li>-->
                    </ul>
                </div>
            </div>
        `
    }

    getResource()
})()