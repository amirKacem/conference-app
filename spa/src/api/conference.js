import { fetchCollection } from './api';

export function getConferences() {
    return fetchCollection('api/conferences');
}
