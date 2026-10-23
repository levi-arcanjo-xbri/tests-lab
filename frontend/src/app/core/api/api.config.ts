/**
 * Base das chamadas à API. Em desenvolvimento o `ng serve` encaminha `/api`
 * para o container do backend (ver proxy.conf.json), então o navegador fala
 * sempre com a mesma origem e não há CORS a configurar.
 */
export const API_BASE_URL = '/api';

/**
 * Envelope usado pelos Resources do Laravel: o recurso vem dentro de `data`.
 */
export interface ApiData<T> {
  data: T;
}
