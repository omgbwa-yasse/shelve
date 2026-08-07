import { redirect } from 'next/navigation';

/** `/workplace` seul → la liste des espaces (le slug suit le préfixe). */
export default function WorkplaceIndex() {
  redirect('/workplaces');
}
