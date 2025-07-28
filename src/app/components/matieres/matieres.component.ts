import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ClasseService } from '../../services/classe.service';
import { EnseignantService } from '../../services/enseignant.service';
import { MatiereService } from '../../services/matiere.service';

@Component({
  selector: 'app-matieres',
  standalone: false,
  templateUrl: './matieres.component.html',
  styleUrl: './matieres.component.css'
})
export class MatieresComponent implements OnInit {
  periodes: any[] = [];
  periodesExistantes: string[] = [];
  matieres: any[] = [];
  classes: any[] = [];
  enseignants: any[] = [];

  matiereForm!: FormGroup;
  selectedId: number | null = null;
  selectedPeriode: string | null = null;
  selectedClasseId: number | null = null;
  selectedEnseignantId: number | null = null;

  constructor(private fb: FormBuilder, private matiereService: MatiereService, private classeService: ClasseService, private enseignantService: EnseignantService) {}

  ngOnInit() {
    this.initForm();
    this.getPeriodes();
    this.getMatieres();
    this.getClasses();
    this.getEnseignants();
  }

  initForm() {
    this.matiereForm = this.fb.group({
      nom: ['', Validators.required],
      coefficient: [0, [Validators.required, Validators.min(1)]],
      periode: [null, Validators.required],
      classe_id: [null, Validators.required],
      enseignant_id: [null, Validators.required],
    });
  }

  getPeriodes() {
    this.matiereService.getPeriodes().subscribe(p => {
      const anneeActuelle = new Date().getFullYear();
      this.periodes = p.filter((periode: string) => {
        const debut = parseInt(periode.split('-')[0], 10);
        return debut === anneeActuelle || debut === anneeActuelle + 1;
      });
    });
  }

  getMatieres() {
    this.matiereService.getAll().subscribe(data => {
      this.matieres = data;
      this.extractPeriodesFromMatieres(data);
    });
  }

  getEnseignants() {
    this.enseignantService.getAll().subscribe(data => this.enseignants = data);
  }

  getClasses() {
    this.classeService.getAll().subscribe(data => this.classes = data);
  }

  extractPeriodesFromMatieres(matieres: any[]) {
    const periodesSet = new Set<string>();

    for (let matiere of matieres) {
      if (matiere.periode) {
        periodesSet.add(matiere.periode);
      }
    }

    this.periodesExistantes = Array.from(periodesSet).sort();
  }

  getFilteredMatieresByPeriode() {
    this.matiereService.getAll().subscribe(data => {
      if (this.selectedPeriode !== null) {
        this.matieres = data.filter((e: any) => e.periode === this.selectedPeriode);
      } else {
        this.matieres = data;
      }
    });
  }

  getFilteredMatieresByClasse() {
    this.matiereService.getAll().subscribe(data => {
      if (this.selectedClasseId !== null) {
        const selectedId = Number(this.selectedClasseId); // force number
        this.matieres = data.filter((e: any) => e.classe_id === selectedId);
      } else {
        this.matieres = data;
      }
    });
  }

  getFilteredMatieresByEnseignant() {
    this.matiereService.getAll().subscribe(data => {
      if (this.selectedEnseignantId !== null) {
        const selectedId = Number(this.selectedEnseignantId); // force number
        this.matieres = data.filter((e: any) => e.enseignant_id === selectedId);
      } else {
        this.matieres = data;
      }
    });
  }

  save() {
    if (this.matiereForm.invalid) return;

    const formData = this.matiereForm.value;

    if (this.selectedId) {
      this.matiereService.update(this.selectedId, formData).subscribe(() => {
        this.getMatieres();
        this.resetForm();
      });
    } else {
      this.matiereService.create(formData).subscribe(() => {
        this.getMatieres();
        this.resetForm();
      });
    }
  }

  edit(matiere: any) {
    this.matiereForm.patchValue(matiere);
    this.selectedId = matiere.id;
  }

  resetForm() {
    this.matiereForm.reset();
    this.selectedId = null;
  }
}