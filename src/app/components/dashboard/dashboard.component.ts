import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth.service';
import { DashboardService } from '../../services/dashboard.service';

@Component({
  selector: 'app-dashboard',
  standalone: false,
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit {
  globalStats: any = {};
  moyennesParClasse: any[] = [];

  constructor(
    public auth: AuthService,
    private router: Router,
    private dashboardService: DashboardService
  ) {}

  ngOnInit(): void {
    this.dashboardService.getGlobalStats().subscribe(data => {
      this.globalStats = data;
    });

    this.dashboardService.getMoyennesParClasse().subscribe(data => {
      this.moyennesParClasse = data;
    });
  }

  logout() {
    this.auth.logout().subscribe(() => {
      this.auth.removeToken();
      this.router.navigate(['/login']);
    });
  }
}