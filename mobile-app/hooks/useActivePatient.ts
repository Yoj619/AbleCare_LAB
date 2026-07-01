import { useEffect, useState, useCallback } from 'react';
import { listPatients, type Patient } from '../services/patients';

// ─── Active-patient resolver ────────────────────────────────────────────────────
// There is no patient-switcher UI yet, so every screen that needs "the"
// patient treats the first result from listPatients() as the active one —
// this matches today's single hardcoded-patient UX.

interface UseActivePatientResult {
  patient: Patient | null;
  loading: boolean;
  error: string | null;
  refresh: () => void;
}

export function useActivePatient(): UseActivePatientResult {
  const [patient, setPatient] = useState<Patient | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [refreshKey, setRefreshKey] = useState(0);

  const refresh = useCallback(() => setRefreshKey(k => k + 1), []);

  useEffect(() => {
    let isMounted = true;
    setLoading(true);

    (async () => {
      const result = await listPatients();
      if (!isMounted) return;

      if (result.ok) {
        setPatient(result.data[0] ?? null);
        setError(null);
      } else {
        setPatient(null);
        setError(result.error);
      }
      setLoading(false);
    })();

    return () => { isMounted = false; };
  }, [refreshKey]);

  return { patient, loading, error, refresh };
}
