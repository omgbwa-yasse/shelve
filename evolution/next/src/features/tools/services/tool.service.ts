/**
 * Feature Outils — services connectés à l'API (D01 référentiels + D08 thésaurus).
 */
import { createResourceApi } from '@/lib/api/resources';

export const activitiesApi = createResourceApi('activities');
export const communicabilitiesApi = createResourceApi('communicabilities');
export const keywordsApi = createResourceApi('keywords');
export const languagesApi = createResourceApi('languages');
export const sortsApi = createResourceApi('sorts');
export const lawsApi = createResourceApi('laws');
export const lawArticlesApi = createResourceApi('law-articles');
export const referenceListsApi = createResourceApi('reference-lists');
export const organisationsApi = createResourceApi('organisations');
export const thesaurusSchemesApi = createResourceApi('thesaurus-schemes');
export const thesaurusConceptsApi = createResourceApi('thesaurus-concepts');
