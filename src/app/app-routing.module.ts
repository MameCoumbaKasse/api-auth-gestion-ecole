import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { RegisterComponent } from './components/register/register.component';
import { LoginComponent } from './components/login/login.component';
import { MenuComponent } from './components/menu/menu.component';
import { DashboardComponent } from './components/dashboard/dashboard.component';
import { ClassesComponent } from './components/classes/classes.component';
import { MatieresComponent } from './components/matieres/matieres.component';
import { EnseignantsComponent } from './components/enseignants/enseignants.component';
import { ElevesComponent } from './components/eleves/eleves.component';
import { NotesComponent } from './components/notes/notes.component';
import { BulletinsComponent } from './components/bulletins/bulletins.component';

import { AuthGuard } from './auth.guard';
import { NoAuthGuard } from './no-auth.guard';

const routes: Routes = [
  { path: 'register', component: RegisterComponent, canActivate: [NoAuthGuard] },
  { path: 'login', component: LoginComponent, canActivate: [NoAuthGuard] },
  { path: 'menu', component: MenuComponent, canActivate: [AuthGuard] },
  { path: 'dashboard', component: DashboardComponent, canActivate: [AuthGuard] },

  // 🔒 Routes avec restriction par rôle
  { path: 'classes', component: ClassesComponent, canActivate: [AuthGuard], data: { expectedRoles: ['admin'] } },
  { path: 'matieres', component: MatieresComponent, canActivate: [AuthGuard], data: { expectedRoles: ['admin'] } },
  { path: 'enseignants', component: EnseignantsComponent, canActivate: [AuthGuard], data: { expectedRoles: ['admin'] } },
  { path: 'eleves', component: ElevesComponent, canActivate: [AuthGuard], data: { expectedRoles: ['admin'] } },

  { path: 'notes', component: NotesComponent, canActivate: [AuthGuard], data: { expectedRoles: ['admin', 'enseignant'] } },


  { path: 'bulletins', component: BulletinsComponent, canActivate: [AuthGuard], data: { expectedRoles: ['admin', 'eleve'] } },

  { path: '', redirectTo: 'login', pathMatch: 'full' }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule {}