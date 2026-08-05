/**
 * Feature Workflow — services connectés à l'API (D13 + tâches D12).
 */
import { createResourceApi } from '@/lib/api/resources';

export const workflowDefinitionsApi = createResourceApi('workflow-definitions');
export const workflowInstancesApi = createResourceApi('workflow-instances');
export const tasksApi = createResourceApi('tasks');
