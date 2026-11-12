// Inertia props are Vue reactive proxies, which structuredClone() cannot copy
// (DataCloneError). Props are plain JSON, so a JSON round trip is a safe deep copy.
export function cloneProps<T>(value: T): T {
  return JSON.parse(JSON.stringify(value)) as T;
}
