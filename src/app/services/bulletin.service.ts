import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class BulletinService {
  private apiUrl = 'http://localhost:8000/api/bulletins';

  constructor(private http: HttpClient) {}

  getPeriodes(): Observable<any> {
    return this.http.get(`${this.apiUrl}/periodes`);
  }

  getAll(): Observable<any> {
    return this.http.get(this.apiUrl);
  }

  getById(id: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/${id}`);
  }

  generate(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/generate`, data);
  }
  
  generateZip(periode: string): Observable<any> {
    return this.http.post(`${this.apiUrl}/zip/${periode}`, {});
  }
  
  download(bulletinId: number): Observable<{ url: string }> {
    return this.http.get<{ url: string }>(`${this.apiUrl}/${bulletinId}/download`);
  }
}