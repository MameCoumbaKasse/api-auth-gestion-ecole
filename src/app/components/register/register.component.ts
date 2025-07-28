import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../../services/auth.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-register',
  standalone: false,
  templateUrl: './register.component.html',
  styleUrl: './register.component.css'
})
export class RegisterComponent {
  form: FormGroup;
  errorMessage: string = '';

  constructor(private fb: FormBuilder, private auth: AuthService, private router: Router) {
    this.form = this.fb.group({
      nom: ['', Validators.required],
      prenom: ['', Validators.required],
      email: ['', Validators.required, Validators.email],
      password: ['', [Validators.required, Validators.minLength(4)]]
    });
  }

  register() {
    if (this.form.invalid) return;

    this.errorMessage = '';

    this.auth.register(this.form.value).subscribe({
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