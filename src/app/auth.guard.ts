import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, Router } from '@angular/router';
import { AuthService } from './services/auth.service';

@Injectable({
  providedIn: 'root'
})
export class AuthGuard implements CanActivate {
  constructor(private auth: AuthService, private router: Router) {}

  canActivate(route: ActivatedRouteSnapshot): boolean {
    const expectedRoles = route.data['expectedRoles'];
    const token = this.auth.getToken();
    const currentRole = this.auth.getRole();

    if (!token) {
      this.router.navigate(['/login']);
      return false;
    }

    // autorise un tableau ou une string
    if (expectedRoles && !expectedRoles.includes(currentRole)) {
      this.router.navigate(['/dashboard']);
      return false;
    }

    return true;
  }
}