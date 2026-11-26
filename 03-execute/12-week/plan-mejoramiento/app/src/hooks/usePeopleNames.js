import { useMemo } from 'react';
import { athletesApi, coachesApi } from '../api/sportsGearApi';
import { can } from '../config/permissions';
import { shortId } from '../utils/format';
import { useApiData } from './useApiData';

/**
 * Loans from the API include athlete_id and user_id.
 * This hook resolves the names to display:
 * - Athletes: GET /athletes (admin and coach).
 * - Coaches: GET /users/coaches, which the contract restricts to admin.
 *   A coach sees "Tú" on their own loans.
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
