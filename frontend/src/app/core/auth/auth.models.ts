/**
 * Tipos do contexto de autenticação, espelhando o contrato da API
 * (ver backend/app/Domain/Auth/Http).
 */

export interface User {
  id: number;
  name: string;
  email: string;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

/** Resposta do login e do cadastro. */
export interface AuthResponse {
  token: string;
  token_type: 'Bearer';
  user: User;
}
