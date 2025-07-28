import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { BulletinService } from '../../services/bulletin.service';
import { EleveService } from '../../services/eleve.service';
import { ClasseService } from '../../services/classe.service';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-bulletins',
  standalone: false,
  templateUrl: './bulletins.component.html',
  styleUrls: ['./bulletins.component.css']
})
export class BulletinsComponent implements OnInit {
  bulletins: any[] = [];
  eleves: any[] = [];
  classes: any[] = [];
  filteredEleves: any[] = [];
  filteredBulletins: any[] = [];
  elevesAvecBulletin: any[] = [];
  elevesSansBulletin: any[] = [];
  periodes: string[] = [];
  selectedClasseId: number | null = null;
  selectedPeriode: string = '';

  form: FormGroup;
  loading: boolean = false;
  isAdmin: boolean = false;
  isEleve: boolean = false;
  currentUser: any;
  zipUrl: string | null = null;

  constructor(
    private bulletinService: BulletinService,
    private eleveService: EleveService,
    private classeService: ClasseService,
    private fb: FormBuilder,
    private authService: AuthService
  ) {
    this.form = this.fb.group({
      eleve_id: [null, Validators.required],
      periode: ['', Validators.required]
    });
  }

  ngOnInit(): void {
    this.authService.getCurrentUser().subscribe((user: any) => {
      this.currentUser = user;
      this.isAdmin = user.role === 'admin';
      this.isEleve = user.role === 'eleve';

      this.loadInitialData();
      this.getPeriodes();

      if (this.isEleve) {
        this.selectedClasseId = user.eleve?.classe_id || null;
        this.getElevesByClasse();
      }
    });
  }

  loadInitialData() {
    this.classeService.getAll().subscribe(classes => {
      this.classes = classes;
      if (this.isAdmin) {
        this.selectedClasseId = classes.length ? classes[0].id : null;
      }
      this.getElevesByClasse();
    });
  }

  loadBulletins() {
    this.bulletinService.getAll().subscribe(data => {
      this.bulletins = this.isAdmin
        ? data
        : data.filter((b: any) => b.eleve_id === this.currentUser?.eleve?.id);

      this.onPeriodeChange(); // <-- Refiltrer une fois les bulletins à jour
    });
  }


  getElevesByClasse() {
    if (!this.selectedClasseId) return;

    this.eleveService.getAll().subscribe(data => {
      this.eleves = data.filter((e: any) => e.classe_id === this.selectedClasseId);
      this.onPeriodeChange();
    });
  }

  getPeriodes() {
    this.bulletinService.getPeriodes().subscribe(data => {
      this.periodes = data;
      if (this.periodes.length && !this.selectedPeriode) {
        this.selectedPeriode = this.periodes[0];
        this.loadBulletins();
      }
    });
  }

  onClasseChange() {
    if (!this.selectedClasseId) {
      console.warn('Aucune classe sélectionnée');
      return;
    }

    this.selectedClasseId = Number(this.selectedClasseId);

    console.log('Classe sélectionnée ID :', this.selectedClasseId, typeof this.selectedClasseId);

    this.eleveService.getAll().subscribe(dataEleves => {
      console.log('Tous les élèves récupérés :', dataEleves);

      // Debug typage / casting
      const filtered = dataEleves.filter((e: any) => {
        console.log('Comparaison :', e.classe_id, '==', this.selectedClasseId, '→', e.classe_id == this.selectedClasseId);
        return e.classe_id == this.selectedClasseId;
      });

      console.log('Élèves filtrés dans la classe :', filtered);
      this.eleves = filtered;

      this.bulletinService.getAll().subscribe(dataBulletins => {
        this.bulletins = this.isAdmin
          ? dataBulletins
          : dataBulletins.filter((b: any) => b.eleve_id === this.currentUser?.eleve?.id);

        this.onPeriodeChange();
      });
    });
  }

  onPeriodeChange() {
    console.log('onPeriodeChange lancé avec :', {
      selectedPeriode: this.selectedPeriode,
      selectedClasseId: this.selectedClasseId,
      bulletins: this.bulletins,
      eleves: this.eleves
    });

    if (!this.selectedPeriode || !this.selectedClasseId) {
      console.warn('Période ou classe non sélectionnée');
      return;
    }

    // Assurons-nous que les données sont bien là
    if (!this.bulletins.length) {
      console.warn('Aucun bulletin chargé');
    }

    if (!this.eleves.length) {
      console.warn('Aucun élève chargé pour cette classe');
    }

    // Filtres
    this.filteredBulletins = this.bulletins.filter(b => b.periode === this.selectedPeriode);
    console.log('Bulletins filtrés pour la période :', this.filteredBulletins);

    const eleveIdsAvecBulletin = this.filteredBulletins.map(b => b.eleve_id);
    const elevesDansClasse = this.eleves.filter(e => e.classe_id === this.selectedClasseId);

    console.log('Élèves dans la classe :', elevesDansClasse);
    console.log('Élève IDs avec bulletin :', eleveIdsAvecBulletin);

    this.elevesAvecBulletin = elevesDansClasse.filter(e => eleveIdsAvecBulletin.includes(e.id));
    this.elevesSansBulletin = elevesDansClasse.filter(e => !eleveIdsAvecBulletin.includes(e.id));

    console.log('Élèves avec bulletin :', this.elevesAvecBulletin);
    console.log('Élèves sans bulletin :', this.elevesSansBulletin);
  }


  generateBulletin(eleveId: number) {
    if (!this.selectedPeriode) return;
    this.loading = true;

    this.bulletinService.generate({ eleve_id: eleveId, periode: this.selectedPeriode }).subscribe({
      next: res => {
        this.bulletins.unshift(res.bulletin);
        alert('Bulletin généré avec succès');
        this.loadInitialData();
      },
      error: err => {
        alert(err.error.message || 'Erreur de génération');
      },
      complete: () => this.loading = false
    });
  }

  generateZipForPeriode() {
    if (!this.selectedPeriode) return;
    this.loading = true;

    this.bulletinService.generateZip(this.selectedPeriode).subscribe({
      next: (res: any) => {
        this.zipUrl = res.url;
      },
      error: err => alert('Erreur lors de la génération du fichier ZIP'),
      complete: () => this.loading = false
    });
  }


  openPdf(url: string | null) {
    if (url) {
      window.open(url, '_blank');
    } else {
      alert('Le bulletin PDF est introuvable.');
    }
  }

  getBulletinPdfPath(eleveId: number): string | null {
    const bulletin = this.bulletins.find(
      b => b.eleve_id === eleveId && b.periode === this.selectedPeriode
    );
    return bulletin?.pdf_path || null;
  }

  downloadBulletin(eleveId: number) {
    const bulletin = this.bulletins.find(b => b.eleve_id === eleveId && b.periode === this.selectedPeriode);
    if (!bulletin) {
      alert("Aucun bulletin trouvé pour cet élève");
      return;
    }

    this.bulletinService.download(bulletin.id).subscribe({
      next: res => {
        window.open(res.url, '_blank');
      },
      error: err => {
        console.error(err);
        alert("Erreur lors du téléchargement du bulletin");
      }
    });
  }
}