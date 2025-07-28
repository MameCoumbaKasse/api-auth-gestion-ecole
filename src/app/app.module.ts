import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { AuthInterceptor } from './auth.interceptor';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { RegisterComponent } from './components/register/register.component';
import { LoginComponent } from './components/login/login.component';
import { DashboardComponent } from './components/dashboard/dashboard.component';
import { ClassesComponent } from './components/classes/classes.component';
import { MatieresComponent } from './components/matieres/matieres.component';
import { ElevesComponent } from './components/eleves/eleves.component';
import { NotesComponent } from './components/notes/notes.component';
import { BulletinsComponent } from './components/bulletins/bulletins.component';
import { EnseignantsComponent } from './components/enseignants/enseignants.component';
import { MenuComponent } from './components/menu/menu.component';
import { AuthLayoutComponent } from './components/auth-layout/auth-layout.component';
import { FooterComponent } from './components/footer/footer.component';

@NgModule({
  declarations: [
    AppComponent,
    RegisterComponent,
    LoginComponent,
    DashboardComponent,
    ClassesComponent,
    MatieresComponent,
    ElevesComponent,
    NotesComponent,
    BulletinsComponent,
    EnseignantsComponent,
    MenuComponent,
    AuthLayoutComponent,
    FooterComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    HttpClientModule,
    FormsModule,
    ReactiveFormsModule
  ],
  providers: [
    { provide: HTTP_INTERCEPTORS, useClass: AuthInterceptor, multi: true }
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
