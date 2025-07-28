import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from './services/auth.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  standalone: false,
  styleUrl: './app.component.css'
})
export class AppComponent {
  title = 'firstAngularAppCRUDAuthGestionEcole';
  constructor(public auth: AuthService, private router: Router) {}

  logout() {
    this.auth.logout().subscribe(() => {
      this.auth.removeToken();
      this.router.navigate(['/login']);
    });
  }
}