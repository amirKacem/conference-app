import { fetchCollection } from './api';

export function getComments(conference) {
    return fetchCollection('api/comments?conference=' + conference.id);
}
