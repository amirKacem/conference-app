export function fetchCollection(path) {
    return fetch(ENV_API_ENDPOINT + path)
        .then((resp) => resp.json())
        .then((json) => json['hydra:member']);
}
