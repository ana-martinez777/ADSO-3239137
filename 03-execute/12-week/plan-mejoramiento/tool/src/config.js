// Reads the seeder settings from environment variables.

export function loadConfig(env = process.env) {
  const required = (name) => {
    const value = env[name];
    if (!value) throw new Error(`Falta la variable de entorno ${name}.`);
    return value;
  };

  return {
    apiUrl: required('API_URL').replace(/\/$/, ''),
    apiPastUrl: required('API_PAST_URL').replace(/\/$/, ''),
    admin: {
      username: required('SPORTS_GEAR_ADMIN_USERNAME'),
      password: required('SPORTS_GEAR_ADMIN_PASSWORD'),
    },
    coachPassword: required('SEED_COACH_PASSWORD'),
  };
}
