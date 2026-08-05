/**
 * Feature IA — services connectés à l'API (D14).
 */
import { createResourceApi } from '@/lib/api/resources';

export const aiSkillsApi = createResourceApi('ai-skills');
export const aiTemplatesApi = createResourceApi('ai-templates');
export const promptsApi = createResourceApi('prompts');
