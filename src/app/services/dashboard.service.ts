import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class DashboardService {
  private baseUrl = 'http://localhost:8000/api/dashboard';

  constructor(private http: HttpClient) {}

  getGlobalStats(): Observable<any> {
    return this.http.get(`${this.baseUrl}/global-stats`);
  }

  getMoyennesParClasse(): Observable<any> {
    return this.http.get(`${this.baseUrl}/moyennes-par-classe`);
  }
}