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
//CA RECUPERER LES INFORMATIONS DE UTILISATEUR CONNECTE 
  getCurrentUser() {
    return this.http.get(`${this.baseUrl}/me`);
  }
//RECUPERER LES INFORMATIONS DE L'UTILISATEUR QUAND IL S'AUTHENTIFIE ET STOKE DANS LE 
//STOCKAGE LOCALE DU NAVIGATEUR 
  saveUser(user: any) {
    localStorage.setItem('user', JSON.stringify(user));
  }
  //RECUPERE LES INFORMATIONS DES USERS QUI ON ETE STOCKE DANS LE STOCKAGE LOCALE 
  getUser(): any {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  }
 // SAUVEGARDER LE TOKEN DU JWT 
  saveToken(token: string) {
    localStorage.setItem('token', token);
  }
// RECUPERER LE TOKEN SAUVAGARDER 
  getToken() {
    return localStorage.getItem('token');
  }
//RECUPERER LE ROLE DE USER QUI S'EST CONNECTE 
  getRole(): string | null {
    return localStorage.getItem('role');
  }
// C'EST UNE VARIABLE BOOLEEN QUI VERIFIE SI UN USER EST AUTHENTIFIE 
  isAuthenticated(): boolean {
    return !!this.getToken();
  }
//VARIBALE BOOLLEN QUI VERIFIE SI USER CONNECTE EST UN ADMIN
  isAdmin(): boolean {
    return this.getRole() === 'admin';
  }
  //VARIBALE BOOLLEN QUI VERIFIE SI USER CONNECTE EST UN ENSEIGNANT
  isEnseignant(): boolean {
    return this.getRole() === 'enseignant';
  }
  //VARIBALE BOOLLEN QUI VERIFIE SI USER CONNECTE EST UN ELEVE 
  isEleve(): boolean {
    return this.getRole() === 'eleve';
  }
// QUAND ON SERA DECCONECTE IL VA SUPPRIMER 
  removeToken() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    localStorage.removeItem('role');
  }
}