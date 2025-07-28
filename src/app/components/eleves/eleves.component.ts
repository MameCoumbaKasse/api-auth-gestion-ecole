import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ClasseService } from '../../services/classe.service';
import { EleveService } from '../../services/eleve.service';

@Component({
  selector: 'app-eleves',
  standalone: false,
  templateUrl: './eleves.component.html',
  styleUrl: './eleves.component.css'
})
export class ElevesComponent implements OnInit {
  eleves: any[] = [];
  classes: any[] = [];
  eleveForm!: FormGroup;
  selectedId: number | null = null;
  selectedClasseId: number | null = null;
  selectedFile: File | null = null;

  constructor(private fb: FormBuilder, private eleveService: EleveService, private classeService: ClasseService) {}

  ngOnInit() {
    this.initForm();
    this.getEleves();
    this.getClasses();
  }

  initForm() {
    this.eleveForm = this.fb.group({
      nom: ['', [Validators.required, Validators.maxLength(155)]],
      prenom: ['', [Validators.required, Validators.maxLength(155)]],
      date_naissance: ['', Validators.required],
      nom_prenom_parent: ['', [Validators.required, Validators.maxLength(155)]],
      email_parent: ['', [Validators.required, Validators.email]],
      classe_id: [null, Validators.required],
    });
  }

  onFileSelected(event: any) {
    const file = event.target.files[0];
    if (file) {
      this.selectedFile = file;
      this.eleveForm.get('document_justificatif')?.setValue(file);
    }
  }

  getEleves() {
    this.eleveService.getAll().subscribe(data => this.eleves = data);
  }

  getClasses() {
    this.classeService.getAll().subscribe(data => this.classes = data);
  }

  getFilteredEleves() {
    this.eleveService.getAll().subscribe(data => {
      if (this.selectedClasseId !== null) {
        const selectedId = Number(this.selectedClasseId); // force number
        this.eleves = data.filter((e: any) => e.classe_id === selectedId);
      } else {
        this.eleves = data;
      }
    });
  }

  save() {
      if (this.eleveForm.invalid) return;

      const formValue = this.eleveForm.value;

      // FormData seulement si un fichier est présent
      let dataToSend: any;
      if (this.selectedFile) {
        const formData = new FormData();
        for (const key in formValue) {
          if (formValue.hasOwnProperty(key)) {
            formData.append(key, formValue[key]);
          }
        }
        formData.append('document_justificatif', this.selectedFile);
        dataToSend = formData;
      } else {
        dataToSend = formValue; // JSON si aucun fichier
      }

      if (this.selectedId) {
        this.eleveService.update(this.selectedId, dataToSend).subscribe(() => {
          this.getEleves();
          this.resetForm();
        });
      } else {
        this.eleveService.create(dataToSend).subscribe(() => {
          this.getEleves();
          this.resetForm();
        });
      }
  }

  edit(eleve: any) {
    this.eleveForm.patchValue({
      nom: eleve.user.nom,
      prenom: eleve.user.prenom,
      date_naissance: eleve.date_naissance,
      nom_prenom_parent: eleve.nom_prenom_parent,
      email_parent: eleve.email_parent,
      classe_id: eleve.classe_id
    });
    this.selectedId = eleve.id;
    this.selectedFile = null; // pour laisser l'utilisateur choisir un nouveau fichier
  }

  resetForm() {
    this.eleveForm.reset();
    this.selectedId = null;
    this.selectedFile = null;
  }
}