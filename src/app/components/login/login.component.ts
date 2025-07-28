import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../../services/auth.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-login',
  standalone: false,
  templateUrl: './login.component.html',
  styleUrl: './login.component.css'
})
export class LoginComponent {
  form: FormGroup;
  errorMessage: string = '';

  constructor(private fb: FormBuilder, private auth: AuthService, private router: Router) {
    this.form = this.fb.group({
      login: ['', Validators.required],
      password: ['', [Validators.required, Validators.minLength(4)]]
    });
  }

  login() {
    if (this.form.invalid) return;

    this.errorMessage = ''; 

    this.auth.login(this.form.value).subscribe({
      next: (res: any) => {
        this.auth.saveToken(res.token);
        this.auth.saveUser(res.user);
      	localStorage.setItem('role', res.user.role);
        this.router.navigate(['/dashboard']);
      },
      error: err => {
        if (err.status === 401 || err.status === 403) {
          this.errorMessage = err.error?.error || err.error?.message || 'Erreur inconnue';
        } else if (err.status === 422) {
          this.errorMessage = 'Champs invalides. Vérifiez le formulaire.';
        } else {
          this.errorMessage = 'Une erreur est survenue. Veuillez réessayer.';
        }
      }
    });
  }
}