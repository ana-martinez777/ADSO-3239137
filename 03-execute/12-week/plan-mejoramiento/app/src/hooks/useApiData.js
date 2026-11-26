import { useCallback, useEffect, useRef, useState } from 'react';

/**
 * Runs an API query and exposes { data, error, loading, reload }.
 * The query runs again when the given dependencies change.
 */
export function useApiData(loader, deps = [], { initialData = null, enabled = true } = {}) {
  const [data, setData] = useState(initialData);
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(enabled);
  const loaderRef = useRef(loader);
  loaderRef.current = loader;
  const requestId = useRef(0);

  const reload = useCallback(async () => {
    const current = ++requestId.current;
    setLoading(true);
    setError(null);
    try {
      const result = await loaderRef.current();
      if (current === requestId.current) setData(result);
    } catch (err) {
      if (current === requestId.current) setError(err);
    } finally {
      if (current === requestId.current) setLoading(false);
    }
  }, []);

  useEffect(() => {
    if (enabled) reload();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [enabled, ...deps]);

  return { data, error, loading, reload, setData };
}
