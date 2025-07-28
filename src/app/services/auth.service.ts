import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private baseUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient, private router: Router) {}

  register(user: { login: string, password: string }) {
    return this.http.post(`${this.baseUrl}/register`, user);
  }

  login(user: { login: string, password: string }) {
    return this.http.post(`${this.baseUrl}/login`, user);
  }

  logout() {
    return this.http.post(`${this.baseUrl}/logout`, {});
  }

  getCurrentUser() {
    return this.http.get(`${this.baseUrl}/me`);
  }

  saveUser(user: any) {
    localStorage.setItem('user', JSON.stringify(user));
  }

  getUser(): any {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  }

  saveToken(token: string) {
    localStorage.setItem('token', token);
  }

  getToken() {
    return localStorage.getItem('token');
  }

  getRole(): string | null {
    return localStorage.getItem('role');
  }

  isAuthenticated(): boolean {
    return !!this.getToken();
  }

  isAdmin(): boolean {
    return this.getRole() === 'admin';
  }
  isEnseignant(): boolean {
    return this.getRole() === 'enseignant';
  }
  isEleve(): boolean {
    return this.getRole() === 'eleve';
  }

  removeToken() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    localStorage.removeItem('role');
  }
}