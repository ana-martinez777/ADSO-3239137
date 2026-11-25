import { useMemo } from 'react';
import { athletesApi, coachesApi } from '../api/sportsGearApi';
import { can } from '../config/permissions';
import { shortId } from '../utils/format';
import { useApiData } from './useApiData';

/**
 * Los préstamos de la API traen athlete_id y user_id.
 * Este hook resuelve los nombres para mostrarlos:
 * - Deportistas: GET /athletes (admin y coach).
 * - Entrenadores: GET /users/coaches, que el contrato reserva al admin.
 *   Para un entrenador se muestra "Tú" en sus propios préstamos.
 */
export function usePeopleNames(user) {
  const isAdmin = can.manageCoaches(user.role);
  const athletes = useApiData(() => athletesApi.list(), [], { initialData: [] });
  const coaches = useApiData(() => coachesApi.list(), [], { initialData: [], enabled: isAdmin });

  return useMemo(() => {
    const athleteNames = new Map(athletes.data.map((a) => [a.id, a.full_name]));
    const coachNames = new Map(coaches.data.map((c) => [c.id, c.username]));

    return {
      athletes: athletes.data,
      coaches: coaches.data,
      error: athletes.error || coaches.error,
      athleteName: (id) => athleteNames.get(id) ?? `Deportista ${shortId(id)}`,
      coachName: (id) => {
        if (id === user.id) return isAdmin ? user.username : 'Tú';
        return coachNames.get(id) ?? `Entrenador ${shortId(id)}`;
      },
    };
  }, [athletes.data, athletes.error, coaches.data, coaches.error, user, isAdmin]);
}
